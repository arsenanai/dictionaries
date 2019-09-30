<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = array('name_kk','name_ru','isZKS');
    
    public function subgroups(){
        return $this->hasMany('App\Subgroup', 'group_id');
    }
    public function user(){
    	return $this->belongsTo('App\User', 'user_id');
    }
}
