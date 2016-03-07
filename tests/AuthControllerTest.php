<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthControllerTest extends TestCase
{
  use DatabaseMigrations, DatabaseTransactions;

  protected $credentials;

  public function setUp()
  {
    parent::setUp();

    $this->credentials = [
        'email'    => 'testing@testing.com',
        'password' => bcrypt('password')
    ];

    factory(App\Models\User::class)->create([
        'email'    => $this->credentials['email'],
        'password' => bcrypt($this->credentials['password'])
    ]);
  }

  /** @test */
  public function it_sets_token()
  {
    $call = $this->json('POST', 'api/authenticate', $this->credentials);

    $call->seeJsonStructure([
        'token'
    ]);
  }

  /** @test */
  public function it_returns_forbidden_response_for_invalid_credentials()
  {
    $response = $this->json('POST', 'api/authenticate', [
        'email'    => 'testing@testing.com',
        'password' => 'secret'
    ]);

    $response->assertResponseStatus(403);
  }
}