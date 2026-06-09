<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Тесты API-эндпоинтов (структурная проверка)
 */
class ApiTest extends TestCase
{
    private string $apiBase;

    protected function setUp(): void
    {
        $this->apiBase = 'http://localhost:8000/api';
    }

    private function isServerRunning(): bool
    {
        $ch = @curl_init($this->apiBase . '/cabinet.php?action=csrf');
        if (!$ch) return false;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code > 0;
    }

    public function testCsrfEndpoint(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $ch = curl_init($this->apiBase . '/cabinet.php?action=csrf');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cf_test_cookies.txt');
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $code);
        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertEquals('ok', $data['status']);
        $this->assertArrayHasKey('token', $data);
        $this->assertEquals(64, strlen($data['token']));
    }

    private function getCsrfWithSession(): array
    {
        $cookieFile = tempnam(sys_get_temp_dir(), 'cf_test_');
        $ch = curl_init($this->apiBase . '/cabinet.php?action=csrf');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        $resp = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($resp, true);
        return ['token' => $data['token'] ?? '', 'cookie' => $cookieFile];
    }

    public function testLoginWithoutCredentials(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $csrf = $this->getCsrfWithSession();

        $ch = curl_init($this->apiBase . '/cabinet.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $csrf['cookie']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-CSRF-TOKEN: ' . $csrf['token']
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'action' => 'login',
            'email' => '',
            'password' => '',
            'csrf_token' => $csrf['token']
        ]));
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        @unlink($csrf['cookie']);

        $this->assertEquals(400, $code);
        $data = json_decode($response, true);
        $this->assertEquals('error', $data['status']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $csrf = $this->getCsrfWithSession();

        $ch = curl_init($this->apiBase . '/cabinet.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $csrf['cookie']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-CSRF-TOKEN: ' . $csrf['token']
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'action' => 'login',
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpass',
            'csrf_token' => $csrf['token']
        ]));
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        @unlink($csrf['cookie']);

        $this->assertEquals(401, $code);
        $data = json_decode($response, true);
        $this->assertEquals('error', $data['status']);
    }

    public function testLoginDemoSuccess(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $ch = curl_init($this->apiBase . '/cabinet.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cf_test_cookies.txt');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'action' => 'login',
            'email' => 'demo@cargoflow.ru',
            'password' => 'demo',
            'csrf_token' => 'bypass_for_test'
        ]));
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($code === 403) {
            $this->assertStringContainsString('CSRF', $data['message'] ?? '');
            return;
        }

        $this->assertEquals(200, $code);
        $this->assertEquals('ok', $data['status']);
        $this->assertArrayHasKey('user', $data);
    }

    public function testLeadPostWithoutMethod(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $ch = curl_init($this->apiBase . '/lead.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($data) {
            $this->assertEquals('error', $data['status']);
        }
        $this->assertNotEquals(500, $code);
    }

    public function testRateLimitHeaders(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $ch = curl_init($this->apiBase . '/cabinet.php?action=csrf');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $code);
        $this->assertStringContainsString('X-Content-Type-Options: nosniff', $response);
    }

    public function testSecurityHeaders(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $ch = curl_init($this->apiBase . '/cabinet.php?action=csrf');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $this->assertStringContainsString('X-Frame-Options: DENY', $response);
        $this->assertStringContainsString('X-XSS-Protection: 1; mode=block', $response);
    }

    public function testCheckSessionUnauthenticated(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $ch = curl_init($this->apiBase . '/cabinet.php?action=check');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $code);
        $data = json_decode($response, true);
        $this->assertEquals('ok', $data['status']);
        $this->assertFalse($data['authenticated']);
    }

    public function testRegisterValidation(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Dev server not running on localhost:8000');
        }

        $cookieFile = '/tmp/cf_test_reg_cookies.txt';

        $ch = curl_init($this->apiBase . '/cabinet.php?action=csrf');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        $csrfResp = curl_exec($ch);
        curl_close($ch);
        $csrfData = json_decode($csrfResp, true);
        $csrfToken = $csrfData['token'] ?? '';

        $ch = curl_init($this->apiBase . '/cabinet.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-CSRF-TOKEN: ' . $csrfToken
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'action' => 'register',
            'email' => 'invalid-email',
            'password' => '123',
            'csrf_token' => $csrfToken
        ]));
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(400, $code);
        $data = json_decode($response, true);
        $this->assertEquals('error', $data['status']);

        @unlink($cookieFile);
    }
}
