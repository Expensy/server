<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectsControllerTest extends ApiTester
{
  protected $url = 'api/v1/projects/%d';
  protected $membersUrl = 'api/v1/projects/%d/members/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp() {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_active_projects() {
    $connectedUser = $this->connectedUser;

    $projects = factory(Project::class, 3)
      ->create()
      ->each(function ($p) use ($connectedUser) {
        $p->users()->attach($connectedUser->id);
      });

    $projects[2]->delete();

    $response = $this->getJson($this->createUrl($this->url));

    $response->assertSuccessful();
    $jsonObj = json_decode($response->baseResponse->content());
    $this->assertCount(2, $jsonObj->items);
    $response->assertJsonStructure([
      'items' => [
        '*' => [
          'id', 'title', 'currency', 'members', 'categories'
        ]
      ],
      'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_archived_projects() {
    $connectedUser = $this->connectedUser;

    $projects = factory(Project::class, 3)
      ->create()
      ->each(function ($p) use ($connectedUser) {
        $p->users()->attach($connectedUser->id);
      });
    $projects[0]->delete();

    $response = $this->getJson($this->createUrl('api/v1/projects/archived'));

    $response->assertSuccessful();
    $jsonObj = json_decode($response->baseResponse->content());
    $this->assertCount(1, $jsonObj->items);
    $response->assertJsonStructure([
      'items' => [
        '*' => [
          'id', 'title', 'currency', 'members', 'categories'
        ]
      ],
      'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_projects_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_projects_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url));

    $response->assertStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_project() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->getJson($this->createUrl($this->url, $project->id));

    $response->assertSuccessful();
    $response->assertJson([
      'id' => $project->id,
      'title' => $project->title,
      'currency' => $project->currency
    ]);
  }

  /** @test */
  public function it_fetches_a_single_project_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url, $project->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_project_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url, $project->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_project_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $response = $this->getJson($this->createUrl($this->url, $project->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_fetches_a_single_project_404_if_not_found() {
    $response = $this->getJson($this->createUrl($this->url, 0));
    $response->assertStatus(404);
  }


  /** @test */
  public function it_creates_a_new_project() {
    $project = factory(Project::class)->make();
    $data = $project->toArray();

    $response = $this->postJson($this->createUrl($this->url), $data);

    $obj = json_decode($response->baseResponse->getContent());
    $categories = Project::find($obj->id)->categories->all();

    $response->assertStatus(201);

    $this->assertEquals(1, count($categories));

    $category = $categories[0];
    $this->assertEquals("Category 1", $category->title);
    $this->assertEquals("#419fdb", $category->color);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_validation_fails() {
    $response = $this->postJson($this->createUrl($this->url), []);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_title_already_taken() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->postJson($this->createUrl($this->url), [
      'title' => $project->title
    ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_currency_invalid() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->postJson($this->createUrl($this->url), [
      'currency' => 'INVALID'
    ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_not_authenticated() {
    $project = factory(Project::class)->make();
    $data = $project->toArray();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->postJson($this->createUrl($this->url), $data);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_wrong_authentication() {
    $project = factory(Project::class)->make();
    $data = $project->toArray();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->postJson($this->createUrl($this->url), $data);

    $response->assertStatus(400);
  }


  /** @test */
  public function it_updates_the_project() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->putJson($this->createUrl($this->url, $project->id), [
      'id' => $project->id,
      'title' => "New Title",
      'currency' => 'EUR'
    ]);

    $response->assertStatus(200);
    $response->assertJson([
      'id' => $project->id,
      'title' => "New Title",
      'currency' => 'EUR'
    ]);
  }

  /** @test */
  public function it_updates_the_project_400_if_validation_fails() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->putJson($this->createUrl($this->url, $project->id), []);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_project_400_if_title_already_taken() {
    $connectedUser = $this->connectedUser;

    $projects = factory(Project::class, 2)
      ->create()
      ->each(function ($p) use ($connectedUser) {
        $p->users()->attach($connectedUser->id);
      });

    $response = $this->putJson($this->createUrl($this->url, $projects[0]->id), [
      'id' => $projects[0]->id,
      'title' => $projects[1]->title
    ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_project_400_if_currency_invalid() {
    $connectedUser = $this->connectedUser;

    $projects = factory(Project::class, 2)
      ->create()
      ->each(function ($p) use ($connectedUser) {
        $p->users()->attach($connectedUser->id);
      });

    $response = $this->putJson($this->createUrl($this->url, $projects[0]->id), [
      'id' => $projects[0]->id,
      'currency' => 'INVALID'
    ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_project_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->url, $project->id), [
        'id' => $project->id,
        'title' => "New Title"
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_project_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->putJson($this->createUrl($this->url, $project->id), [
        'id' => $project->id,
        'title' => "New Title"
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_project_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $response = $this->putJson($this->createUrl($this->url, $project->id), [
      'id' => $project->id,
      'title' => "New title"
    ]);

    $response->assertStatus(403);
  }

  /** @test */
  public function it_updates_the_project_404_if_not_found() {
    $response = $this->putJson($this->createUrl($this->url, 0), [
      'id' => 0,
      'title' => "New title"
    ]);

    $response->assertStatus(404);
  }


  /** @test */
  public function it_deletes_a_project() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->deleteJson($this->createUrl($this->url, $project->id));

    $response->assertStatus(204);
  }

  /** @test */
  public function it_deletes_the_project_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->deleteJson($this->createUrl($this->url, $project->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_the_project_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->deleteJson($this->createUrl($this->url, $project->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_a_project_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $response = $this->deleteJson($this->createUrl($this->url, $project->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_deletes_a_project_404_if_not_found() {
    $response = $this->deleteJson($this->createUrl($this->url, 0));

    $response->assertStatus(404);
  }


  /** @test */
  public function it_adds_member() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $user = factory(User::class)->create();

    $response = $this->putJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $response->assertStatus(200);
  }

  /** @test */
  public function it_adds_member_400_if_already_added() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $user = factory(User::class)->create();
    $project->users()->attach($user->id);

    $response = $this->putJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $response->assertStatus(400);
  }


  /** @test */
  public function it_adds_member_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $user = factory(User::class)->create();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_adds_member_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $user = factory(User::class)->create();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->putJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_adds_member_403_if_forbidden() {
    $user1 = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user1->id);

    $user2 = factory(User::class)->create();

    $response = $this->putJson($this->createUrl($this->membersUrl, $project->id, $user2->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_adds_member_404_if_project_not_found() {
    $user = factory(User::class)->create();

    $response = $this->putJson($this->createUrl($this->membersUrl, 0, $user->id));

    $response->assertStatus(404);
  }

  /** @test */
  public function it_adds_member_404_if_user_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $response = $this->putJson($this->createUrl($this->membersUrl, $project->id, 0));

    $response->assertStatus(404);
  }


  /** @test */
  public function it_deletes_member() {
    $user = factory(User::class)->create();

    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $project->users()->attach($user->id);

    $response = $this->deleteJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $response->assertStatus(200);
  }

  /** @test */
  public function it_deletes_member_400_if_not_authenticated() {
    $user = factory(User::class)->create();

    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $project->users()->attach($user->id);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->deleteJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_member_400_if_wrong_authentication() {
    $user = factory(User::class)->create();

    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $project->users()->attach($user->id);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->deleteJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_member_403_if_forbidden() {
    $user1 = factory(User::class)->create();
    $user2 = factory(User::class)->create();

    $project = factory(Project::class)->create();
    $project->users()->attach($user1->id);
    $project->users()->attach($user2->id);

    $response = $this->deleteJson($this->createUrl($this->membersUrl, $project->id, $user2->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_deletes_member_404_if_project_not_found() {
    $user = factory(User::class)->create();

    $response = $this->deleteJson($this->createUrl($this->membersUrl, 0, $user->id));

    $response->assertStatus(404);
  }

  /** @test */
  public function it_deletes_member_404_if_user_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->deleteJson($this->createUrl($this->membersUrl, $project->id, 0));

    $response->assertStatus(404);
  }
}
