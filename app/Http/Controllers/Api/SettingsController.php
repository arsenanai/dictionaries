<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Setting;
use App;
use App\Group;
use App\Subgroup;
use App\Code;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\SettingResource;

class SettingsController extends Controller
{
    public function fetch(Request $request){
    	$uid = $request->user()->id;
    	$query = Setting::where('user_id',$uid)->first();
    	if($query==null){
    		$query = new Setting();
    		$query->user_id = $uid;
    		$query->settings = "[]";
    		$query->save();

    	}
    	return new SettingResource($query);
    }
    public function save(Setting $setting, Request $request){
    	$data = $request->validate([
	        'user_id' => 'required|exists:users,id',
	        'settings' => 'required',
	    ]);
        //echo json_encode($data); exit;
        $data['settings'] = json_encode($data['settings']);
	    $setting->update($data);
	    return new SettingResource($setting);
    }

    public function reset(Request $request){
        if($request->user()->id==1){
            $type = $request->input('type');
            if($type=='group'){
                DB::table('codes')->where('subgroup_id','!=',1)->update(['subgroup_id'=>1]);
                DB::table('subgroups')->where('id','!=',1)->delete();
                DB::table('groups')->where('id','!=',1)->delete();
                Artisan::call('import:groups', []);
                Artisan::call('import:subgroups', []);
            }else if($type=='code'){
                Code::truncate();
                Artisan::queue('import:codes', []);
                //echo 999;
            }
            return response(null, 200);
        }else
            return response(null, 403);
    }

    public function sync(Request $request){
        if($request->user()->id==1){
            Artisan::call('update:codes', []);
            //echo 999;
            return response(null, 200);
        }else
            return response(null, 403);
    }
}
