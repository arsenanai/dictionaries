<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Group;
use App\Subgroup;
use App\Code;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

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
        $prod_codes = DB::connection('snd')
        ->table('ens_map_15')
        ->select(DB::raw("ens,name,name_kaz,concat_ws(' ', attr1, attr2, attr3, attr4, attr5, attr6, attr7, attr8, attr9, attr10) as desc, 
            concat_ws(' ', attr1_kaz, attr2_kaz, attr3_kaz, attr4_kaz, attr5_kaz, attr6_kaz, attr7_kaz, attr8_kaz, attr9_kaz, attr10_kaz) as desc_kaz,type"))
        ->where('state','FINAL')
        ->get();
        $otherSubgroupId = Subgroup::select('id')->where('name_kk','Қалғандары')->value('id');
        $count = 0;
        DB::beginTransaction();
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
            $count++;
            if($count%1000==0){
                DB::commit();
                DB::beginTransaction();
            }
        }
        DB::commit();
        echo $count;
    }
}
