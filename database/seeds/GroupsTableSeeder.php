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
        Artisan::call('import:groups', []);
    }
}
