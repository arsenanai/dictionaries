<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Group;
use App\Subgroup;
use App\Code;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ImportCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import codes from production database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //production data
        //$start = microtime(true);
        $otherSubgroupId = Subgroup::select('id')->where('name_kk','Қалғандары')->value('id');
        DB::disableQueryLog();
        $prod_codes = DB::connection('snd')
        ->table('ens_map_15')
        ->select(DB::raw(
            "ens as code,
            name as name_ru,
            name_kaz as name_kk,
            concat_ws(' ', attr1, attr2, attr3, attr4, attr5, attr6, attr7, attr8, attr9, attr10) as description_ru, 
            concat_ws(' ', attr1_kaz, attr2_kaz, attr3_kaz, attr4_kaz, attr5_kaz, attr6_kaz, attr7_kaz, attr8_kaz, attr9_kaz, attr10_kaz) as description_kk,
            type
            "
        ))
        ->where('state','FINAL')
        ->orderBy('id','desc')
        ->get()->keyBy('code')->toArray();
        $local_codes = DB::table('codes')
        ->select('code','name_kk','name_ru','description_kk','description_ru','type')
        ->get()->keyBy('code')->toArray();
        foreach($prod_codes as $c){
            $i=null;
            if(!array_key_exists($c->code, $local_codes)){
                $i = new Code();
                $i->subgroup_id = $otherSubgroupId;
            }else{ 
                $l = $local_codes[$c->code];
                if(strcmp($l->name_kk, trim($c->name_kk))!==0
                    || strcmp($l->name_ru , trim($c->name_ru))!==0
                    || strcmp($l->description_kk , trim($c->description_kk))!==0
                    || strcmp($l->description_ru , trim($c->description_ru))!==0
                    || strcmp($l->type , $c->type)!==0
                ){
                    $i = Code::where('code',$c->code)->first();
                }
            }
            if($i!==null){
                $i->code = $c->code;
                $i->name_kk = trim($c->name_kk);
                $i->name_ru = trim($c->name_ru);
                $i->description_kk = trim($c->description_kk);
                $i->description_ru = trim($c->description_ru);
                $i->type = $c->type;
                $i->save();
                $count++;
            }
        }
        echo DB::update('update codes set subgroup_id = ?', [$otherSubgroupId]);
    }
}
