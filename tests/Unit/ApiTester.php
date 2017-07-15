<?php

namespace Tests\Unit;

use App\Models\User;
use Helpers\AuthEnum;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiTester extends TestCase
{
  protected $connectedUser;

  protected $authEnum = AuthEnum::CORRECT;

  public function setUp() {
    parent::setUp();
    Artisan::call('migrate');

    $this->createConnectedUser();
  }

  public function getJson($url, array $parameters = [], array $headers = []) {
    return $this->callJson($url, 'GET', $parameters, $headers);
  }

  public function postJson($url, array $parameters = [], array $headers = []) {
    return $this->callJson($url, 'POST', $parameters, $headers);
  }

  public function putJson($url, array $parameters = [], array $headers = []) {
    return $this->callJson($url, 'PUT', $parameters, $headers);
  }

  public function deleteJson($url, array $parameters = [], array $headers = []) {
    return $this->callJson($url, 'DELETE', $parameters, $headers);
  }

  protected function callJson($url, $method = 'GET', array $parameters = [], array $headers) {
    $authHeaders = [];

    switch ($this->authEnum) {
      case AuthEnum::NONE :
        break;

      case AuthEnum::WRONG :
        $authHeaders = ['Authorization' => 'Bearer wrongtoken.wrongtoken.wrongtoken'];
        break;

      case AuthEnum::CORRECT :
      default :
        $this->createConnectedUser();
        $token = $this->authenticate();
        $authHeaders = ['Authorization' => 'Bearer ' . $token];
        break;
    }

    return $this->json($method, $url, $parameters, array_merge($headers, $authHeaders));
  }

  /**
   * Creates a User into the database
   *
   * @param array $data
   * @return User
   */
  protected function createConnectedUser(array $data = []): User {
    if (!$this->connectedUser) {
      $this->connectedUser = factory(User::class)->create(array_merge([
        'email' => 'testing@testing.com',
        'password' => bcrypt('password')
      ], $data));
    }

    return $this->connectedUser;
  }

  protected function setAuthentication(int $authEnum) {
    $this->authEnum = $authEnum;

    return $this;
  }

  /**
   * Creates a User into the database and automatically log in
   * @return string
   */
  protected function authenticate() {
    return JWTAuth::fromUser($this->connectedUser);
  }

  protected function createUrl($url, ...$args) {
    foreach ($args as $arg) {
      $pos = strpos($url, '%d');
      if ($pos !== false) {
        $url = substr_replace($url, $arg, $pos, strlen('%d'));
      }
    }
    $url = str_replace('/%d', '', $url);

    return $url;
  }
}
