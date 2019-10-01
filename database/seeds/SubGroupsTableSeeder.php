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
        Artisan::call('import:subgroups', []);
    }
}
