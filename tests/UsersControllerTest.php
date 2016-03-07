<?php

use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Underscore\Types\Arrays;

class UsersControllerTest extends ApiTester
{
  protected $url = 'api/v1/users';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp()
  {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_users()
  {
    factory(App\Models\User::class, 3)->create();

    $call = $this->getJson($this->url);

    $call->seeJsonStructure([
        'data',
        'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_users_400_if_not_authenticated()
  {
    factory(App\Models\User::class, 3)->create();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->url);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_users_400_if_wrong_authentication()
  {
    factory(App\Models\User::class, 3)->create();

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->url);

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_user()
  {
    $user = factory(App\Models\User::class, 1)->create();

    $call = $this->getJson($this->url . '/' . $user->id);

    $this->assertResponseOk();
    $call->seeJsonEquals([
        'data' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email
        ]
    ]);
  }

  /** @test */
  public function it_fetches_a_single_user_400_if_not_authenticated()
  {
    $user = factory(App\Models\User::class, 1)->create();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->url . '/' . $user->id);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_user_400_if_wrong_authentication()
  {
    $user = factory(App\Models\User::class)->create();

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->url . '/' . $user->id);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_user_404_if_a_user_is_not_found()
  {
    $this->getJson($this->url . '/0');
    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_creates_a_new_user_given_valid_parameters()
  {
    $user = factory(App\Models\User::class)->make();
    $data = Arrays::merge($user->toArray(), ['password' => 'password']);

    $this->postJson($this->url, $data);

    $this->assertResponseStatus(201);
  }

  /** @test */
  public function it_throw_a_bad_request_error_if_a_new_user_request_fails_validation()
  {
    $this->postJson($this->url, []);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_given_valid_parameters()
  {
    $connectedUser = $this->createConnectedUser();

    $call = $this->putJson($this->url . '/' . $connectedUser->id, [
        'name' => "New Name"
    ]);

    $this->assertResponseStatus(200);
    $call->seeJson([
        'data' => [
            'id'    => $this->connectedUser->id,
            'email' => $this->connectedUser->email,
            'name'  => "New Name"
        ]
    ]);
  }

  /** @test */
  public function it_updates_the_connected_user_400_if_wrong_validation()
  {
    $connectedUser = $this->createConnectedUser();

    $this->putJson($this->url . '/' . $connectedUser->id, [
        'email' => "wrongemail"
    ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_400_error_if_not_authenticated_for_update()
  {
    $connectedUser = $this->createConnectedUser();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->putJson($this->url . '/' . $connectedUser->id, [
            'name' => "New Name"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_400_error_if_wrong_authenticated_for_update()
  {
    $connectedUser = $this->createConnectedUser();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->putJson($this->url . '/' . $connectedUser->id, [
            'name' => "New Name"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_connected_user_403_error_if_a_user_is_not_authorized_to_update_user()
  {
    $this->createConnectedUser();

    $user = factory(App\Models\User::class, 1)->create();

    $this->putJson($this->url . '/' . $user->id, [
        'name' => "New name"
    ]);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_updates_the_connected_user_404_error_if_an_updated_user_does_not_exists()
  {
    $this->createConnectedUser();

    $this->putJson($this->url . '/0', [
        'name' => "New name"
    ]);

    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_deletes_a_user()
  {
    $connectedUser = $this->createConnectedUser();

    $this->deleteJson($this->url . '/' . $connectedUser->id);

    $this->assertResponseStatus(204);
  }

  /** @test */
  public function it_deletes_a_user_403_error_if_a_user_is_not_authorized_to_delete_user()
  {
    $this->createConnectedUser();

    $user = factory(App\Models\User::class, 1)->create();

    $this->deleteJson($this->url . '/' . $user->id);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_throws_a_404_error_if_a_deleted_user_does_not_exists()
  {
    $this->createConnectedUser();

    $this->deleteJson($this->url . '/0');

    $this->assertResponseStatus(404);
  }
}
