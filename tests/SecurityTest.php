<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Тесты модуля безопасности (rate-limit, CSRF, XSS-утилиты)
 */
class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../api/security.php';
    }

    public function testCsrfTokenGeneration(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];

        $token = cf_csrf_token();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token));
    }

    public function testCsrfTokenConsistency(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];

        $token1 = cf_csrf_token();
        $token2 = cf_csrf_token();
        $this->assertEquals($token1, $token2, 'Token should remain the same within the time window');
    }

    public function testCsrfVerifyValid(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];

        $token = cf_csrf_token();
        $this->assertTrue(cf_csrf_verify($token));
    }

    public function testCsrfVerifyInvalid(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];

        cf_csrf_token();
        $this->assertFalse(cf_csrf_verify('invalid_token_12345'));
    }

    public function testCsrfVerifyNull(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];

        cf_csrf_token();
        $this->assertFalse(cf_csrf_verify(null));
    }

    public function testCsrfVerifyEmpty(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];

        cf_csrf_token();
        $this->assertFalse(cf_csrf_verify(''));
    }

    public function testEscHtmlBasic(): void
    {
        $this->assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', cf_esc('<script>alert(1)</script>'));
    }

    public function testEscHtmlQuotes(): void
    {
        $this->assertEquals('&quot;hello&quot; &amp; &#039;world&#039;', cf_esc('"hello" & \'world\''));
    }

    public function testEscHtmlEmpty(): void
    {
        $this->assertEquals('', cf_esc(''));
    }

    public function testEscHtmlCyrillic(): void
    {
        $this->assertEquals('Привет мир', cf_esc('Привет мир'));
    }

    public function testEscJsBasic(): void
    {
        $result = cf_esc_js('<script>');
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringNotContainsString('>', $result);
    }
}
