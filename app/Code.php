<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $fillable = array('code', 'description_kk', 'description_ru', 'name_kk', 'name_ru', 'isZKS');
    
	public function group(){
        return $this->belongsTo('App\Group', 'group_id');
    }

    public function subgroup(){
        return $this->belongsTo('App\Subgroup', 'subgroup_id');
    }

}
