<?php

use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Underscore\Types\Arrays;

class ProjectsControllerTest extends ApiTester
{
  protected $url = 'api/v1/projects/%d';
  protected $membersUrl = 'api/v1/projects/%d/members/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp()
  {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_projects()
  {
    $connectedUser = $this->connectedUser;

    factory(App\Models\Project::class, 3)
        ->create()
        ->each(function ($p) use ($connectedUser) {
          $p->users()->attach($connectedUser->id);
        });

    $call = $this->getJson($this->createUrl($this->url));

    $this->assertResponseOk();

    $call->seeJsonStructure([
        'items' => [
            '*' => [
                'id', 'title', 'members'
            ]
        ],
        'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_projects_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->createUrl($this->url));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_projects_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url));

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_project()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $call = $this->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseOk();
    $call->seeJson([
        'id'    => $project->id,
        'title' => $project->title,
    ]);
  }

  /** @test */
  public function it_fetches_a_single_project_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_project_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_project_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $this->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_fetches_a_single_project_404_if_not_found()
  {
    $this->getJson($this->createUrl($this->url, 0));
    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_creates_a_new_project()
  {
    $project = factory(App\Models\Project::class)->make();
    $data = $project->toArray();

    $this->postJson($this->createUrl($this->url), $data);

    $this->assertResponseStatus(201);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_validation_fails()
  {
    $this->postJson($this->createUrl($this->url), []);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->make();
    $data = $project->toArray();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->postJson($this->createUrl($this->url), $data);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_project_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->make();
    $data = $project->toArray();

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->postJson($this->createUrl($this->url), $data);

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_updates_the_project()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $call = $this->putJson($this->createUrl($this->url, $project->id), [
        'id'    => $project->id,
        'title' => "New Title"
    ]);

    $this->assertResponseStatus(200);
    $call->seeJson([
        'id'    => $project->id,
        'title' => "New Title",
    ]);
  }

  /** @test */
  public function it_updates_the_project_400_if_validation_fails()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->putJson($this->createUrl($this->url, $project->id), []);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_project_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->putJson($this->createUrl($this->url, $project->id), [
            'id'    => $project->id,
            'title' => "New Title"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_project_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->putJson($this->createUrl($this->url, $project->id), [
            'id'    => $project->id,
            'title' => "New Title"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_project_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $this->putJson($this->createUrl($this->url, $project->id), [
        'id'    => $project->id,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_updates_the_project_404_if_not_found()
  {
    $this->putJson($this->createUrl($this->url, 0), [
        'id'    => 0,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_deletes_a_project()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->deleteJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(204);
  }

  /** @test */
  public function it_deletes_the_project_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->deleteJson($this->createUrl($this->url, $project->id));


    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_the_project_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->deleteJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_a_project_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $this->deleteJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_deletes_a_project_404_if_not_found()
  {
    $this->deleteJson($this->createUrl($this->url, 0));

    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_adds_member()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $user = factory(App\Models\User::class)->create();

    $this->putJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $this->assertResponseStatus(200);
  }

  /** @test */
  public function it_adds_member_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $user = factory(App\Models\User::class)->create();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->putJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_adds_member_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $user = factory(App\Models\User::class)->create();

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->putJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_adds_member_403_if_forbidden()
  {
    $user1 = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user1->id);

    $user2 = factory(App\Models\User::class)->create();

    $this->putJson($this->createUrl($this->membersUrl, $project->id, $user2->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_adds_member_404_if_project_not_found()
  {
    $user = factory(App\Models\User::class)->create();

    $this->putJson($this->createUrl($this->membersUrl, 0, $user->id));

    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_adds_member_404_if_user_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $this->putJson($this->createUrl($this->membersUrl, $project->id, 0));

    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_deletes_member()
  {
    $user = factory(App\Models\User::class)->create();

    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $project->users()->attach($user->id);

    $this->deleteJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $this->assertResponseStatus(200);
  }

  /** @test */
  public function it_deletes_member_400_if_not_authenticated()
  {
    $user = factory(App\Models\User::class)->create();

    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $project->users()->attach($user->id);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->deleteJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_member_400_if_wrong_authentication()
  {
    $user = factory(App\Models\User::class)->create();

    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $project->users()->attach($user->id);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->deleteJson($this->createUrl($this->membersUrl, $project->id, $user->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_member_403_if_forbidden()
  {
    $user1 = factory(App\Models\User::class)->create();
    $user2 = factory(App\Models\User::class)->create();

    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user1->id);
    $project->users()->attach($user2->id);

    $this->deleteJson($this->createUrl($this->membersUrl, $project->id, $user2->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_deletes_member_404_if_project_not_found()
  {
    $user = factory(App\Models\User::class)->create();

    $this->deleteJson($this->createUrl($this->membersUrl, 0, $user->id));

    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_deletes_member_404_if_user_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->deleteJson($this->createUrl($this->membersUrl, $project->id, 0));

    $this->assertResponseStatus(404);
  }

}
