<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Group;
use Faker\Generator as Faker;

$factory->define(Group::class, function (Faker $faker) {
	$number = $faker->unique()->randomNumber(2);
    return [
        'name_kk' => 'Топтың аты '.$number,
        'name_ru' => 'Название группы '.$number,
        'isZKS' => $faker->boolean()
    ];
});
