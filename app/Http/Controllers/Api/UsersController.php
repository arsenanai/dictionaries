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
	
    public function index()
    {
    	return UserResource::collection(User::withCount(['groups','subgroups','codes'])->paginate(10));
    }

    public function show(User $user)
    {
    	return new UserResource($user);
    }

    public function update(User $user, Request $request)
	{
		$data = $request->validate([
	        'name' => "required|unique:users,name,$user->id",
	        'email' => "required|email|unique:users,email,$user->id",
	    ]);
        if($request->has('password'))
	    	$data['password'] = Hash::make($request->input('password'));
	    $user->update($data);
	    return new UserResource($user);
	}

	public function destroy(User $user)
	{
		$user->delete();
		return response(null, 204);
	}

	public function store(Request $request)
	{
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
	}
}
