<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
  return [
    'name' => $faker->name,
    'email' => $faker->email,
    'password' => bcrypt(str_random(10)),
    'remember_token' => str_random(10),
  ];
});

$factory->define(App\Models\Project::class, function (Faker\Generator $faker) {
  return [
    'title' => $faker->name
  ];
});

$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {
  return [
    'title' => $faker->name,
    'color' => $faker->hexcolor,
    'by_default' => $faker->boolean()
  ];
});

$factory->define(App\Models\Entry::class, function (Faker\Generator $faker) {
  return [
    'title' => $faker->name,
    'price' => $faker->numberBetween($min = 100, $max = 100000),
    'date' => $faker->dateTime(),
    'content' => $faker->sentence()
  ];
});

