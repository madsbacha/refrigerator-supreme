<?php
use \Firebase\JWT\JWT;

// https://github.com/firebase/php-jwt

if (class_exists('JWTHelper')) {
    return new JWTHelper();
}

class JWTHelper {
    static function getConfig () {
        $config = include __DIR__.'/config.php';
        return $config['jwt'];
    }
    static function getKey () {
        return self::getConfig()['key'];
    }
    static function createPayload ($user) {
        $jwt_config = self::getConfig();
        return [
            'iss' => $jwt_config['issuer'],
            'aud' => $jwt_config['audience'],
            'user' => $user
        ];
    }
    static function encode($user) {
        return JWT::encode(
            self::createPayload($user),
            self::getKey()
        );
    }
    static function decode($token) {
        return JWT::decode(
            $token,
            self::getKey(),
            self::getConfig()['supported_algorithms']
        );
    }
}

return new JWTHelper();
