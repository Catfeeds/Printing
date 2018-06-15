<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/6/13
 * Time: 9:16
 */

namespace App\Http\Resources;


class ExpressResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_weight' => $this->first_weight,
            'additional_weight' => $this->additional_weight,
            'capped' => $this->capped,
            'region' => $this->regions()->pluck('name')
        ];
    }
}