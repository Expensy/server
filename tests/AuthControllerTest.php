<?php


use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthControllerTest extends TestCase
{
  use DatabaseMigrations;

  protected $credentials;

  public function setUp() {
    parent::setUp();

    $this->credentials = [
      'email' => 'testing-auth@testing.com',
      'password' => bcrypt('password')
    ];

    factory(App\Models\User::class)->create([
      'email' => $this->credentials['email'],
      'password' => bcrypt($this->credentials['password'])
    ]);
  }

  /** @test */
  public function it_sets_token() {
    $call = $this->json('POST', 'api/authenticate', $this->credentials);

    $call->assertResponseOK();
  }

  /** @test */
  public function it_returns_unauthorized_response_for_invalid_credentials() {
    $response = $this->json('POST', 'api/authenticate', [
      'email' => 'testing@testing.com',
      'password' => 'secret'
    ]);

    $response->assertResponseStatus(401);
  }
}
