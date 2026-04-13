<?php

namespace Modules\Tasks\Services;

use Illuminate\Support\Facades\Http;

class FamilyPlannerWeatherService
{
    protected float $latitude = 41.6946;
    protected float $longitude = -8.8302;
    protected string $location = 'Viana do Castelo - Cidade';

    public function today(): array
    {
        try {
            $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'current' => 'temperature_2m,weather_code,is_day',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
                'hourly' => 'temperature_2m,weather_code,is_day',
                'timezone' => 'Europe/Lisbon',
                'forecast_days' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? [];
                $daily = $data['daily'] ?? [];
                $hourly = $data['hourly'] ?? [];

                $code = (int) ($current['weather_code'] ?? 3);
                $isDay = (bool) ($current['is_day'] ?? 1);

                return [
                    'location' => $this->location,
                    'temp' => (int) round($current['temperature_2m'] ?? 0),
                    'description' => $this->descriptionFromCode($code),
                    'image' => $this->imageFromCode($code, $isDay),
                    'max' => (int) round($daily['temperature_2m_max'][0] ?? ($current['temperature_2m'] ?? 0)),
                    'min' => (int) round($daily['temperature_2m_min'][0] ?? ($current['temperature_2m'] ?? 0)),
                    'hourly' => $this->buildHourly($hourly),
                ];
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        return $this->fallback();
    }

    protected function buildHourly(array $hourly): array
    {
        $targets = [ '10:00', '14:00', '18:00'];
        $times = $hourly['time'] ?? [];
        $temps = $hourly['temperature_2m'] ?? [];
        $codes = $hourly['weather_code'] ?? [];
        $dayFlags = $hourly['is_day'] ?? [];

        $items = [];
        foreach ($targets as $target) {
            $index = null;
            foreach ($times as $i => $time) {
                if (str_ends_with((string) $time, $target)) {
                    $index = $i;
                    break;
                }
            }

            if ($index === null) {
                continue;
            }

            $code = (int) ($codes[$index] ?? 3);
            $isDay = (bool) ($dayFlags[$index] ?? 1);

            $items[] = [
                'time' => substr($target, 0, 2) . 'h',
                'temp' => (int) round($temps[$index] ?? 0),
                'description' => $this->descriptionFromCode($code),
                'image' => $this->imageFromCode($code, $isDay),
            ];
        }

        return $items;
    }

    protected function fallback(): array
    {
        return [
            'location' => $this->location,
            'temp' => 15,
            'description' => 'Poucas nuvens',
            'image' => $this->imageFile('cloudy-1-day.svg'),
            'max' => 16,
            'min' => 10,
            'hourly' => [
                ['time' => '08h', 'temp' => 14, 'description' => 'Poucas nuvens', 'image' => $this->imageFile('cloudy-1-day.svg')],
                ['time' => '12h', 'temp' => 15, 'description' => 'Poucas nuvens', 'image' => $this->imageFile('cloudy-1-day.svg')],
                ['time' => '16h', 'temp' => 15, 'description' => 'Nublado', 'image' => $this->imageFile('cloudy.svg')],
                ['time' => '20h', 'temp' => 12, 'description' => 'Algumas nuvens', 'image' => $this->imageFile('cloudy-1-night.svg')],
            ],
        ];
    }

    protected function imageFromCode(int $code, bool $isDay): string
    {
        $file = match (true) {
            $code === 0 => $isDay ? 'clear-day.svg' : 'clear-night.svg',
            in_array($code, [1, 2], true) => $isDay ? 'cloudy-1-day.svg' : 'cloudy-1-night.svg',
            $code === 3 => 'cloudy.svg',
            in_array($code, [45, 48], true) => $isDay ? 'fog-day.svg' : 'fog-night.svg',
            in_array($code, [51, 53, 55, 56, 57], true) => $isDay ? 'rainy-1-day.svg' : 'rainy-1-night.svg',
            in_array($code, [61, 63], true) => $isDay ? 'rainy-2-day.svg' : 'rainy-2-night.svg',
            in_array($code, [65, 66, 67, 80, 81, 82], true) => $isDay ? 'rainy-3-day.svg' : 'rainy-3-night.svg',
            in_array($code, [95, 96, 99], true) => $isDay ? 'isolated-thunderstorms-day.svg' : 'isolated-thunderstorms-night.svg',
            default => 'cloudy.svg',
        };

        return $this->imageFile($file);
    }

    protected function imageFile(string $file): string
    {
        return asset('/modules/tasks/weather') . '/'.$file;
    }

    protected function descriptionFromCode(int $code): string
    {
        return match (true) {
            $code === 0 => 'Céu limpo',
            in_array($code, [1, 2], true) => 'Poucas nuvens',
            $code === 3 => 'Nublado',
            in_array($code, [45, 48], true) => 'Nevoeiro',
            in_array($code, [51, 53, 55, 56, 57], true) => 'Chuviscos',
            in_array($code, [61, 63], true) => 'Chuva',
            in_array($code, [65, 66, 67, 80, 81, 82], true) => 'Chuva forte',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => 'Neve',
            in_array($code, [95, 96, 99], true) => 'Trovoada',
            default => 'Condição variável',
        };
    }
}
