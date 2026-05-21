<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;

class RecognitionQualityService
{
    public function analyseCapture(?VisualRecognitionCapture $capture): array
    {
        $path = $capture?->file_path;
        if (!$path || !Storage::disk('public')->exists($path)) {
            return $this->emptyResult('capture_missing');
        }

        $opencvQuality = app(OpenCvRecognitionClient::class)->analyseQuality($path);
        if ($opencvQuality) {
            return [
                'ok' => true,
                'score' => round((float) ($opencvQuality['score'] ?? 0), 4),
                'blur' => round((float) ($opencvQuality['blur'] ?? 0), 4),
                'brightness' => round((float) ($opencvQuality['brightness'] ?? 0), 4),
                'glare' => round((float) ($opencvQuality['glare'] ?? 100), 4),
                'glare_score' => round((float) ($opencvQuality['glare_score'] ?? 0), 4),
                'card_area' => round((float) ($opencvQuality['card_area'] ?? $opencvQuality['object_area'] ?? 0), 4),
                'object_area' => round((float) ($opencvQuality['object_area'] ?? $opencvQuality['card_area'] ?? 0), 4),
                'perspective' => round((float) ($opencvQuality['perspective'] ?? 0), 4),
                'width' => $opencvQuality['source_width'] ?? null,
                'height' => $opencvQuality['source_height'] ?? null,
                'size' => Storage::disk('public')->size($path),
                'rejection_reason' => null,
            ];
        }

        $absolutePath = Storage::disk('public')->path($path);
        $size = @getimagesize($absolutePath);
        if (!$size) {
            return $this->emptyResult('invalid_image');
        }

        $image = $this->openImage($absolutePath, (string) ($size['mime'] ?? ''));
        if (!$image) {
            return $this->emptyResult('unsupported_image');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $sampleStep = max(1, (int) floor(max($width, $height) / 96));
        $pixels = 0;
        $brightnessTotal = 0.0;
        $edgeTotal = 0.0;
        $edgeCount = 0;
        $glarePixels = 0;
        $previousRows = [];

        for ($y = 0; $y < $height; $y += $sampleStep) {
            $previousLum = null;

            for ($x = 0; $x < $width; $x += $sampleStep) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $lum = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $saturation = $max > 0 ? (($max - $min) / $max) : 0;

                $pixels++;
                $brightnessTotal += $lum;

                if ($lum >= 238 && $saturation <= 0.12) {
                    $glarePixels++;
                }

                if ($previousLum !== null) {
                    $edgeTotal += abs($lum - $previousLum);
                    $edgeCount++;
                }

                $columnIndex = (int) floor($x / $sampleStep);
                if (isset($previousRows[$columnIndex])) {
                    $edgeTotal += abs($lum - $previousRows[$columnIndex]);
                    $edgeCount++;
                }

                $previousRows[$columnIndex] = $lum;
                $previousLum = $lum;
            }
        }

        imagedestroy($image);

        $averageBrightness = $pixels > 0 ? $brightnessTotal / $pixels : 0;
        $brightnessScore = $this->brightnessScore($averageBrightness);
        $blurScore = min(100, max(0, (($edgeCount > 0 ? $edgeTotal / $edgeCount : 0) / 38) * 100));
        $glarePercent = $pixels > 0 ? ($glarePixels / $pixels) * 100 : 100;
        $glareScore = max(0, min(100, 100 - ($glarePercent * 4.5)));
        $objectAreaScore = $this->objectAreaScore($capture, $width, $height);
        $perspectiveScore = $this->perspectiveScore($capture);

        $qualityScore = ($blurScore * 0.24)
            + ($brightnessScore * 0.22)
            + ($glareScore * 0.18)
            + ($objectAreaScore * 0.20)
            + ($perspectiveScore * 0.16);

        return [
            'ok' => true,
            'score' => round($qualityScore, 4),
            'blur' => round($blurScore, 4),
            'brightness' => round($brightnessScore, 4),
            'glare' => round(100 - $glareScore, 4),
            'glare_score' => round($glareScore, 4),
            'card_area' => round($objectAreaScore, 4),
            'object_area' => round($objectAreaScore, 4),
            'perspective' => round($perspectiveScore, 4),
            'width' => $width,
            'height' => $height,
            'size' => Storage::disk('public')->size($path),
            'rejection_reason' => null,
        ];
    }

    public function qualityModifier(float $qualityScore): float
    {
        foreach ((array) config('webcatalogue.recognition.pipeline_v2.quality.modifiers', []) as $row) {
            if ($qualityScore >= (float) ($row['min'] ?? 0)) {
                return (float) ($row['modifier'] ?? 1);
            }
        }

        return 0.0;
    }

    private function openImage(string $path, string $mime)
    {
        return match ($mime) {
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => @imagecreatefromjpeg($path),
        };
    }

    private function brightnessScore(float $brightness): float
    {
        $ideal = 132;
        $distance = abs($brightness - $ideal);

        return max(0, min(100, 100 - (($distance / 132) * 100)));
    }

    private function objectAreaScore(?VisualRecognitionCapture $capture, int $width, int $height): float
    {
        $contour = $capture?->metadata['opencv_analysis']['contour'] ?? null;
        if (is_array($contour) && isset($contour['width'], $contour['height'])) {
            $areaRatio = (((float) $contour['width']) * ((float) $contour['height'])) / max(1, $width * $height);
            $ideal = 0.72;

            return max(0, min(100, 100 - (abs($areaRatio - $ideal) / $ideal) * 100));
        }

        return 72.0;
    }

    private function perspectiveScore(?VisualRecognitionCapture $capture): float
    {
        $confidence = $capture?->metadata['opencv_analysis']['confidence'] ?? null;
        if (is_numeric($confidence)) {
            return max(0, min(100, ((float) $confidence) * 100));
        }

        return ($capture?->metadata['cropped_client_side'] ?? false) ? 78.0 : 68.0;
    }

    private function emptyResult(string $reason): array
    {
        return [
            'ok' => false,
            'score' => 0.0,
            'blur' => 0.0,
            'brightness' => 0.0,
            'glare' => 100.0,
            'glare_score' => 0.0,
            'card_area' => 0.0,
            'object_area' => 0.0,
            'perspective' => 0.0,
            'width' => null,
            'height' => null,
            'size' => null,
            'rejection_reason' => $reason,
        ];
    }
}
