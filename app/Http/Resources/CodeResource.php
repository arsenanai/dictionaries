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
            'isZKS' => $this->isZKS
        ];
        if($this->group!=null)
            $result['group'] = [
                'id' => $this->group->id,
                'name_kk' => $this->group->name_kk,
                'name_ru' => $this->group->name_ru,
            ];
        else
            $result['group'] = null;
        if($this->subgroup!=null)
            $result['subgroup'] = [
                'id' => $this->subgroup->id,
                'name_kk' => $this->subgroup->name_kk,
                'name_ru' => $this->subgroup->name_ru,
            ];
        else
            $result['subgroup'] = null;
        return $result;
    }
}
