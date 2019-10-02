<?php

use Illuminate\Database\Seeder;
use App\Group;
use App\Subgroup;
use Illuminate\Support\Facades\Artisan;

class SubGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$other = new Subgroup();
        $other->name_kk = 'Қалғандары';
        $other->name_ru = 'Прочие';
        $other->group_id = Group::select('id')->where('name_kk','Қалғандары')->value('id');
        $other->save();
        Artisan::call('import:subgroups', []);
    }
}
