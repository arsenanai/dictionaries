<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Group;
use App\Subgroup;
use App\Code;
use Illuminate\Support\Facades\Artisan;

class CodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @return void
     */
    public function run()
    {
        Artisan::call('import:codes', []);
    }
}
