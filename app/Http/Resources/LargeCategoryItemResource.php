<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/8/7
 * Time: 10:04
 */

namespace App\Http\Resources;


class LargeCategoryItemResource extends Resource
{
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'type' => $this->item_type,
            'item' => $this->item_type == 1
                ? (new TypeResource($this->type))->show(['id', 'name'])
                : (new EntityResource($this->entity))->show(['id', 'name']),
            'is_hot' => (bool)$this->is_hot,
            'is_new' => (bool)$this->is_new
        ]);
    }
}