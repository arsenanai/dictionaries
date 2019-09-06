<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = array('name_kk','name_ru','isZKS');
    
    public function subGroups(){
        return $this->hasMany('App\Subgroup', 'group_id');
    }
}
