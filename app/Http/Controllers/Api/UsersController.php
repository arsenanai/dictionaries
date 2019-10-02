<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
	
    public function index(Request $request){
    	if($request->user()->id==1)
    		return UserResource::collection(User::withCount(['groups','subgroups','codes'])->paginate(10));
    	else
    		return response(null,401);
    }

    public function show(User $user,Request $request){
    	if($request->user()->id==1 || $request->user()->id == $user->id)
			$uid = $user->id;
		else
			$uid = $request->user()->id;
    	return new UserResource(User::find($uid));
    }

    public function profile(Request $request){
    	return new UserResource(User::find($request->user()->id));
    }

    public function update(User $user, Request $request)
	{
		if($request->user()->id==1 || $request->user()->id == $user->id)
			$uid = $user->id;
		else
			$uid = $request->user()->id;
		$data = $request->validate([
	        'name' => "required|unique:users,name,$uid",
	        'email' => "required|email|unique:users,email,$uid",
	    ]);
        if($request->has('password'))
	    	$data['password'] = Hash::make($request->input('password'));
	    $user->update($data);
	    return new UserResource($user);
	}

	public function destroy(User $user,Request $request)
	{
		if($request->user()->id==1){
			DB::table('groups')->where('user_id',$user->id)->update(['user_id'=>1]);
			DB::table('subgroups')->where('user_id',$user->id)->update(['user_id'=>1]);
			DB::table('codes')->where('user_id',$user->id)->update(['user_id'=>1]);
			$user->delete();
		}
		return response(null, 204);
	}

	public function store(Request $request)
	{
		if($request->user()->id==1){
		    $data = $request->validate([
		        'name' => 'required|unique:users',
		        'email' => 'required|email|unique:users',
		        'password' => 'required|min:8',
		    ]);

		    return new UserResource(User::create([
		        'name' => $data['name'],
		        'email' => $data['email'],
		        'password' => bcrypt($data['password']),
		    ]));
		}else
			return response(null, 401);
	}
}
