<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Entry;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EntriesControllerTest extends ApiTester
{
  protected $indexUrl = 'api/v1/projects/%d/entries/%d';
  protected $url = 'api/v1/entries/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp() {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_entries() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    factory(Entry::class, 3)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->getJson($this->createUrl($this->indexUrl, $project->id), [
      'start_date' => Carbon::now()->subDay(1)->toDateString(),
      'end_date' => Carbon::now()->toDateString()
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
      'items' => [
        '*' => [
          'id', 'title', 'price', 'content'
        ]
      ],
      'stats',
      'paginate'
    ]);
  }


  /** @test */
  public function it_fetches_entries_400_if_wrong_parameters() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    factory(Entry::class, 3)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->getJson($this->createUrl($this->indexUrl, $project->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_entries_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    factory(Entry::class, 3)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->indexUrl, $project->id), [
      'start_date' => Carbon::now()->subDay(1)->toDateString(),
      'end_date' => Carbon::now()->toDateString()
    ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_entries_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    factory(Entry::class, 3)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->indexUrl, $project->id), [
      'start_date' => Carbon::now()->subDay(1)->toDateString(),
      'end_date' => Carbon::now()->toDateString()
    ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_entries_404_if_project_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    factory(Entry::class, 3)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->indexUrl, 0),[
      'start_date' => Carbon::now()->subDay(1)->toDateString(),
      'end_date' => Carbon::now()->toDateString()
    ]);

    $response->assertStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_entry() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->getJson($this->createUrl($this->url, $entry->id));
    $response->assertSuccessful();
    $response->assertJson([
      'id' => $entry->id,
      'title' => $entry->title,
      'price' => $entry->price,
      'date' => $entry->date->toIso8601String(),
      'content' => $entry->content
    ]);
  }

  /** @test */
  public function it_fetches_a_single_entry_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->getJson($this->createUrl($this->url, $entry->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_entry_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->getJson($this->createUrl($this->url, $entry->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_entry_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->getJson($this->createUrl($this->url, $entry->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_fetches_a_single_entry_404_entry_if_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->getJson($this->createUrl($this->url, $project->id, 0));
    $response->assertStatus(404);
  }


  /** @test */
  public function it_creates_a_new_entry() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->make([
      'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(201);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_validation_fails() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $entry = factory(Entry::class)->make([
      'category_id' => 0
    ]);
    $data = $entry->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_category_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), []);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->make([
      'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->make([
      'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->make([
      'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, $project->id), $data);

    $response->assertStatus(403);
  }

  /** @test */
  public function it_creates_a_new_entry_404_if_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->make([
      'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $response = $this->postJson($this->createUrl($this->indexUrl, 0), $data);

    $response->assertStatus(404);
  }


  /** @test */
  public function it_updates_the_entry() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->putJson($this->createUrl($this->url, $entry->id), [
      'id' => $entry->id,
      'title' => 'New Title',
      'price' => $entry->price,
      'date' => $entry->date->toDateTimeString(),
      'category_id' => $entry->category->id
    ]);

    $response->assertStatus(200);
    $response->assertJson([
      'title' => 'New Title'
    ]);
  }

  /** @test */
  public function it_updates_the_entry_400_if_validation_fails() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->putJson($this->createUrl($this->url, $entry->id), []);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_400_if_category_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->putJson($this->createUrl($this->url, $entry->id), [
      'id' => $entry->id,
      'title' => 'New Title',
      'price' => $entry->price,
      'date' => $entry->date->toDateTimeString(),
      'category_id' => 0
    ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->putJson($this->createUrl($this->url, $entry->id), [
        'id' => $entry->id,
        'title' => 'New Title',
        'category_id' => $category->id
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->putJson($this->createUrl($this->url, $entry->id), [
        'id' => $entry->id,
        'title' => 'New Title',
        'category_id' => $category->id
      ]);

    $response->assertStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->putJson($this->createUrl($this->url, $entry->id), [
      'id' => $entry->id,
      'title' => 'New title'
    ]);

    $response->assertStatus(403);
  }

  /** @test */
  public function it_updates_the_entry_404_if_entry_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->putJson($this->createUrl($this->url, 0), [
      'id' => 0,
      'title' => 'New title'
    ]);

    $response->assertStatus(404);
  }


  /** @test */
  public function it_deletes_a_entry() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->deleteJson($this->createUrl($this->url, $entry->id));

    $response->assertStatus(204);
  }

  /** @test */
  public function it_deletes_the_entry_400_if_not_authenticated() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::NONE)
      ->deleteJson($this->createUrl($this->url, $entry->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_the_entry_400_if_wrong_authentication() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this
      ->setAuthentication(AuthEnum::WRONG)
      ->deleteJson($this->createUrl($this->url, $entry->id));

    $response->assertStatus(400);
  }

  /** @test */
  public function it_deletes_a_entry_403_if_forbidden() {
    $user = factory(User::class)->create();
    $project = factory(Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(Category::class)->create([
      'project_id' => $project->id
    ]);

    $entry = factory(Entry::class)->create([
      'category_id' => $category->id,
      'project_id' => $project->id
    ]);

    $response = $this->deleteJson($this->createUrl($this->url, $entry->id));

    $response->assertStatus(403);
  }

  /** @test */
  public function it_deletes_a_entry_404_if_entry_not_found() {
    $project = factory(Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $response = $this->deleteJson($this->createUrl($this->url, $project->id, 0));

    $response->assertStatus(404);
  }
}
