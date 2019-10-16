<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Group;
use App\Subgroup;
use App\Code;
use App\Setting;
use App;
use App\Http\Resources\GroupResource;
use App\Http\Resources\SubgroupResource;
use App\Http\Resources\CodeResource;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Validator;

class TRUController extends Controller
{
	public function indexGroup(Request $request)
	{
		$sortBy = $request->input('sort');
    	$order = $request->input('order');
    	$id = $request->input('id');
    	$isZKS = $request->input('isZKS');
    	$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = Group::with('user')->withCount(['subgroups']);
    	if($id!==null && $id != -1)
    		$query = $query->where('id', $id);
    	if($isZKS != null && $isZKS!='')
    		$query = $query->where('isZKS',($isZKS==='false') ? false : true);
    	if(in_array($sortBy,array('id','name_kk','name_ru'))){
    		$query = $query->orderBy($sortBy,($order==='desc')?'desc':'asc');
    	}else
    		$query = $query->orderBy('id','asc');
    	//$p = Setting::where('user_id',$request->user()->id)
    	//	->first()->per_page();
    	$query = $query->paginate(20);
    	return GroupResource::collection($query);
    }
    public function indexSubgroup(Request $request)
    {
    	$sortBy = $request->input('sort');
    	$order = $request->input('order');
    	$group_id = $request->input('group_id');
    	$name = $request->input('name');
    	$isZKS = $request->input('isZKS');
    	$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = Subgroup::with('user')->join('groups','groups.id','=','subgroups.group_id')
    	->withCount(['codes']);
    	if($group_id!=null)
    		$query->where('groups.id', $group_id);
    	if($name!==null && $name != '')
    		$query = $query->where('subgroups.name_'.$lang,'ilike', '%'.$name.'%');
    	if($isZKS != null && $isZKS!='')
    		$query->where('groups.isZKS',($isZKS==='false') ? false : true);
    	if(in_array($sortBy,array('id','name_kk','name_ru'))){
    		$query = $query->orderBy('subgroups.'.$sortBy,($order==='desc')?'desc':'asc');
    	}else
    		$query = $query->orderBy('subgroups.id','asc');
    	//$p = Setting::where('user_id',$request->user()->id)
    	//	->first()->per_page();
    	$query = $query->paginate(20);
    	return SubgroupResource::collection($query);
    }
    public function indexCode(Request $request)
    {
    	$uid = $request->user()->id;
    	$sortBy = $request->input('sort');
    	$order = $request->input('order');
    	$group_id = $request->input('group_id');
    	$subgroup_id = $request->input('subgroup_id');
    	$code = $request->input('code');
    	$name = $request->input('name');
    	$description = $request->input('description');
    	$isZKS = $request->input('isZKS');
    	$type = $request->input('type');
    	$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = Code::with('user')->with('subgroup.group');
    	if($group_id>-1)
    		$query = $query->whereHas('subgroup', function($q) use($lang, $group_id){
    			$q = $q->whereHas('group', function($q) use($lang, $group_id){
    				$q->where('id', $group_id);
    			});
			});
    	if($subgroup_id>-1)
    		$query = $query->whereHas('subgroup', function($q) use($lang, $subgroup_id){
    			$q->where('id', $subgroup_id);
			});
    	if($code!==null && $code != '')
    		$query = $query->where('code','ilike', $code.'%');
    	if($name!==null && $name != '')
    		$query = $query->where('name_'.$lang,'ilike', '%'.$name.'%');
    	if($description!==null && $description != '')
    		$query = $query->where('description_'.$lang,'ilike', '%'.$description.'%');
    	if($isZKS != null && $isZKS!='')
    		$query = $query->whereHas('subgroup', function($query) use($isZKS){
    			$query = $query->whereHas('group', function($q) use($isZKS){
    				$q->where('isZKS',($isZKS==='false') ? false : true);
    			});
    		});
    	if($request->has('type'))
    		$query = $query->where('type',$type);
    	if(in_array($sortBy,array('id','code','name_kk','name_ru','description_kk','description_ru'))){
    		$query = $query->orderBy($sortBy,($order==='desc')?'desc':'asc');
    	}else
    		$query = $query->orderBy('id','asc');
    	$p = Setting::where('user_id',$request->user()->id)
    		->first();
    	$p = ($p!==null) ? $p->per_page() : 15;
    	$query = $query->paginate($p);
    	return CodeResource::collection($query);
    }

    public function showGroup (Group $group) 
    {
    	//return var_dump($group);
	    return new GroupResource($group);
	}
	public function showSubgroup(Subgroup $subgroup)
	{
		$subgroup = Subgroup::with('group')->find($subgroup->id);
		return new SubgroupResource($subgroup);
	}
	public function showCode(Code $code)
	{
		$code = Code::with('subgroup.group')->find($code->id);
		return new CodeResource($code);		
	}

	public function createGroup(Group $group, Request $request){
		Validator::extend('uniqueIsZKSAndNameRu', function ($attribute, $value, $parameters, $validator) {
		    $count = DB::table('groups')
		    	->where('isZKS', ($parameters[0]==false) ? false : true)
                ->where('name_ru', $value)
                ->count();
		    return $count === 0;
		});
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => "required|string|max:256|uniqueIsZKSAndNameRu:{$request->input('isZKS')}",
	        'isZKS' => 'boolean',
	    ]);
	    $group->create($data);
	    return new GroupResource($group);
	}

	public function createSubgroup(Subgroup $group, Request $request){
		Validator::extend('uniqueGroupIdAndNameRu', function ($attribute, $value, $parameters, $validator) {
		    $count = DB::table('subgroups')
		    	->where('group_id', $parameters[0])
                ->where('name_ru', $value)
                ->count();
		    return $count === 0;
		});
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => "required|string|max:256|uniqueGroupIdAndNameRu:{$request->input('group_id')}",
	        'group_id' => 'required|exists:groups,id',
	        //'isZKS' => 'boolean',
	    ]);
	    $subgroup = new Subgroup();
	    $subgroup->name_kk = $request->input('name_kk');
	    $subgroup->name_ru = $request->input('name_ru');
	    $subgroup->group_id = $request->input('group_id');
	    //$subgroup->isZKS = ($request->input('isZKS')) ? true : false;
	    $subgroup->save();
	    return new SubgroupResource($subgroup);
	}

	public function createCode(Code $code, Request $request){
		$data = $request->validate([
	        'code' => 'required|string|between:17,17|unique:codes',
	        'name_kk' => 'required|string|max:300',
	        'name_ru' => 'required|string|max:300',
	        'description_kk' => 'string|max:1024',
	        'description_ru' => 'string|max:1024',
	        'type' => 'in:GOODS,WORK,SERVICE',
	        //'isZKS' => 'boolean',
	        'subgroup_id' => 'required|exists:subgroups,id',
	    ]);
	    $code = new Code();
	    $code->code = $request->input('code');
	    $code->name_kk = $request->input('name_kk');
	    $code->name_ru = $request->input('name_ru');
	    $code->description_kk = $request->input('description_kk');
	    $code->description_ru = $request->input('description_ru');
	    $type->type = $request->input('type');
	    $code->subgroup_id = $request->input('subgroup_id');
	    $code->save();
	    return new CodeResource($code);
	}

	public function updateGroup(Group $group, Request $request){
		Validator::extend('uniqueIsZKSAndNameRu', function ($attribute, $value, $parameters, $validator) {
		    $count = DB::table('groups')
		    	->where('isZKS', $parameters[0])
                ->where('name_ru', $value)
                ->count();
		    return $count === 0;
		});
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => "required|string|max:256|uniqueIsZKSAndNameRu:{$request->input('isZKS')}",
	        'isZKS' => 'boolean',
	    ]);
	    
	    if($group->id!==1){//Others Group
	    	$data['user_id'] = $request->user()->id;
	    	$group->update($data);
	    }
	    return new GroupResource($group);
	}

	public function updateSubgroup(Subgroup $subgroup, Request $request){
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => 'required|string|max:256',
	        'group_id' => 'required|exists:groups,id',
	    ]);
	    if($subgroup->id!==1){//Others Subgroup
	    	$subgroup->group_id = $request->input('group_id');
	    	$data['user_id'] = $request->user()->id;
	    	$subgroup->update($data);
	    }
	    return new SubgroupResource($subgroup);
	}

	public function updateCode(Code $code, Request $request){
		$data = $request->validate([
	        'code' => 'required|string|between:17,17',
	        'name_kk' => 'required|string|max:300',
	        'name_ru' => 'required|string|max:300',
	        'description_kk' => 'string|max:1024',
	        'description_ru' => 'string|max:1024',
	        'type' => 'in:GOODS,WORK,SERVICE',
	        //'isZKS' => 'boolean',
	        //'group_id' => 'required|exists:groups,id',
	        'subgroup_id' => 'exists:subgroups,id'
	    ]);
	    //$code->isZKS = ($request->isZKS) ? true : false;
	    $code->subgroup_id = $request->input('subgroup_id');
	    $data['user_id'] = $request->user()->id;
	    $code->update($data);
	    return new CodeResource($code);
	}

	public function destroyGroup(Group $group)
	{
		$affectedRows = Subgroup::where('group_id', $group->id)
			->update(array('group_id' => 
				Group::select('id')
				->where('name_kk','Қалғандары')
				->value('id')
			)
		);
		$group->delete();
		$response = [
			'message' => 'success',
			'migrated_childs' => $affectedRows
 		];
	    return response($response, 200);
	}

	public function destroySubgroup(Subgroup $subgroup)
	{
		$affectedRows = 0;
		if($subgroup->group_id!=null){
			$affectedRows = Code::where('subgroup_id', $subgroup->id)
				->update(array('subgroup_id' => 
					Subgroup::select('id')
					->where('name_kk','Қалғандары')
					->value('id')
				)
			);
		}
		$subgroup->delete();
		$response = [
			'message' => 'success',
			'migrated_childs' => $affectedRows
 		];
	    return response($response, 200);
	}

	public function destroyCode(Code $code)
	{
		$code->delete();
		$response = ['message' => 'success'];
	    return response($response, 200);
	}

	public function searchGroupsByName(Request $request){
		$lang = $request->input('lang');
		//$input = $request->input('input');
		$except = $request->input('except');
		$lang = ($lang==='kk')?'kk':'ru';
		App::setLocale($lang);
		$query = DB::table('groups')->select('groups.id','groups.name_'.$lang,'isZKS');
		//$query = $query->where('groups.name_'.$lang, 'ilike', '%' . $input . '%');
		if($except!=null)
			$query = $query->where('groups.id',$except);
		if($request->has('onlyWithSubgroups') || $request->has('onlyWithCodes')){
			$query = $query->join('subgroups','subgroups.group_id','=','groups.id');
			if($request->has('onlyWithCodes'))
				$query = $query->join('codes','codes.subgroup_id','=','subgroups.id');
		}
		$query = $query
			->groupBy('groups.id')
			->orderBy('groups.name_'.$lang,'asc')
			->get();
		return json_encode($query);
	}

	public function searchSubgroupsByName(Request $request){
		$lang = $request->input('lang');
		//$input = $request->input('input');
		$except = $request->input('except');
		$parent = $request->input('parent');
		$lang = ($lang==='ru')?'ru':'kk';
		App::setLocale($lang);
		$query = DB::table('subgroups')->select('subgroups.id','subgroups.name_'.$lang);
		//if($request->has('input'))
		//	$query = $query->where('subgroups.id', $input);
		if($except!=null)
			$query = $query->where('subgroups.id','!=',$except);
		if($parent!=null){
			$query = $query->join('groups','groups.id','=','subgroups.group_id')
				->where('groups.id',  $parent);
		}
		if($request->has('onlyWithCodes'))
			$query = $query->join('codes','codes.subgroup_id','=','subgroups.id');
		$query = $query
			->groupBy('subgroups.id')
			->orderBy('subgroups.name_'.$lang,'asc')
			->get();
		return json_encode($query);
	}

	public function searchCodes(Request $request){
		$lang = $request->input('lang');
		$lang = ($lang==='ru')?'ru':'kk';
		App::setLocale($lang);
		$by = $request->input('by');
		$query = DB::table('codes');
		if($by=='name'||$by=='description'){
			$query = $query
				->select($by.'_'.$lang)
				->groupBy($by.'_'.$lang)
				->pluck('codes.'.$by.'_'.$lang);
		}else if($by=='code'){
			$query = $query
				->select($by)
				->groupBy($by)
				->pluck('codes.'.$by);
		}
	    return json_encode($query);
	}

	public function migrateCodes(Request $request){
		DB::enableQueryLog();
		$data = $request->validate([
	        'codes' => "required|array|min:1",
	        'is_selected_all_codes' => 'required|boolean',
	        'applied_filters' => 'required_if:is_selected_all_codes,true',
	        'migrate_subgroup_id' => 'required|exists:subgroups,id'
	    ]);
		$codes = $request->input('codes');
		$selectedAll = $request->input('is_selected_all_codes');
		$selectedGroupId = explode('_', $request->input('applied_filters'))[0];
		$selectedSubgroupId = explode('_', $request->input('applied_filters'))[1];
		$isZKS = explode('_', $request->input('applied_filters'))[2];
		$code = explode('_', $request->input('applied_filters'))[3];
		$name = explode('_', $request->input('applied_filters'))[4];
		$description = explode('_', $request->input('applied_filters'))[5];
		$type = explode('_', $request->input('applied_filters'))[6];
		$migrateSubgroupId = $request->input('migrate_subgroup_id');
		$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
		$query = DB::table('codes');
    	$query = $query->join('subgroups', 'codes.subgroup_id', '=', 'subgroups.id');
    	$query = $query->join('groups', 'subgroups.group_id', '=', 'groups.id');
		$debug = '';
		if($selectedAll===false){
			//$debug .= "selecting by ids\n";
			$query = $query->whereIn('codes.id',$codes);
		}else{
			//$debug .= "selecting all\n";
			if(
				($selectedGroupId==null || $selectedGroupId==-1)
				&& ($selectedSubgroupId==null || $selectedSubgroupId==-1) 
				&& ($isZKS=='null' || $isZKS=='') 
				&& ($code=='null' || $code=='')
				&& ($name=='null' || $name=='')
				&& ($description=='null' || $description=='')
				&& ($type=='null' || $type=='')
			){
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Apply some filters before migrating all')],
				]);
				throw $error;
			}else{
				$applied = false;
				if(!($selectedSubgroupId=='null' || $selectedSubgroupId==-1))
					$query = $query->where('subgroups.id',$selectedSubgroupId);
				if(!($isZKS=='null' || $isZKS==''))
		    		$query = $query->where('groups.isZKS',($isZKS==='false') ? false : true);
		    	if(!($type=='null' || $type==''))
		    		$query->where('codes.type',$type);
				if(!($code=='null' || $code==''))
					$query = $query->where('codes.code','ilike', $code.'%');
				if(!($name=='null' || $name==''))
		    		$query = $query->where('codes.name_'.$lang,'ilike', '%'.$name.'%');
		    	if(!($description=='null' || $description==''))
		    		$query = $query->where('codes.description_'.$lang,'ilike', '%'.$description.'%');
		    		//$debug .= "selecting by query\n";
			}
		}
		$updateFields = array();
		if($migrateSubgroupId!='' && $migrateSubgroupId!=null){
			$target = Subgroup::where('id', $migrateSubgroupId)->first();
			if($target!=null){
				$updateFields['subgroup_id'] = $migrateSubgroupId;
				//$debug .= "updating subgroup_id\n";
			}else{
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Set subgroup exactly')],
				]);
				throw $error;
			}
		}
		$updateFields['user_id'] = $request->user()->id;
		$affectedRows = $query->update($updateFields);
		$response = [
			'message' => 'success',
			'affected_rows' => $affectedRows
 		];
	    return response($response, 200);
	}

	public function migrateSubgroups(Request $request){
		$data = $request->validate([
	        'items' => "required|array|min:1",
	        'is_selected_all' => 'required|boolean',
	        'applied_filters' => 'required_if:is_selected_all_codes,true',
	        'migrate_group_id' => 'required|exists:groups,id'
	    ]);
	    $items = $request->input('items');
		$selectedAll = $request->input('is_selected_all');
		$selectedGroupId = explode('_', $request->input('applied_filters'))[0];
		$isZKS = explode('_', $request->input('applied_filters'))[1];
		$name = explode('_', $request->input('applied_filters'))[2];
		$migrateGroupId = $request->input('migrate_group_id');
		$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = DB::table('subgroups');
		$query = $query->join('groups', 'subgroups.group_id', '=', 'groups.id');
		$debug = '';
		if($selectedAll===false){
			//$debug .= "selecting by ids\n";
			$query = $query->whereIn('subgroups.id',$items);
		}else{
			//$debug .= "selecting all\n";
			if(
				($selectedGroupId==null || $selectedGroupId==-1)
				&& ($isZKS=='null' || $isZKS=='') 
				&& ($name=='null' || $name=='')
			){
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Apply some filters before migrating all')],
				]);
				throw $error;
			}else{
				$applied = false;
				if(!($selectedGroupId==null || $selectedGroupId==-1)){
					$query = $query->where('groups.id',$selectedGroupId);
					//$debug .= "selecting by group_id\n";
				}
				if(!($isZKS=='null' || $isZKS==''))
		    		$query = $query->whereHas('subgroup', function($query) use($isZKS){
		    			$query = $query->whereHas('group', function($q) use($isZKS){
		    				$q->where('isZKS',($isZKS==='false') ? false : true);
		    			});
		    		});
				if(!($name=='null' || $name==''))
		    		$query = $query->where('name_'.$lang,'ilike', '%'.$name.'%');
			}
		}
		$updateFields = array();
		if($migrateGroupId!='' && $migrateGroupId!=null){
			$target = Group::where('id', $migrateGroupId)->first();
			if($target!=null){
				$updateFields['group_id'] = $target->id;
			}else{
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Set Group exactly')],
				]);
				throw $error;
			}
		}
		$updateFields['user_id'] = $request->user()->id;
		$affectedRows = $query->update($updateFields);
		$response = [
			'message' => 'success',
			'affected_rows' => $affectedRows
 		];
	    return response($response, 200);
	}

	public function excel(Request $request){
		try{
			$sortBy = $request->input('sort');
	    	$order = $request->input('order');
	    	$group_id = $request->input('group_id');
	    	$subgroup_id = $request->input('subgroup_id');
	    	$code = $request->input('code');
	    	$name = $request->input('name');
	    	$description = $request->input('description');
	    	$isZKS = $request->input('isZKS');
	    	$lang = $request->input('lang');
	    	$lang = ($lang=='ru')?'ru':'kk';
	    	App::setLocale($lang);
	    	$query = DB::table('codes')
	    		->select('codes.id as id','codes.name_'.$lang.' as name_'.$lang,'codes.description_'.$lang.' as description_'.$lang,'codes.code as code',
	    			'groups.name_'.$lang.' as group_name_'.$lang, 'subgroups.name_'.$lang.' as subgroup_name_'.$lang, 'codes.type as type'
	    	);
    		//->with('group')
    		//->with('subgroup');
	    	$query = $query->join('subgroups', 'subgroups.id','=','codes.subgroup_id');
	    	$query = $query->join('groups', 'groups.id','=','subgroups.group_id');
	    	if($group_id!=null)
	    		$query = $query->where('groups.id', $group_id);
	    	if($subgroup_id!=null)
	    		$query = $query->where('subgroups.id', $subgroup_id);
	    	if($code!==null )
	    		$query = $query->where('codes.code','ilike', $code.'%');
	    	if($name!==null)
	    		$query = $query->where('codes.name_'.$lang,'ilike', '%'.$name.'%');
	    	if($description!==null)
	    		$query = $query->where('codes.description_'.$lang,'ilike', '%'.$description.'%');
	    	if($isZKS != null)
	    		$query = $query->where('groups.isZKS', $isZKS);
	    	if($request->has('type'))
		    	$query = $query->where('type',$request->input('type'));
	    	if(in_array($sortBy,array('id','code','name_'.$lang,'description_'.$lang)))
	    		$query = $query->orderBy('codes.'.$sortBy,($order==='desc')?'desc':'asc');

	    	$data = $query->get();
	    	//$file = (new FastExcel($data))->export('codes.xlsx');
	    	return (new FastExcel($data))->download('codes.xlsx');
		}catch(\Exception $e){
			echo $e->getMessage(); exit;
		}
	}

	public function sync(Request $request){
		return 'todo';
	}
}
