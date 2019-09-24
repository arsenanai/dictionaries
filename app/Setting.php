<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = array('user_id', 'settings');

    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
