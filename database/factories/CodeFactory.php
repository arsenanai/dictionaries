<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Code;
use App\SubGroup;
use App\Group;
use Faker\Generator as Faker;

$factory->define(Code::class, function (Faker $faker) {
	$groups = Group::all()->pluck('id')->toArray();
	$subGroups = SubGroup::all()->pluck('id')->toArray();
    $number = $faker->unique()->randomNumber(6);
    return [
    	'code' => '123456.789.'.$number,
        'name_kk' => 'Кодтың аты '.$number,
        'name_ru' => 'Наименование кода '.$number,
        'description_kk' => 'Кодтың анықтамасы '.$number,
   		'description_ru' => 'Описание кода '.$number,
        'group_id' => $faker->randomElement($groups),
        'subgroup_id' => $faker->randomElement($subGroups),
        'isZKS' => $faker->boolean()
    ];
});
