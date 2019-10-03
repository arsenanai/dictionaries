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

class UpdateCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating codes from production database';

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
        $start = microtime(true);
        $otherSubgroupId = Subgroup::select('id')->where('name_kk','Қалғандары')->value('id');
        $count=0;
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
        ->get();
        $time_elapsed_secs = microtime(true) - $start;
        echo 'data read: '.$time_elapsed_secs.PHP_EOL;
        //create temp table
        Schema::dropIfExists('codes2');
        Schema::create('codes2', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subgroup_id')->default(1)->nullable(false);
            $table->unsignedBigInteger('user_id')->default(1)->nullable(false);
            $table->string('code', 17)->nullable(false)->unique();
            $table->string('name_kk',300)->nullable(false);
            $table->string('name_ru',300)->nullable(false);
            $table->string('description_kk',1024);
            $table->string('description_ru',1024);
            $table->string('type');
            $table->timestamps();
            $table->foreign('subgroup_id')->references('id')->on('subgroups')
            ->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')
                ->nullable(false)->change();
        });
        $count = 0;
        //DB::beginTransaction();
        $pc = array();
        foreach($prod_codes as $c)
            $pc[]=get_object_vars($c);
        DB::table("codes2")->insert($pc);
        //DB::commit();
        $start = microtime(true);
        //DB::table('codes2')->insertOrIgnore($prod_codes);
        $time_elapsed_secs = microtime(true) - $start;
        echo $time_elapsed_secs.PHP_EOL;
        /*$start = microtime(true);
            //DB::transaction(function () use($prod_codes, $otherSubgroupId,$count,$card){
        foreach($prod_codes as $c){
            $i=null;
            if(DB::table('codes')->where('code',$c->code)->count()==0){
                $i = new Code();
                $i->subgroup_id = $otherSubgroupId;
            }else if(
                DB::table('codes')->where('code',$c->code)
                    ->where('name_kk','!=',trim($c->name_kk))
                    ->orWhere('name_ru','!=',trim($c->name_ru))
                    ->orWhere('description_kk','!=',trim($c->description_kk))
                    ->orWhere('description_ru','!=',trim($c->description_ru))
                    ->orWhere('type','!=',trim($c->type))
                    ->count()==1
            ){
                $i = Code::where('code',$c->code)->first();
            }

            if($i!=null){
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
            //});
        $time_elapsed_secs = microtime(true) - $start;
        echo $time_elapsed_secs.PHP_EOL;
        echo $count;*/
    }
}
