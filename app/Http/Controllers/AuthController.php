<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Enum\AuthEnum;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
  protected $authRepository;

  public function __construct(AuthRepository $authRepository)
  {
    $this->authRepository = $authRepository;
  }

  public function authenticate(Request $request)
  {
    $credentials = $request->only('email', 'password');


    $auth = $this->authRepository->authenticate($credentials);

    if ($auth['error'] == AuthEnum::FORBIDDEN) {
      return $this->respondForbidden();

    } else if ($auth['error'] == AuthEnum::INTERNAL_ERROR) {
      return $this->respondInternalError('Can not create token');
    }

    return $this->respond([
        'token' => $auth['token']
    ]);
  }
}