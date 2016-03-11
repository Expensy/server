<?php

use Helpers\AuthEnum;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EntriesControllerTest extends ApiTester
{
  protected $url = 'api/v1/projects/%d/entries/%d';

  use DatabaseMigrations, DatabaseTransactions;

  public function setUp()
  {
    parent::setUp();
  }

  /** @test */
  public function it_fetches_entries()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entries = factory(App\Models\Entry::class, 3)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->saveMany($entries);

    $call = $this->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseOk();
    $call->seeJsonStructure([
        'items' => [
            '*' => [
                'id', 'title', 'price', 'content'
            ]
        ],
        'paginate'
    ]);
  }

  /** @test */
  public function it_fetches_entries_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entries = factory(App\Models\Entry::class, 3)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->saveMany($entries);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_entries_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entries = factory(App\Models\Entry::class, 3)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->saveMany($entries);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url, $project->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_entries_404_if_project_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entries = factory(App\Models\Entry::class, 3)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->saveMany($entries);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url, 0));

    $this->assertResponseStatus(400);
  }


  /** @test */
  public function it_fetches_a_single_entry()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $call = $this->getJson($this->createUrl($this->url, $project->id, $entry->id));
    $this->assertResponseOk();
    $call->seeJson([
        'id'      => $entry->id,
        'title'   => $entry->title,
        'price'   => $entry->price,
        'date'    => $entry->date->toIso8601String(),
        'content' => $entry->content
    ]);
  }

  /** @test */
  public function it_fetches_a_single_entry_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->getJson($this->createUrl($this->url, $project->id, $entry->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_entry_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->getJson($this->createUrl($this->url, $project->id, $entry->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_fetches_a_single_entry_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this->getJson($this->createUrl($this->url, $project->id, $entry->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_fetches_a_single_entry_404_project_if_not_found()
  {
    $this->getJson($this->createUrl($this->url, 0, 0));
    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_fetches_a_single_entry_404_entry_if_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->getJson($this->createUrl($this->url, $project->id, 0));
    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_creates_a_new_entry()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->make([
        'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $this->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(201);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_validation_fails()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $entry = factory(App\Models\Entry::class)->make([
        'category_id' => 0
    ]);
    $data = $entry->toArray();

    $this->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_category_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->postJson($this->createUrl($this->url, $project->id), []);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->make([
        'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->make([
        'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_creates_a_new_entry_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->make([
        'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $this->postJson($this->createUrl($this->url, $project->id), $data);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_creates_a_new_entry_404_if_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->make([
        'category_id' => $category->id
    ]);
    $data = $entry->toArray();

    $this->postJson($this->createUrl($this->url, 0), $data);

    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_updates_the_entry()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $call = $this->putJson($this->createUrl($this->url, $project->id, $entry->id), [
        'id'          => $entry->id,
        'title'       => "New Title",
        'price'       => $entry->price,
        'date'        => $entry->date->toDateTimeString(),
        'category_id' => $entry->category->id
    ]);

    $this->assertResponseStatus(200);
    $call->seeJson([
        'title' => "New Title"
    ]);
  }

  /** @test */
  public function it_updates_the_entry_400_if_validation_fails()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this->putJson($this->createUrl($this->url, $project->id, $entry->id), []);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_400_if_category_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);
    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => 0
    ]);
    $project->entries()->save($entry);

    $this->putJson($this->createUrl($this->url, $project->id, $entry->id), [
        'id'          => $entry->id,
        'title'       => "New Title",
        'price'       => $entry->price,
        'date'        => $entry->date->toDateTimeString(),
        'category_id' => 0
    ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->putJson($this->createUrl($this->url, $project->id, $entry->id), [
            'id'    => $entry->id,
            'title' => "New Title"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->putJson($this->createUrl($this->url, $project->id, $entry->id), [
            'id'    => $entry->id,
            'title' => "New Title"
        ]);

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_updates_the_entry_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this->putJson($this->createUrl($this->url, $project->id, $entry->id), [
        'id'    => $entry->id,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_updates_the_entry_404_if_project_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this->putJson($this->createUrl($this->url, 0, $entry->id), [
        'id'    => 0,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_updates_the_entry_404_if_entry_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->putJson($this->createUrl($this->url, $project->id, 0), [
        'id'    => 0,
        'title' => "New title"
    ]);

    $this->assertResponseStatus(404);
  }


  /** @test */
  public function it_deletes_a_entry()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this->deleteJson($this->createUrl($this->url, $project->id, $entry->id));

    $this->assertResponseStatus(204);
  }

  /** @test */
  public function it_deletes_the_entry_400_if_not_authenticated()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this
        ->setAuthentication(AuthEnum::NONE)
        ->deleteJson($this->createUrl($this->url, $project->id, $entry->id));


    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_the_entry_400_if_wrong_authentication()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this
        ->setAuthentication(AuthEnum::WRONG)
        ->deleteJson($this->createUrl($this->url, $project->id, $entry->id));

    $this->assertResponseStatus(400);
  }

  /** @test */
  public function it_deletes_a_entry_403_if_forbidden()
  {
    $user = factory(App\Models\User::class)->create();
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($user->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this->deleteJson($this->createUrl($this->url, $project->id, $entry->id));

    $this->assertResponseStatus(403);
  }

  /** @test */
  public function it_deletes_a_entry_404_if_project_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $category = factory(App\Models\Category::class)->create();
    $project->categories()->save($category);

    $entry = factory(App\Models\Entry::class)->create([
        'category_id' => $category->id
    ]);
    $project->entries()->save($entry);

    $this->deleteJson($this->createUrl($this->url, 0, $entry->id));

    $this->assertResponseStatus(404);
  }

  /** @test */
  public function it_deletes_a_entry_404_if_entry_not_found()
  {
    $project = factory(App\Models\Project::class)->create();
    $project->users()->attach($this->connectedUser->id);

    $this->deleteJson($this->createUrl($this->url, $project->id, 0));

    $this->assertResponseStatus(404);
  }
}
