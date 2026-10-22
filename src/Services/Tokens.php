<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class Tokens
{
  private const string JWT_ALGORITHM = 'HS256';

  public static function verify(string $token): object|null|bool
  {
    try {
      return JWT::decode($token, new Key(getenv('JWT_SECRET'), self::JWT_ALGORITHM));
    } catch (ExpiredException) {
      return false;
    } catch (\Exception $e) {
      return null;
    }
  }

  public static function sign(array $payload, int $exp = 3600): array
  {
    $exp += time();
    return [
      'exp' => $exp,
      'token' => JWT::encode(
        [
          'iss' => $_SERVER['HTTP_HOST'],
          'exp' => $exp,
          'data' => $payload
        ],
        getenv('JWT_SECRET'),
        self::JWT_ALGORITHM
      )
    ];
  }
}