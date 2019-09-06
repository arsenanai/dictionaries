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
    	}
    	$query = $query->paginate(15);
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
    	}
    	$query = $query->paginate(15);
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
    	$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$query = Code::with('subgroup.group');
    	if($group_name!=null)
    		$query = $query->whereHas('group', function($q) use($lang, $group_name){
    			$q->where('name_'.$lang, 'ilike', '%'.$group_name.'%');
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
    	if(in_array($sortBy,array('id','code','name_kk','name_ru','description_kk','description_ru'))){
    		$query = $query->orderBy($sortBy,($order==='desc')?'desc':'asc');
    	}
    	$query = $query->paginate(15);
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
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => 'required|string|max:256',
	        'isZKS' => 'boolean',
	    ]);
	    $group->create($data);
	    return new GroupResource($group);
	}

	public function createSubgroup(Subgroup $group, Request $request){
		/*$data = $request->validate([
	        'name' => 'required',
	        'email' => 'required|unique:users',
	        'password' => 'required|min:8',
	    ]);

	    return new UserResource(User::create([
	        'name' => $data['name'],
	        'email' => $data['email'],
	        'password' => bcrypt($data['password']),
	    ]));*/
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => 'required|string|max:256',
	        'group.id' => 'required|exists:groups,id',
	        //'isZKS' => 'boolean',
	    ]);
	    $subgroup = new Subgroup();
	    $subgroup->name_kk = $request->input('name_kk');
	    $subgroup->name_ru = $request->input('name_ru');
	    $subgroup->group_id = $request->input('group.id');
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
	        //'isZKS' => 'boolean',
	        'subgroup.id' => 'required|exists:subgroups,id',
	    ]);
	    $code = new Code();
	    $code->code = $request->input('code');
	    $code->name_kk = $request->input('name_kk');
	    $code->name_ru = $request->input('name_ru');
	    $code->description_kk = $request->input('description_kk');
	    $code->description_ru = $request->input('description_ru');
	    //$code->isZKS = ($request->input('isZKS')) ? true : false;
	    //$code->group_id = $request->input('group.id');
	    //if ($request->has('subgroup.id')) {
        	$code->subgroup_id = $request->input('subgroup.id');
    	//}
	    $code->save();
	    return new CodeResource($code);
	}

	public function updateGroup(Group $group, Request $request){
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => 'required|string|max:256',
	        'isZKS' => 'boolean',
	    ]);
	    $group->update($data);
	    return new GroupResource($group);
	}

	public function updateSubgroup(Subgroup $subgroup, Request $request){
		$data = $request->validate([
	        'name_kk' => 'required|string|max:256',
	        'name_ru' => 'required|string|max:256',
	        'group.id' => 'required|exists:groups,id',
	        //'isZKS' => 'boolean',
	    ]);
	    $subgroup->update($data);
	    return new SubgroupResource($subgroup);
	}

	public function updateCode(Code $code, Request $request){
		$data = $request->validate([
	        'code' => 'required|string|between:17,17',
	        'name_kk' => 'required|string|max:300',
	        'name_ru' => 'required|string|max:300',
	        'description_kk' => 'string|max:1024',
	        'description_ru' => 'string|max:1024',
	        //'isZKS' => 'boolean',
	        //'group.id' => 'required|exists:groups,id',
	        'subgroup.id' => 'exists:subgroups,id'
	    ]);
	    //$code->isZKS = ($request->isZKS) ? true : false;
	    $code->update($data);
	    return new CodeResource($code);
	}

	public function destroyGroup(Group $group)
	{
		$affectedRows = Code::where('group_id', $group->id)
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
		$input = $request->input('name');
		$except = $request->input('except');
		$lang = ($lang==='ru')?'ru':'kk';
		App::setLocale($lang);
		$result = Group::select('id','name_kk','name_ru');
		$result = $result->where('name_'.$lang, 'ilike', '%' . $input . '%');
		if($except!=null)
			$result = $result->where('name_'.$lang,'not ilike','%' .$except. '%');
		$result = $result
			->limit(20)->get();
		return GroupResource::collection($result);
	}

	public function searchSubgroupsByName(Request $request){
		$lang = $request->input('lang');
		$input = $request->input('name');
		$except = $request->input('except');
		$parent = $request->input('parent');
		$lang = ($lang==='ru')?'ru':'kk';
		App::setLocale($lang);
		$result = Subgroup::select('id','name_kk','name_ru')
		->where('name_'.$lang, 'ilike', '%' . $input . '%');
		if($except!=null)
			$result = $result->where('name_'.$lang,'not ilike','%' .$except. '%');
		if($parent!=null)
			$result = $result->whereHas('group', function($q) use($lang, $parent){
    			$q->where('name_'.$lang, 'ilike', '%'.$parent.'%');
			});
		$result = $result
			->limit(20)->get();
		return SubgroupResource::collection($result);
	}

	public function searchCodes(Request $request){
		$lang = $request->input('lang');
		$lang = ($lang==='ru')?'ru':'kk';
		App::setLocale($lang);
		$code = $request->input('code');
		$name = $request->input('name');
		$description = $request->input('description');
		$result = Code::select('id','name_kk','name_ru','description_kk','description_ru','code');
		if($code!=null)
			$result = $result->where('code','ilike',$code. '%');
		if($name!=null)
			$result = $result->where('name_'.$lang,'ilike','%'.$name.'%');
		if($description!=null)
			$result = $result->where('description_'.$lang,'ilike','%'.$description.'%');
		$result = $result
			->limit(10)->get();
		return CodeResource::collection($result);
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
		$selectedGroupName = (int)explode('_', $request->input('applied_filters'))[0];
		$selectedSubgroupName = (int)explode('_', $request->input('applied_filters'))[1];
		$isZKS = explode('_', $request->input('applied_filters'))[2];
		$code = explode('_', $request->input('applied_filters'))[3];
		$name = explode('_', $request->input('applied_filters'))[4];
		$description = explode('_', $request->input('applied_filters'))[5];
		$migrateSubgroupName = $request->input('migrate_subgroup_name');
		$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
		$request = DB::table('codes');
		$request = $request->join('groups', 'codes.group_id', '=', 'groups.id');
    	$request = $request->join('subgroups', 'codes.subgroup_id', '=', 'subgroups.id');
		$debug = '';
		if($selectedAll===false){
			//$debug .= "selecting by ids\n";
			$request = $request->whereIn('codes.id',$codes);
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
				if(!($selectedGroupName==null || $selectedGroupName=='')){
					$request = $request->where('group.name_'.$lang,'ilike','%'.$selectedGroupName.'%');
					//$debug .= "selecting by group_id\n";
				}
				if(!($selectedSubgroupName==null || $selectedSubgroupName=='')){
					$request = $request->where('subgroup.name_'.$lang,'ilike','%'.$selectedSubgroupName.'%');
					//$debug .= "selecting by subgroup_id\n";
				}
				if(!($isZKS=='null' || $isZKS=='')){
					$request = $request->where('isZKS',($isZKS=='false') ? false : true);
					//$debug .= "selecting by ZKS\n";
				}
				if(!($isZKS=='null' || $isZKS==''))
		    		$request = $request->whereHas('subgroup', function($request) use($isZKS){
		    			$request = $request->whereHas('group', function($q) use($isZKS){
		    				$q->where('isZKS',($isZKS==='false') ? false : true);
		    			});
		    		});
				if(!($code=='null' || $code==''))
					$request = $request->where('code','ilike', $code.'%');
				if(!($name=='null' || $name==''))
		    		$request = $request->where('name_'.$lang,'ilike', '%'.$name.'%');
		    	if(!($description=='null' || $description==''))
		    		$request = $request->where('description_'.$lang,'ilike', '%'.$description.'%');
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
		$affectedRows = $request->update($updateFields);
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
		$selectedGroupName = (int)explode('_', $request->input('applied_filters'))[0];
		$isZKS = explode('_', $request->input('applied_filters'))[1];
		$name = explode('_', $request->input('applied_filters'))[2];
		$migrateSubgroupId = $request->input('migrate_subgroup_id');
		$lang = $request->input('lang');
    	$lang = ($lang==='ru')?'ru':'kk';
    	App::setLocale($lang);
    	$request = DB::table('subgroups');
		$request = $request->join('groups', 'subgroups.group_id', '=', 'groups.id');
		$debug = '';
		if($selectedAll===false){
			//$debug .= "selecting by ids\n";
			$request = $request->whereIn('subgroups.id',$items);
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
					$request = $request->where('group.name_'.$lang,'ilike','%'.$selectedGroupName.'%');
					//$debug .= "selecting by group_id\n";
				}
				if(!($isZKS=='null' || $isZKS==''))
		    		$request = $request->whereHas('subgroup', function($request) use($isZKS){
		    			$request = $request->whereHas('group', function($q) use($isZKS){
		    				$q->where('isZKS',($isZKS==='false') ? false : true);
		    			});
		    		});
				if(!($name=='null' || $name==''))
		    		$request = $request->where('name_'.$lang,'ilike', '%'.$name.'%');
			}
		}
		$updateFields = array();
		if($migrateSubgroupId>-1){
			$target1 = Subgroup::find($migrateSubgroupId);
			if($target1!=null){
				$updateFields['group_id'] = $target1->id;
				//$debug .= "updating group_name\n";
			}else{
				$error = \Illuminate\Validation\ValidationException::withMessages([
				   'Groups' => [__('Set subgroup exactly')],
				]);
				throw $error;
			}
		}
		$affectedRows = $request->update($updateFields);
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
	    	$query = $query->join('groups', 'groups.id','=','codes.group_id');
	    	$query = $query->join('subgroups', 'subgroups.id','=','codes.subgroup_id');
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
	    		$query = $query->where('isZKS',($isZKS==='false') ? false : true);
	    	if($isZKS != null)
	    		$query = $query->whereHas('subgroup', function($query) use($isZKS){
	    			$query = $query->whereHas('group', function($q) use($isZKS){
	    				$q->where('isZKS',($isZKS==='false') ? false : true);
	    			});
	    		});
	    	if(in_array($sortBy,array('id','code','name_kk','name_ru','description_kk','description_ru')))
	    		$query = $query->orderBy('codes.'.$sortBy,($order==='desc')?'desc':'asc');

	    	$items = $query->get();
	    	/*$query->chunk(10000, function ($is) use($items){
			  array_merge($items, $is->toArray());
			});*/
			//echo json_encode($items); exit;

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="codes.csv"');

			$fp = fopen('php://output', 'wb');
			fputcsv($fp, array('Id',__('Group'),__('Subgroup'),__('Code'),__('Name'),__('Description')));
			foreach ( $items as $i ) {
			    fputcsv($fp, array($i->id,$i->group_name_ru,$i->subgroup_name_ru,$i->code,$i->name_ru,$i->description_ru));
			}
			fclose($fp);
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
