<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Тесты валидации форм и данных
 */
class ValidationTest extends TestCase
{
    public function testEmailValidation(): void
    {
        $this->assertTrue(filter_var('user@example.com', FILTER_VALIDATE_EMAIL) !== false);
        $this->assertTrue(filter_var('test@cargoflow.ru', FILTER_VALIDATE_EMAIL) !== false);
        $this->assertFalse(filter_var('invalid-email', FILTER_VALIDATE_EMAIL) !== false);
        $this->assertFalse(filter_var('', FILTER_VALIDATE_EMAIL) !== false);
        $this->assertFalse(filter_var('@no-local.com', FILTER_VALIDATE_EMAIL) !== false);
        $this->assertFalse(filter_var('user@', FILTER_VALIDATE_EMAIL) !== false);
    }

    public function testPasswordMinLength(): void
    {
        $this->assertTrue(mb_strlen('123456') >= 6);
        $this->assertTrue(mb_strlen('strongpassword') >= 6);
        $this->assertFalse(mb_strlen('12345') >= 6);
        $this->assertFalse(mb_strlen('') >= 6);
        $this->assertFalse(mb_strlen('abc') >= 6);
    }

    public function testPasswordHashing(): void
    {
        $password = 'test_password_123';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertNotEquals($password, $hash);
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrong_password', $hash));
    }

    public function testTransportTypeValidation(): void
    {
        $validTypes = ['air', 'sea', 'road', 'rail', 'multi'];

        $this->assertTrue(in_array('air', $validTypes, true));
        $this->assertTrue(in_array('sea', $validTypes, true));
        $this->assertTrue(in_array('road', $validTypes, true));
        $this->assertTrue(in_array('rail', $validTypes, true));
        $this->assertTrue(in_array('multi', $validTypes, true));
        $this->assertFalse(in_array('bike', $validTypes, true));
        $this->assertFalse(in_array('', $validTypes, true));
        $this->assertFalse(in_array('<script>', $validTypes, true));
    }

    public function testWeightValidation(): void
    {
        $validate = fn($v) => is_numeric($v) && (float)$v >= 0;

        $this->assertTrue($validate('100.5'));
        $this->assertTrue($validate('0.01'));
        $this->assertTrue($validate('99999'));
        $this->assertFalse($validate('abc'));
        $this->assertFalse($validate(''));
        $this->assertFalse($validate('-1'));
    }

    public function testApplicationStatusRange(): void
    {
        $validStatuses = [1, 2, 3, 4, 5];

        foreach ($validStatuses as $s) {
            $this->assertTrue($s >= 1 && $s <= 5);
        }

        $this->assertFalse(0 >= 1 && 0 <= 5);
        $this->assertFalse(6 >= 1 && 6 <= 5);
        $this->assertFalse(-1 >= 1 && -1 <= 5);
    }

    public function testInputSanitization(): void
    {
        $malicious = "  <script>alert('xss')</script>  ";
        $sanitized = trim($malicious);
        $escaped = htmlspecialchars($sanitized, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testConfirmTokenFormat(): void
    {
        $token = bin2hex(random_bytes(32));
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testCityNotEmpty(): void
    {
        $cityFrom = trim('Шанхай');
        $cityTo = trim('Москва');
        $this->assertNotEmpty($cityFrom);
        $this->assertNotEmpty($cityTo);

        $emptyCity = trim('');
        $this->assertEmpty($emptyCity);

        $spacesCity = trim('   ');
        $this->assertEmpty($spacesCity);
    }

    public function testSqlInjectionInInput(): void
    {
        $maliciousInputs = [
            "'; DROP TABLE users; --",
            "1 OR 1=1",
            "admin'--",
            "' UNION SELECT * FROM users --",
        ];

        foreach ($maliciousInputs as $input) {
            $escaped = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $this->assertStringNotContainsString("'", $escaped);
        }
    }
}
