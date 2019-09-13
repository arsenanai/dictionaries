<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Group;
use App\Subgroup;
use App\Code;
use App;
use App\Http\Resources\GroupResource;
use App\Http\Resources\SubgroupResource;
use App\Http\Resources\CodeResource;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Facades\Validator;

class TRUController extends Controller
{
	public function indexGroup(Request $request)
	{
		$sortBy = $request->input('sort');
    	$order = $request->input('order');
    	$name = $request->input('name');
    	$isZKS = $request->input('isZKS');
    	$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = Group::where('id','>',-1);
    	if($name!==null && $name != '')
    		$query = $query->where('name_'.$lang,'ilike', '%'.$name.'%');
    	if($isZKS != null && $isZKS!='')
    		$query = $query->where('isZKS',($isZKS==='false') ? false : true);
    	if(in_array($sortBy,array('id','name_kk','name_ru'))){
    		$query = $query->orderBy($sortBy,($order==='desc')?'desc':'asc');
    	}else
    		$query = $query->orderBy('id','asc');
    	$query = $query->paginate(env('APP_PAGINATION_PER_PAGE',15));
    	return GroupResource::collection($query);
    }
    public function indexSubgroup(Request $request)
    {
    	$sortBy = $request->input('sort');
    	$order = $request->input('order');
    	$group_name = $request->input('group_name');
    	$name = $request->input('name');
    	$isZKS = $request->input('isZKS');
    	$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = Subgroup::with('group');
    	if($group_name!=null)
    		$query = $query->whereHas('group', function($q) use($lang, $group_name){
    			$q->where('name_'.$lang, 'ilike', '%'.$group_name.'%');
			});
    	if($name!==null && $name != '')
    		$query = $query->where('name_'.$lang,'ilike', '%'.$name.'%');
    	if($isZKS != null && $isZKS!='')
    		$query = $query->whereHas('group', function($q) use($isZKS){
    			$q->where('isZKS',($isZKS==='false') ? false : true);
    		});
    	if(in_array($sortBy,array('id','name_kk','name_ru'))){
    		$query = $query->orderBy($sortBy,($order==='desc')?'desc':'asc');
    	}else
    		$query = $query->orderBy('id','asc');
    	$query = $query->paginate(env('APP_PAGINATION_PER_PAGE',15));
    	return SubgroupResource::collection($query);
    }
    public function indexCode(Request $request)
    {
    	$sortBy = $request->input('sort');
    	$order = $request->input('order');
    	$group_name = $request->input('group_name');
    	$subgroup_name = $request->input('subgroup_name');
    	$code = $request->input('code');
    	$name = $request->input('name');
    	$description = $request->input('description');
    	$isZKS = $request->input('isZKS');
    	$type = $request->input('type');
    	$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = Code::with('subgroup.group');
    	if($group_name!=null)
    		$query = $query->whereHas('subgroup', function($q) use($lang, $group_name){
    			$q = $q->whereHas('group', function($q) use($lang, $group_name){
    				$q->where('name_'.$lang, 'ilike', '%'.$group_name.'%');
    			});
			});
    	if($subgroup_name!=null)
    		$query = $query->whereHas('subgroup', function($q) use($lang, $subgroup_name){
    			$q->where('name_'.$lang, 'ilike', '%'.$subgroup_name.'%');
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
    	$query = $query->paginate(env('APP_PAGINATION_PER_PAGE',15));
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
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256|unique:groups',
	        'name_ru' => 'required|string|max:256|unique:groups',
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
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => 'required|string|max:256',
	        'isZKS' => 'boolean',
	    ]);
	    if($group->id!==1)//Others Group
	    	$group->update($data);
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
		$input = $request->input('input');
		$except = $request->input('except');
		$lang = ($lang==='kk')?'kk':'ru';
		App::setLocale($lang);
		$query = DB::table('groups')->select('groups.id','groups.name_'.$lang,'isZKS');
		$query = $query->where('groups.name_'.$lang, 'ilike', '%' . $input . '%');
		if($except!=null)
			$query = $query->where('groups.name_'.$lang,'not ilike','%' .$except. '%');
		if($request->has('onlyWithSubgroups'))
			$query = $query->join('subgroups','subgroups.group_id','=','groups.id');
		$query = $query
			->groupBy('groups.id')
			->orderBy('groups.name_'.$lang,'asc')
			->limit(100)->get();
		return json_encode($query);
	}

	public function searchSubgroupsByName(Request $request){
		$lang = $request->input('lang');
		$input = $request->input('input');
		$except = $request->input('except');
		$parent = $request->input('parent');
		$lang = ($lang==='ru')?'ru':'kk';
		App::setLocale($lang);
		$query = DB::table('subgroups')->select('subgroups.id','subgroups.name_'.$lang);
		if($request->has('input'))
			$query = $query->where('subgroups.name_'.$lang, 'ilike', '%' . $input . '%');
		if($except!=null)
			$query = $query->where('subgroups.name_'.$lang,'not ilike','%' .$except. '%');
		if($parent!=null){
			$query = $query->join('groups','groups.id','=','subgroups.group_id')
				->where('groups.name_'.$lang, 'ilike', '%'.$parent.'%');
		}
		$query = $query
			->orderBy('subgroups.name_'.$lang,'asc')
			->limit(100)->get();
		return json_encode($query);
	}

	public function searchCodes(Request $request){
		$lang = $request->input('lang');
		$lang = ($lang==='ru')?'ru':'kk';
		App::setLocale($lang);
		$input = $request->input('input');
		$code = $request->input('code');
		$name = $request->input('name');
		$description = $request->input('description');
		$select='';
		if($name!=null||$input!=null)
			$select .= 'name_'.$lang.',';
		if($description!=null)
			$select .= 'description_'.$lang.',';
		if($code!=null)
			$select .= 'code,';
		$query = DB::table('codes')->select(DB::raw(substr($select,0,-1)));
		if($name!=null){
			$query = $query->where('name_'.$lang,'ilike','%'.$name.'%')->orderBy('name_'.$lang,'asc');
		}else if($description!=null){
			$query = $query->where('description_'.$lang,'ilike','%'.$description.'%')->orderBy('description_'.$lang,'asc');
		}else{
			$query = $query->where('code','ilike',$code. '%')->orderBy('code','asc');
		}
		if($input!=null){
			$query = $query->where('name_'.$lang,'ilike','%'.$input.'%')->orderBy('name_'.$lang,'asc');
		}
		$query = $query
			->limit(env('APP_TYPEAHEAD_LIMIT',10))->get();
	    return json_encode($query);
	}

	public function migrateCodes(Request $request){
		//DB::enableQueryLog();
		$data = $request->validate([
	        'codes' => "required|array|min:1",
	        'is_selected_all_codes' => 'required|boolean',
	        'applied_filters' => 'required_if:is_selected_all_codes,true',
	        'migrate_subgroup_name' => 'required'
	    ]);
		$codes = $request->input('codes');
		$selectedAll = $request->input('is_selected_all_codes');
		$selectedGroupName = explode('_', $request->input('applied_filters'))[0];
		$selectedSubgroupName = explode('_', $request->input('applied_filters'))[1];
		$isZKS = explode('_', $request->input('applied_filters'))[2];
		$code = explode('_', $request->input('applied_filters'))[3];
		$name = explode('_', $request->input('applied_filters'))[4];
		$description = explode('_', $request->input('applied_filters'))[5];
		$migrateSubgroupName = $request->input('migrate_subgroup_name');
		$type = $request->input('type');
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
				($selectedGroupName==null || $selectedGroupName=='')
				&& ($selectedSubgroupName==null || $selectedSubgroupName=='') 
				&& ($isZKS=='null' || $isZKS=='') 
				&& ($code=='null' || $code=='')
				&& ($name=='null' || $name=='')
				&& ($description=='null' || $description=='')
			){
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Apply some filters before migrating all')],
				]);
				throw $error;
			}else{
				$applied = false;
				/*if(!($selectedGroupName==null || $selectedGroupName=='')){
					$query = $query->where('group.name_'.$lang,'ilike','%'.$selectedGroupName.'%');
					//$debug .= "selecting by group_id\n";
				}*/
				if(!($selectedSubgroupName==null || $selectedSubgroupName=='')){
					$query = $query->where('subgroup.name_'.$lang,'ilike','%'.$selectedSubgroupName.'%');
					//$debug .= "selecting by subgroup_id\n";
				}
				if(!($isZKS=='null' || $isZKS==''))
		    		$query = $query->whereHas('subgroup', function($query) use($isZKS){
		    			$query = $query->whereHas('group', function($q) use($isZKS){
		    				$q->where('isZKS',($isZKS==='false') ? false : true);
		    			});
		    		});
		    	if($request->has('type'))
		    		$query->where('type',$type);
				if(!($code=='null' || $code==''))
					$query = $query->where('code','ilike', $code.'%');
				if(!($name=='null' || $name==''))
		    		$query = $query->where('codes.name_'.$lang,'ilike', '%'.$name.'%');
		    	if(!($description=='null' || $description==''))
		    		$query = $query->where('description_'.$lang,'ilike', '%'.$description.'%');
		    		//$debug .= "selecting by query\n";
				
			}
		}
		$updateFields = array();
		if($migrateSubgroupName!='' && $migrateSubgroupName!=null){
			$target = Subgroup::where('name_'.$lang, $migrateSubgroupName)->first();
			if($target!=null){
				$updateFields['subgroup_id'] = $target->id;
				//$debug .= "updating subgroup_name\n";
			}else{
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Set subgroup exactly')],
				]);
				throw $error;
			}
		}
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
	        'migrate_group_name' => 'required'
	    ]);
	    $items = $request->input('items');
		$selectedAll = $request->input('is_selected_all');
		$selectedGroupName = explode('_', $request->input('applied_filters'))[0];
		$isZKS = explode('_', $request->input('applied_filters'))[1];
		$name = explode('_', $request->input('applied_filters'))[2];
		$migrateGroupName = $request->input('migrate_group_name');
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
				($selectedGroupName==null || $selectedGroupName=='')
				&& ($isZKS=='null' || $isZKS=='') 
				&& ($name=='null' || $name=='')
			){
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Apply some filters before migrating all')],
				]);
				throw $error;
			}else{
				$applied = false;
				if(!($selectedGroupName==null || $selectedGroupName=='')){
					$query = $query->where('groups.name_'.$lang,'ilike','%'.$selectedGroupName.'%');
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
		if($migrateGroupName!='' && $migrateGroupName!=null){
			$target = Group::where('name_'.$lang, $migrateGroupName)->first();
			if($target!=null){
				$updateFields['group_id'] = $target->id;
			}else{
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Set subgroup exactly')],
				]);
				throw $error;
			}
		}
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
	    	$group_name = $request->input('group_name');
	    	$subgroup_name = $request->input('subgroup_name');
	    	$code = $request->input('code');
	    	$name = $request->input('name');
	    	$description = $request->input('description');
	    	$isZKS = $request->input('isZKS');
	    	$lang = $request->input('lang');
	    	$lang = ($lang=='ru')?'ru':'kk';
	    	App::setLocale($lang);
	    	$query = DB::table('codes')
	    		->select('codes.id as id','codes.name_'.$lang.' as name_'.$lang,'codes.description_'.$lang.' as description_'.$lang,'codes.code as code',
	    			'groups.name_'.$lang.' as group_name_'.$lang, 'subgroups.name_'.$lang.' as subgroup_name_'.$lang
	    	);
    		//->with('group')
    		//->with('subgroup');
	    	$query = $query->join('subgroups', 'subgroups.id','=','codes.subgroup_id');
	    	$query = $query->join('groups', 'groups.id','=','subgroups.group_id');
	    	if($group_name!=null)
	    		$query = $query->where('groups.name_'.$lang, 'ilike', '%'.$group_name.'%');
	    	if($subgroup_name!=null)
	    		$query = $query->where('subgroups.name_'.$lang, 'ilike', '%'.$subgroup_name.'%');
	    	if($code!==null )
	    		$query = $query->where('code','ilike', $code.'%');
	    	if($name!==null)
	    		$query = $query->where('name_'.$lang,'ilike', '%'.$name.'%');
	    	if($description!==null)
	    		$query = $query->where('description_'.$lang,'ilike', '%'.$description.'%');
	    	if($isZKS != null)
	    		$query = $query->whereHas('subgroup', function($query) use($isZKS){
	    			$query = $query->whereHas('group', function($q) use($isZKS){
	    				$q->where('isZKS',($isZKS==='false') ? false : true);
	    			});
	    		});
	    	if($request->has('type'))
		    	$query = $query->where('type',$query->input('type'));
	    	if(in_array($sortBy,array('id','code','name_'.$lang,'description_'.$lang)))
	    		$query = $query->orderBy('codes.'.$sortBy,($order==='desc')?'desc':'asc');

	    	return json_encode($query->get());
	    	/*$query->chunk(10000, function ($is) use($items){
			  array_merge($items, $is->toArray());
			});*/
			//echo json_encode($items); exit;

			/*header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="codes.csv"');

			$fp = fopen('php://output', 'wb');
			fputcsv($fp, array('Id',__('Group'),__('Subgroup'),__('Code'),__('Name'),__('Description')));
			foreach ( $items as $i ) {
			    fputcsv($fp, array($i->id,$i->group_name_ru,$i->subgroup_name_ru,$i->code,$i->name_ru,$i->description_ru));
			}
			fclose($fp);*/
	    	/*$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setCellValue('A1', 'Id');
			$sheet->setCellValue('B1', __('Group'));
			$sheet->setCellValue('C1', __('Subgroup'));
			$sheet->setCellValue('D1', __('Code'));
			$sheet->setCellValue('E1', __('Name'));
			$sheet->setCellValue('F1', __('Description'));
			
			$rows = 2;
			foreach($items as $i){
				//echo $i->name_ru; exit; 
				$sheet->setCellValue('A' . $rows, $i->id);
				$sheet->setCellValue('B' . $rows, $i->group_name_ru);
				$sheet->setCellValue('C' . $rows, $i->subgroup_name_ru);
				$sheet->setCellValue('D' . $rows, $i->code);
				$sheet->setCellValue('E' . $rows, $i->name_ru);
				$sheet->setCellValue('F' . $rows, $i->description_ru);
				$rows++;
			}
			echo 'without error till here'; exit;
			//$fileName = "codes.xlsx";
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    		header('Content-Disposition: attachment; filename="codes.xlsx"');
			$writer->save("php://output");*/
		}catch(\Exception $e){
			echo $e->getMessage(); exit;
		}
	}

	public function sync(Request $request){
		return 'todo';
	}
}
