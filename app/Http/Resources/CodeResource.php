<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = [
            'id' => $this->id,
            'code' => $this->code,
            'name_kk' => $this->name_kk,
            'name_ru' => $this->name_ru,
            'description_kk' => $this->description_kk,
            'description_ru' => $this->description_ru,
            'type' => $this->type,
        ];
        if($this->subgroup!=null){
            $result['subgroup'] = [
                'id' => $this->subgroup->id,
                'name_kk' => $this->subgroup->name_kk,
                'name_ru' => $this->subgroup->name_ru,
            ];
            if($this->subgroup->group!=null)
                $result['subgroup']['group'] = [
                    'id' => $this->subgroup->group->id,
                    'name_kk' => $this->subgroup->group->name_kk,
                    'name_ru' => $this->subgroup->group->name_ru,
                    'isZKS' => $this->subgroup->group->isZKS
                ];
            else
                $result['subgroup']['group'] = null;
        }else
            $result['subgroup'] = null;
        return $result;
    }
}
