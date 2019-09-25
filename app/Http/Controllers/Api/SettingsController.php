<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Setting;
use App;
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
}
