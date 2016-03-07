<?php

use Helpers\AuthEnum;
use Underscore\Types\Arrays;

class ApiTester extends TestCase
{
  protected $connectedUser;

  protected $authEnum = AuthEnum::CORRECT;

  public function setUp()
  {
    parent::setUp();
    Artisan::call('migrate');
  }

  protected function getJson($url, array $parameters = [], array $headers = [])
  {
    return $this->callJson($url, 'GET', $parameters, $headers);
  }

  protected function postJson($url, array $parameters = [], array $headers = [])
  {
    return $this->callJson($url, 'POST', $parameters, $headers);
  }

  protected function putJson($url, array $parameters = [], array $headers = [])
  {
    return $this->callJson($url, 'PUT', $parameters, $headers);
  }

  protected function deleteJson($url, array $parameters = [], array $headers = [])
  {
    return $this->callJson($url, 'DELETE', $parameters, $headers);
  }

  protected function callJson($url, $method = 'GET', array $parameters = [], array $headers)
  {
    $authHeaders = [];
    switch ($this->authEnum) {
      case AuthEnum::CORRECT :
        $this->createConnectedUser();
        $token = $this->authenticate();
        $authHeaders = ['HTTP_Authorization' => 'Bearer' . $token];
        break;

      case AuthEnum::WRONG :
        $authHeaders = ['HTTP_Authorization' => 'Bearer wrongtoken.wrongtoken.wrongtoken'];
        break;
    }

    return $this->json($method, $url, $parameters, Arrays::merge($headers, $authHeaders));
  }

  /**
   * Creates a User into the database
   *
   * @param array $data
   *
   * @return static
   */
  protected function createConnectedUser(array $data = [])
  {
    if (!$this->connectedUser) {
      $this->connectedUser = factory(App\Models\User::class)->create(Arrays::merge([
          'email'    => 'testing@testing.com',
          'password' => bcrypt('password')
      ], $data));
    }

    return $this->connectedUser;
  }

  /**
   * Creates a User into the database and automatically log in
   * @return static
   */
  protected function authenticate()
  {
    return JWTAuth::fromUser($this->connectedUser);
  }

  protected function setAuthentication(int $authEnum)
  {
    $this->authEnum = $authEnum;

    return $this;
  }
}