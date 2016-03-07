<?php

namespace App\Repositories;

use App\Models\Enum\AuthEnum;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthRepository
{

  public function authenticate($credentials)
  {
    try {
      if (!$token = JWTAuth::attempt($credentials)) {
        return [
            'error' => AuthEnum::FORBIDDEN
        ];
      }
    } catch (JWTException $e) {
      return [
          'error' => AuthEnum::INTERNAL_ERROR
      ];
    }

    return [
        'error' => AuthEnum::SUCCESS,
        'token' => $token
    ];
  }
}