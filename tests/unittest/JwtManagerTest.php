<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Security\JwtManager;

class JwtManagerTest extends TestCase
{
    private $jwtManager;

    public function setUp(): void
    {
        $this->jwtManager = new JwtManager('your-256-bit-secret');
    }

    public function testCreateJwt(): void
    {
        $jwt = $this->jwtManager->createJwt(["sub" => "1234567890","name" => "John Doe","iat" => 1516239022]);

        $this->assertEquals($jwt, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c');
    }

    public function testVerifyJwtWhenValid(): void
    {
        $payload = ["sub" => "1234567890","name" => "John Doe","iat" => 15162390220];
        $jwt = $this->jwtManager->createJwt($payload);

        $returnedData = $this->jwtManager->verifyJwt($jwt);

        $this->assertEquals($returnedData, $payload);
    }

    public function testVerifyJwtWhenTokenIsInvalid(): void
    {
        $jwt = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid JWT.');

        $this->jwtManager->verifyJwt($jwt);
    }

    public function testVerifyJwtWhenSignatureIsCorrupted(): void
    {
        $payload = ["sub" => "1234567890","name" => "John Doe","iat" => 15162390220];
        $jwt = $this->jwtManager->createJwt($payload);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid hash.');

        $jwtManager = new JwtManager('invalid-secret');
        $jwtManager->verifyJwt($jwt);
    }

    public function testVerifyJwtWhenPerempted(): void
    {
        $payload = ["sub" => "1234567890","name" => "John Doe","iat" => 200];
        $jwt = $this->jwtManager->createJwt($payload);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The token is perempted.');

        $this->jwtManager->verifyJwt($jwt);
    }

    public function testGetJwtPayload(): void
    {
        $payload = $this->jwtManager->getJwtPayload('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c');

        $this->assertEquals($payload, ["sub" => "1234567890","name" => "John Doe","iat" => 1516239022]);
    }
}
