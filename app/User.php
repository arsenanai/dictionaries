<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = array('name', 'password', 'email');
    protected $hidden = ['password'];
    protected $rules = [
        'email' => 'required|email|unique:users',
        'name' => 'required|unique:users',
    ];
    public function groups(){
        return $this->hasMany('App\Code', 'user_id');
    }
    public function subgroups(){
        return $this->hasMany('App\Code', 'user_id');
    }
    public function codes(){
        return $this->hasMany('App\Code', 'user_id');
    }

}