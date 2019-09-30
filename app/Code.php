<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $fillable = array('code', 'description_kk', 'description_ru', 'name_kk', 'name_ru', 'type');

    public function subgroup(){
        return $this->belongsTo('App\Subgroup', 'subgroup_id');
    }

    public function user(){
    	return $this->belongsTo('App\User', 'user_id');
    }
}
