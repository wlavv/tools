<?php

namespace Tests\Unit;

use Modules\AIConsensus\Services\AIConsensusService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AIConsensusServiceTest extends TestCase
{
    public function test_pdf_best_effort_extract_returns_json_encodable_text(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ai-consensus-pdf-');
        $this->assertIsString($path);

        try {
            file_put_contents($path, "%PDF-1.4\nBT (Texto valido " . chr(0xC3) . " final) ET\n%%EOF");

            $service = new AIConsensusService();
            $method = new ReflectionMethod($service, 'readPdfBestEffort');
            $method->setAccessible(true);

            $text = $method->invoke($service, $path);

            $this->assertIsString($text);
            $this->assertNotFalse(json_encode(['prompt' => $text]));
            $this->assertSame(JSON_ERROR_NONE, json_last_error());
        } finally {
            if (is_string($path) && file_exists($path)) {
                unlink($path);
            }
        }
    }
}
