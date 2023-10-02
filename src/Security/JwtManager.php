<?php

namespace App\Security;

class JwtManager
{
    /**
     * @return JWT
     */
    public function createJwt(array $payload): string
    {
        $headerAndPlayload = base64_encode('{"alg": "HS256","typ": "JWT"}') . '.'
            . base64_encode(json_encode([
                'iat' => time() + 2592000,
                ...$payload,
            ]));
        $hash = hash_hmac("sha256", $headerAndPlayload, $_ENV['APP_SECRET']);
        $jwt = $headerAndPlayload . '.' . $hash;

        return $jwt;
    }

    /**
     * @return JWT payload
     */
    public function verifyJwt(string $token): array
    {
        // 0. Verify the token format
        if (null === $token) {
            throw new \Exception('No token provided.');
        }
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid JWT.');
        }

        // 1. Verify the signature of the JWT
        $hash = hash_hmac("sha256", $parts[0] . "." . $parts[1], $_ENV['APP_SECRET']);
        if ($parts[2] !== $hash) {
            throw new \Exception('Invalid hash.');
        }

        // We can trust these data because we check the signature if valid!
        $data = json_decode(base64_decode($parts[1]), true);

        // We check that the token is not perempted
        if (time() >= $data['iat']) {
            throw new \Exception('The token is perempted.');
        }

        return $data;
    }

    public function getJwtPayload(string $token): array
    {
        $parts = explode('.', $token);
        $data = json_decode(base64_decode($parts[1]), true);

        return $data;
    }
}
