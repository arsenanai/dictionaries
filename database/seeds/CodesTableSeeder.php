<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Group;
use App\Subgroup;
use App\Code;

class CodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @return void
     */
    public function run()
    {
        /*factory(App\Code::class, 1000)
        	->create();*/
        /*
select
ens,name,name_kaz,attr1 
from ens_map_15 
where state = 'FINAL'
        */
        $prod_codes = DB::connection('snd')
        ->table('ens_map_15')
        ->select('ens','name','name_kaz','attr1', 'attr1_kaz','type')
        ->where('state','FINAL')
        ->get();
        echo 'data read...';
        $otherSubgroupId = Subgroup::select('id')->where('name_kk','Қалғандары')->value('id');
        DB::transaction(function () use($prod_codes, $otherSubgroupId){
            foreach($prod_codes as $c){
                $i = new Code();
                $i->code = $c->ens;
                $i->name_kk = $c->name_kaz;
                $i->name_ru = $c->name;
                $i->description_kk = $c->attr1_kaz;
                $i->description_ru = $c->attr1;
                $i->type = $c->type;
                $i->subgroup_id = $otherSubgroupId;
                $i->save();
            }
        });
    }
}
