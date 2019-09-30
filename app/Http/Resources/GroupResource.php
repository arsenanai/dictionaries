<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            'name_kk' => $this->name_kk,
            'name_ru' => $this->name_ru,
            'isZKS' => $this->isZKS,
            'subgroups_count' => $this->subgroups_count,
        ];
        if($this->user!=null){
            $result['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ];
        }else
            $result['user'] = null;
        return $result;
    }
}
