<?php

namespace Tests\Unit;

use App\Models\User;
use Auth;
use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UsersControllerTest extends ApiTester
{
  protected $url = 'api/v1/users/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp() {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_users() {
    factory(User::class, 3)->create();

    $response = $this->getJson($this->createUrl($this->url));

    $response->assertSuccessful();

    $response->assertJsonStructure([
      'items' => [
        '*' => ['id', 'first_name', 'last_name', 'email']
      ],
      'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_users_400_if_not_authenticated() {
    factory(User::class, 3)->create();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_users_400_if_wrong_authentication() {
    factory(User::class, 3)->create();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url));

    $response->assertStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_user() {
    $user = factory(User::class)->create();

    $response = $this->getJson($this->createUrl($this->url, $user->id));

    $response->assertSuccessful();
    $response->assertExactJson([
      'id' => $user->id,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name,
      'email' => $user->email,
      'projects' => []
    ]);
  }

  /** @test */
  public function it_fetches_the_connected_user() {
    $response = $this->getJson($this->createUrl($this->url, "current"));

    $user = Auth::user();
    $response->assertSuccessful();
    $response->assertExactJson([
      'id' => $user->id,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name,
      'email' => $user->email,
      'projects' => []
    ]);
  }

  /** @test */
  public function it_fetches_a_single_user_400_if_not_authenticated() {
    $user = factory(User::class)->create();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url, $user->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_user_400_if_wrong_authentication() {
    $user = factory(User::class)->create();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url, $user->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_user_404_if_not_found() {
    $response = $this->getJson($this->createUrl($this->url, 0));
    $response->assertStatus(404);
  }


  /** @test */
  public function it_creates_a_new_user() {
    $user = factory(User::class)->make();
    $data = array_merge($user->toArray(), [
      'password' => 'password',
      'password_confirmation' => 'password'
    ]);

    $response = $this->postJson($this->createUrl($this->url), $data);

    $response->assertStatus(201);
    $response->assertJson([
      'id' => 2, //TODO fix this number
      'email' => $user->email,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name
    ]);
  }

  /** @test */
  public function it_creates_a_new_user_400_if_validation_fails() {
    $response = $this->postJson($this->createUrl($this->url), []);

    $response->assertStatus(400);
  }


  /** @test */
  public function it_updates_the_connected_user() {
    $connectedUser = $this->createConnectedUser();

    $response = $this->putJson($this->createUrl($this->url, $connectedUser->id), array_merge($connectedUser->toArray(), [
      'first_name' => "New",
      'last_name' => "Name"
    ]));

    $response->assertStatus(200);
    $response->assertJson([
      'id' => $this->connectedUser->id,
      'email' => $this->connectedUser->email,
      'first_name' => "New",
      'last_name' => "Name"
    ]);
  }

  /** @test */
  public function it_updates_the_connected_user_400_if_wrong_validation() {
    $connectedUser = $this->createConnectedUser();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->putJson($this->createUrl($this->url, $connectedUser->id), [
        'email' => "wrongemail"
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_400_if_not_authenticated_() {
    $connectedUser = $this->createConnectedUser();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->url, $connectedUser->id), [
        'first_name' => "New",
        'last_name' => "Name"
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_400_if_wrong_authenticated() {
    $connectedUser = $this->createConnectedUser();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->url, $connectedUser->id), [
        'first_name' => "New",
        'last_name' => "Name"
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_403_if_forbidden() {
    $this->createConnectedUser();

    $user = factory(User::class)->create();

    $response = $this->putJson($this->createUrl($this->url, $user->id), [
      'first_name' => "New",
      'last_name' => "Name"
    ]);

    $response->assertStatus(403);
  }

  /** @test */
  public function it_updates_the_connected_user_404_if_not_found() {
    $this->createConnectedUser();

    $response = $this->putJson($this->createUrl($this->url, 0), [
      'first_name' => "New",
      'last_name' => "Name"
    ]);

    $response->assertStatus(404);
  }

  /** @test */
  public function it_deletes_a_user() {
    $connectedUser = $this->createConnectedUser();

    $response = $this->deleteJson($this->createUrl($this->url, $connectedUser->id));

    $response->assertStatus(204);
  }

  /** @test */
  public function it_deletes_a_user_400_if_not_authenticated() {
    $connectedUser = $this->createConnectedUser();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->deleteJson($this->createUrl($this->url, $connectedUser->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_a_user_400_if_wrong_authentication() {
    $connectedUser = $this->createConnectedUser();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->deleteJson($this->createUrl($this->url, $connectedUser->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_a_user_403_if_forbidden() {
    $this->createConnectedUser();

    $user = factory(User::class)->create();

    $response = $this->deleteJson($this->createUrl($this->url, $user->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_throws_a_404_if_not_found() {
    $this->createConnectedUser();

    $response = $this->deleteJson($this->createUrl($this->url, 0));

    $response->assertStatus(404);
  }
}
