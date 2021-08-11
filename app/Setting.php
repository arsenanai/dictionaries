<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model
{
    protected $fillable = array('user_id', 'settings');
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
    public function per_page(){
    	$s = json_decode($this->settings);
    	if(is_array($s)&&sizeof($s)>0)
    		for($i=0;$i<sizeof($s);$i++){
    			if($s[$i]->key = 'dictionary_per_page'){
    				return $s[$i]->value;
    			}
    		}
    	return 15;
    }
}