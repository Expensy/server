<?php

namespace App\Repositories;

use App\Models\Enum\AuthEnum;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthRepository
{

  public function authenticate($credentials)
  {
    return JWTAuth::attempt($credentials);
  }
}