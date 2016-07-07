<?php

use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Underscore\Types\Arrays;

class UsersControllerTest extends ApiTester {
  protected $url = 'api/v1/users/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp() {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_users() {
    factory(App\Models\User::class, 3)->create();

    $call = $this->getJson($this->createUrl($this->url));

    $this->assertResponseOk();

    $call->seeJsonStructure([
      'items' => [
        '*' => ['id', 'first_name', 'last_name', 'email']
      ],
      'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_users_400_if_not_authenticated() {
    factory(App\Models\User::class, 3)->create();

    $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_users_400_if_wrong_authentication() {
    factory(App\Models\User::class, 3)->create();

    $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url));

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_user() {
    $user = factory(App\Models\User::class, 1)->create();

    $call = $this->getJson($this->createUrl($this->url, $user->id));

    $this->assertResponseOk();
    $call->seeJsonEquals([
      'id' => $user->id,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name,
      'email' => $user->email,
      'projects' => []
    ]);
  }

  /** @test */
  public function it_fetches_the_connected_user() {
    $call = $this->getJson($this->createUrl($this->url, "current"));

    $user = Auth::user();
    $this->assertResponseOk();
    $call->seeJsonEquals([
      'id' => $user->id,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name,
      'email' => $user->email,
      'projects' => []
    ]);
  }

  /** @test */
  public function it_fetches_a_single_user_400_if_not_authenticated() {
    $user = factory(App\Models\User::class, 1)->create();

    $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url, $user->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_user_400_if_wrong_authentication() {
    $user = factory(App\Models\User::class)->create();

    $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url, $user->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_user_404_if_not_found() {
    $this->getJson($this->createUrl($this->url, 0));
    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_creates_a_new_user() {
    $user = factory(App\Models\User::class)->make();
    $data = Arrays::merge($user->toArray(), [
      'password' => 'password',
      'password_confirmation' => 'password'
    ]);

    $call = $this->postJson($this->createUrl($this->url), $data);

    $this->assertResponseStatus(201);
    $call->seeJson([
      'id' => 2, //TODO fix this number
      'email' => $user->email,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name
    ]);
  }

  /** @test */
  public function it_creates_a_new_user_400_if_validation_fails() {
    $this->postJson($this->createUrl($this->url), []);

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_updates_the_connected_user() {
    $connectedUser = $this->createConnectedUser();

    $call = $this->putJson($this->createUrl($this->url, $connectedUser->id), Arrays::merge($connectedUser->toArray(), [
      'first_name' => "New",
      'last_name' => "Name"
    ]));

    $this->assertResponseStatus(200);
    $call->seeJson([
      'id' => $this->connectedUser->id,
      'email' => $this->connectedUser->email,
      'first_name' => "New",
      'last_name' => "Name"
    ]);
  }

  /** @test */
  public function it_updates_the_connected_user_400_if_wrong_validation() {
    $connectedUser = $this->createConnectedUser();

    $this
      ->setAuthentication(AuthEnum::WRONG)
      ->putJson($this->createUrl($this->url, $connectedUser->id), [
        'email' => "wrongemail"
      ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_400_if_not_authenticated_() {
    $connectedUser = $this->createConnectedUser();

    $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->url, $connectedUser->id), [
        'first_name' => "New",
        'last_name' => "Name"
      ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_400_if_wrong_authenticated() {
    $connectedUser = $this->createConnectedUser();

    $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->url, $connectedUser->id), [
        'first_name' => "New",
        'last_name' => "Name"
      ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_403_if_forbidden() {
    $this->createConnectedUser();

    $user = factory(App\Models\User::class, 1)->create();

    $this->putJson($this->createUrl($this->url, $user->id), [
      'first_name' => "New",
      'last_name' => "Name"
    ]);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_updates_the_connected_user_404_if_not_found() {
    $this->createConnectedUser();

    $this->putJson($this->createUrl($this->url, 0), [
      'first_name' => "New",
      'last_name' => "Name"
    ]);

    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_deletes_a_user() {
    $connectedUser = $this->createConnectedUser();

    $this->deleteJson($this->createUrl($this->url, $connectedUser->id));

    $this->assertResponseStatus(204);
  }

  /** @test */
  public function it_deletes_a_user_400_if_not_authenticated() {
    $connectedUser = $this->createConnectedUser();

    $this
      ->setAuthentication(AuthEnum::NONE)
      ->deleteJson($this->createUrl($this->url, $connectedUser->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_a_user_400_if_wrong_authentication() {
    $connectedUser = $this->createConnectedUser();

    $this
      ->setAuthentication(AuthEnum::WRONG)
      ->deleteJson($this->createUrl($this->url, $connectedUser->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_a_user_403_if_forbidden() {
    $this->createConnectedUser();

    $user = factory(App\Models\User::class, 1)->create();

    $this->deleteJson($this->createUrl($this->url, $user->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_throws_a_404_if_not_found() {
    $this->createConnectedUser();

    $this->deleteJson($this->createUrl($this->url, 0));

    $this->assertResponseStatus(404);
  }
}
