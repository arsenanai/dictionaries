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
        ->select(DB::raw("ens,name,name_kaz,concat_ws(' ', attr1, attr2, attr3, attr4, attr5, attr6, attr7, attr8, attr9, attr10) as desc, 
            concat_ws(' ', attr1_kaz, attr2_kaz, attr3_kaz, attr4_kaz, attr5_kaz, attr6_kaz, attr7_kaz, attr8_kaz, attr9_kaz, attr10_kaz) as desc_kaz,type"))
        ->where('state','FINAL')
        ->get();
        echo 'data read...';
        $otherSubgroupId = Subgroup::select('id')->where('name_kk','Қалғандары')->value('id');
        DB::transaction(function () use($prod_codes, $otherSubgroupId){
            foreach($prod_codes as $c){
                $i = new Code();
                $i->code = $c->ens;
                $i->name_kk = trim($c->name_kaz);
                $i->name_ru = trim($c->name);
                $i->description_kk = trim($c->desc_kaz);
                $i->description_ru = trim($c->desc);
                $i->type = $c->type;
                $i->subgroup_id = $otherSubgroupId;
                $i->save();
            }
        });
    }
}
