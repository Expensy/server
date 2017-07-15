<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

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

    factory(User::class)->create([
      'email' => $this->credentials['email'],
      'password' => bcrypt($this->credentials['password'])
    ]);
  }

  /** @test */
  public function it_sets_token() {
    $response = $this->json('POST', 'api/authenticate', $this->credentials);

    $response->assertSuccessful();
  }

  /** @test */
  public function it_returns_unauthorized_response_for_invalid_credentials() {
    $response = $this->json('POST', 'api/authenticate', [
      'email' => 'testing@testing.com',
      'password' => 'secret'
    ]);

    $response->assertStatus(401);
  }
}
