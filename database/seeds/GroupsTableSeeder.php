<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Group;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$other = new Group();
        $other->name_kk = 'Қалғандары';
        $other->name_ru = 'Прочие';
        $other->isZKS = false;
        $other->save();
        Artisan::call('import:groups', []);
    }
}
