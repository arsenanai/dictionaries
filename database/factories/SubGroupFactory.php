<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\SubGroup;
use App\Group;
use Faker\Generator as Faker;

$factory->define(SubGroup::class, function (Faker $faker) {
	$groups = Group::all()->pluck('id')->toArray();
	$number = $faker->unique()->randomNumber(3);
    return [
        'name_kk' => 'Кіші топтың аты '.$number,
        'name_ru' => 'Название подгруппы '.$number,
        'isZKS' => $faker->boolean(),
        'group_id' => $faker->randomElement($groups)
    ];
});
