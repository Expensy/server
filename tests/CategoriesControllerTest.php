<?php

use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoriesControllerTest extends ApiTester
{
  protected $url = 'api/v1/projects/%d/categories/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp()
  {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_categories()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(App\Models\Category::class, 3)->create([
        'project_id' => $project->id
    ]);

    $call = $this->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseOk();

    $call->seeJsonStructure([
        'items' => [
            '*' => [
                'id', 'title', 'color'
            ]
        ],
        'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_categories_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(App\Models\Category::class, 3)->create([
        'project_id' => $project->id
    ]);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_categories_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(App\Models\Category::class, 3)->create([
        'project_id' => $project->id
    ]);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_categories_404_if_category_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(App\Models\Category::class, 3)->create([
        'project_id' => $project->id
    ]);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url, 0));

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_category()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $call = $this->getJson($this->createUrl($this->url, $project->id, $category->id));

    $this->assertResponseOk();
    $call->seeJson([
        'id'    => $category->id,
        'title' => $category->title,
        'color' => $category->color,
    ]);
  }

  /** @test */
  public function it_fetches_a_single_category_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->createUrl($this->url, $project->id, $category->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_category_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url, $project->id, $category->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_category_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this->getJson($this->createUrl($this->url, $project->id, $category->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_fetches_a_single_category_404_project_if_not_found()
  {
    $this->getJson($this->createUrl($this->url, 0, 0));
    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_fetches_a_single_category_404_category_if_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->getJson($this->createUrl($this->url, $project->id, 0));
    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_creates_a_new_category()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->make();
    $data = $category->toArray();

    $this->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(201);
  }

  /** @test */
  public function it_creates_a_new_category_v2()
  {
    $project1 = factory(App\Models\Project::class)->create();
    $project1->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project1->id
    ]);

    $project2 = factory(App\Models\Project::class)->create();
    $project2->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->make([
        'title' => $category->title
    ]);

    $data = $category->toArray();
    $this->postJson($this->createUrl($this->url, $project2->id), $data);

    $this->assertResponseStatus(201);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_validation_fails()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->postJson($this->createUrl($this->url, $project->id), []);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_title_already_taken()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category1 = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $category2 = factory(App\Models\Category::class)->make([
        'title' => $category1->title
    ]);

    $data = $category2->toArray();
    $this->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_color_not_hexadecimal()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->make([
        'color' => "123456"
    ]);
    $data = $category->toArray();

    $this->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_creates_a_new_category_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->make();
    $data = $category->toArray();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->make();
    $data = $category->toArray();

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(App\Models\Category::class)->make();
    $data = $category->toArray();

    $this->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_creates_a_new_category_404_if_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->make();
    $data = $category->toArray();

    $this->postJson($this->createUrl($this->url, 0), $data);

    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_updates_the_category()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $call = $this->putJson($this->createUrl($this->url, $project->id, $category->id), [
        'id'    => $category->id,
        'title' => "New Title",
        'color' => '#ffffff'
    ]);

    $this->assertResponseStatus(200);
    $call->seeJson([
        'id'    => $category->id,
        'title' => "New Title",
        'color' => "#ffffff",
    ]);
  }

  /** @test */
  public function it_updates_the_category_400_if_validation_fails()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this->putJson($this->createUrl($this->url, $project->id, $category->id), []);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_category_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->putJson($this->createUrl($this->url, $project->id, $category->id), [
            'id'    => $category->id,
            'title' => "New Title"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_category_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->putJson($this->createUrl($this->url, $project->id, $category->id), [
            'id'    => $category->id,
            'title' => "New Title"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_category_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this->putJson($this->createUrl($this->url, $project->id, $category->id), [
        'id'    => $category->id,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_updates_the_category_404_if_project_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create([
        'project_id' => $project->id
    ]);

    $this->putJson($this->createUrl($this->url, 0, $category->id), [
        'id'    => 0,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_updates_the_category_404_if_category_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->putJson($this->createUrl($this->url, $project->id, 0), [
        'id'    => 0,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(404);
  }


//  /** @test */
//  public function it_deletes_a_category()
//  {
//    $project = factory(App\Models\Project::class)->create();
//    $project->users()->attach($this->connectedUser->id);
//    $category = factory(App\Models\Category::class)->create([
//        'project_id' => $project->id
//    ]);
//
//    $this->deleteJson($this->createUrl($this->url, $project->id, $category->id));
//
//    $this->assertResponseStatus(204);
//  }
//
//  /** @test */
//  public function it_deletes_the_category_400_if_not_authenticated()
//  {
//    $project = factory(App\Models\Project::class)->create();
//    $project->users()->attach($this->connectedUser->id);
//    $category = factory(App\Models\Category::class)->create([
//        'project_id' => $project->id
//    ]);
//
//    $this
//        ->setAuthentication(AuthEnum::NONE)
//        ->deleteJson($this->createUrl($this->url, $project->id, $category->id));
//
//
//    $this->assertResponseStatus(400);
//  }
//
//  /** @test */
//  public function it_deletes_the_category_400_if_wrong_authentication()
//  {
//    $project = factory(App\Models\Project::class)->create();
//    $project->users()->attach($this->connectedUser->id);
//    $category = factory(App\Models\Category::class)->create([
//        'project_id' => $project->id
//    ]);
//
//    $this
//        ->setAuthentication(AuthEnum::WRONG)
//        ->deleteJson($this->createUrl($this->url, $project->id, $category->id));
//
//    $this->assertResponseStatus(400);
//  }
//
//  /** @test */
//  public function it_deletes_a_category_403_if_forbidden()
//  {
//    $user = factory(App\Models\User::class)->create();
//    $project = factory(App\Models\Project::class)->create();
//    $project->users()->attach($user->id);
//    $category = factory(App\Models\Category::class)->create([
//        'project_id' => $project->id
//    ]);
//
//    $this->deleteJson($this->createUrl($this->url, $project->id, $category->id));
//
//    $this->assertResponseStatus(403);
//  }
//
//  /** @test */
//  public function it_deletes_a_category_404_if_project_not_found()
//  {
//    $project = factory(App\Models\Project::class)->create();
//    $project->users()->attach($this->connectedUser->id);
//    $category = factory(App\Models\Category::class)->create([
//        'project_id' => $project->id
//    ]);
//
//    $this->deleteJson($this->createUrl($this->url, 0, $category->id));
//
//    $this->assertResponseStatus(404);
//  }
//
//  /** @test */
//  public function it_deletes_a_category_404_if_category_not_found()
//  {
//    $project = factory(App\Models\Project::class)->create();
//    $project->users()->attach($this->connectedUser->id);
//
//    $this->deleteJson($this->createUrl($this->url, $project->id, 0));
//
//    $this->assertResponseStatus(404);
//  }
}
