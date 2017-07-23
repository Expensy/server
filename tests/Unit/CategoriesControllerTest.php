<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoriesControllerTest extends ApiTester
{
  protected $indexUrl = 'api/v1/projects/%d/categories/%d';
  protected $url = 'api/v1/categories/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp() {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_categories() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(Category::class, 3)->create([
      'project_id' => $project->id
    ]);

    $response = $this->getJson($this->createUrl($this->indexUrl, $project->id));

    $response->assertSuccessful();

    $response->assertJsonStructure([
      'items' => [
        '*' => [
          'id', 'title', 'color'
        ],
      ],
      'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_categories_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(Category::class, 3)->create([
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->indexUrl, $project->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_categories_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(Category::class, 3)->create([
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->indexUrl, $project->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_categories_404_if_category_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    factory(Category::class, 3)->create([
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->indexUrl, 0));

    $response->assertStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_category() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this->getJson($this->createUrl($this->url, $category->id));

    $response->assertSuccessful();
    $response->assertJson([
      'id' => $category->id,
      'title' => $category->title,
      'color' => $category->color
    ]);
  }

  /** @test */
  public function it_fetches_a_single_category_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url, $category->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_category_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url, $category->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_category_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this->getJson($this->createUrl($this->url, $category->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_fetches_a_single_category_404_category_if_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->getJson($this->createUrl($this->url, 0));
    $response->assertStatus(404);
  }


  /** @test */
  public function it_creates_a_new_category() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->make();
    $data = $category->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(201);
  }

  /** @test */
  public function it_creates_a_new_category_v2() {
    $project1 = factory(Project::class)->create();
    $project1->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project1->id
    ]);

    $project2 = factory(Project::class)->create();
    $project2->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->make([
      'title' => $category->title
    ]);

    $data = $category->toArray();
    $response = $this->postJson($this->createUrl($this->indexUrl, $project2->id), $data);

    $response->assertStatus(201);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_validation_fails() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), []);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_title_already_taken() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category1 = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $category2 = factory(Category::class)->make([
      'title' => $category1->title
    ]);

    $data = $category2->toArray();
    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_color_not_hexadecimal() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->make([
      'color' => '123456'
    ]);
    $data = $category->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(400);
  }


  /** @test */
  public function it_creates_a_new_category_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->make();
    $data = $category->toArray();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->make();
    $data = $category->toArray();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_category_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(Category::class)->make();
    $data = $category->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(403);
  }

  /** @test */
  public function it_creates_a_new_category_404_if_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->make();
    $data = $category->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, 0), $data);

    $response->assertStatus(404);
  }


  /** @test */
  public function it_updates_the_category() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this->putJson($this->createUrl($this->url, $category->id), [
      'id' => $category->id,
      'title' => 'New Title',
      'color' => '#ffffff'
    ]);

    $response->assertStatus(200);
    $response->assertJson([
      'id' => $category->id,
      'title' => 'New Title',
      'color' => '#ffffff'
    ]);
  }

  /** @test */
  public function it_updates_the_category_400_if_validation_fails() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this->putJson($this->createUrl($this->url, $category->id), []);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_category_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->url, $category->id), [
        'id' => $category->id,
        'title' => 'New Title'
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_category_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->putJson($this->createUrl($this->url, $category->id), [
        'id' => $category->id,
        'title' => 'New Title'
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_category_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);
    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $response = $this->putJson($this->createUrl($this->url, $category->id), [
      'id' => $category->id,
      'title' => 'New title'
    ]);

    $response->assertStatus(403);
  }

  /** @test */
  public function it_updates_the_category_404_if_category_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->putJson($this->createUrl($this->url, $project->id, 0), [
      'id' => 0,
      'title' => 'New title'
    ]);

    $response->assertStatus(404);
  }


  //  /** @test */
  //  public function it_deletes_a_category()
  //  {
  //    $project = factory(Project::class)->create();
  //    $project->users()->attach($this->connectedUser->id);
  //    $category = factory(Category::class)->create([
  //        'project_id' => $project->id
  //    ]);
  //
  //    $this->deleteJson($this->createUrl($this->url, $project->id, $category->id));
  //
  //    $response->assertStatus(204);
  //  }
  //
  //  /** @test */
  //  public function it_deletes_the_category_400_if_not_authenticated()
  //  {
  //    $project = factory(Project::class)->create();
  //    $project->users()->attach($this->connectedUser->id);
  //    $category = factory(Category::class)->create([
  //        'project_id' => $project->id
  //    ]);
  //
  //    $this
  //        ->setAuthentication(AuthEnum::NONE)
  //        ->deleteJson($this->createUrl($this->url, $project->id, $category->id));
  //
  //
  //    $response->assertStatus(400);
  //  }
  //
  //  /** @test */
  //  public function it_deletes_the_category_400_if_wrong_authentication()
  //  {
  //    $project = factory(Project::class)->create();
  //    $project->users()->attach($this->connectedUser->id);
  //    $category = factory(Category::class)->create([
  //        'project_id' => $project->id
  //    ]);
  //
  //    $this
  //        ->setAuthentication(AuthEnum::WRONG)
  //        ->deleteJson($this->createUrl($this->url, $project->id, $category->id));
  //
  //    $response->assertStatus(400);
  //  }
  //
  //  /** @test */
  //  public function it_deletes_a_category_403_if_forbidden()
  //  {
  //    $user = factory(User::class)->create();
  //    $project = factory(Project::class)->create();
  //    $project->users()->attach($user->id);
  //    $category = factory(Category::class)->create([
  //        'project_id' => $project->id
  //    ]);
  //
  //    $this->deleteJson($this->createUrl($this->url, $project->id, $category->id));
  //
  //    $response->assertStatus(403);
  //  }
  //
  //  /** @test */
  //  public function it_deletes_a_category_404_if_project_not_found()
  //  {
  //    $project = factory(Project::class)->create();
  //    $project->users()->attach($this->connectedUser->id);
  //    $category = factory(Category::class)->create([
  //        'project_id' => $project->id
  //    ]);
  //
  //    $this->deleteJson($this->createUrl($this->url, 0, $category->id));
  //
  //    $response->assertStatus(404);
  //  }
  //
  //  /** @test */
  //  public function it_deletes_a_category_404_if_category_not_found()
  //  {
  //    $project = factory(Project::class)->create();
  //    $project->users()->attach($this->connectedUser->id);
  //
  //    $this->deleteJson($this->createUrl($this->url, $project->id, 0));
  //
  //    $response->assertStatus(404);
  //  }
}
