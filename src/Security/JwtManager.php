<?php

namespace App\Security;

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

class JwtManager
{
    public function __construct(private string $secret)
    {
    }

    protected function createHash($headerAndPlayload): string
    {
        return base64UrlEncode(hash_hmac("sha256", $headerAndPlayload, $this->secret, true));
    }

    /**
     * @return JWT
     */
    public function createJwt(array $payload): string
    {
        if (!array_key_exists('iat', $payload)) $payload['iat'] = time() + 2592000;
        $headerAndPlayload = base64UrlEncode('{"alg":"HS256","typ":"JWT"}') . '.'
            . base64UrlEncode(json_encode($payload));
        $hash = $this->createHash($headerAndPlayload);
        $jwt = $headerAndPlayload . '.' . $hash;

        return $jwt;
    }

    /**
     * @return JWT payload
     */
    public function verifyJwt(string $token): array
    {
        // 0. Verify the token format
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid JWT.');
        }

        // 1. Verify the signature of the JWT
        $hash = $this->createHash($parts[0] . "." . $parts[1]);
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
