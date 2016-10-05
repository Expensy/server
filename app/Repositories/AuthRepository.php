<?php

namespace App\Repositories;

use JWTAuth;

class AuthRepository
{
  public function authenticate($credentials) {
    return JWTAuth::attempt($credentials);
  }
}
