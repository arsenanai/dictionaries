<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subgroup extends Model
{
    protected $fillable = array('name_kk','name_ru');

    public function group(){
        return $this->belongsTo('App\Group', 'group_id');
    }
    
    public function codes(){
    	return $this->hasMany('App\Code', 'subgroup_id');
    }
    public function user(){
    	return $this->belongsTo('App\User', 'user_id');
    }
}
