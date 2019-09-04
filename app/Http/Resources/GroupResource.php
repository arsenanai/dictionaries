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
        //return json_encode($this);
        return [
            'id' => $this->id,
            'name_kk' => $this->name_kk,
            'name_ru' => $this->name_ru,
            'isZKS' => $this->isZKS,
        ];
    }
}
