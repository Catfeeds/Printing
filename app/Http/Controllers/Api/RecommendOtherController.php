<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/9/7
 * Time: 14:21
 */

namespace App\Http\Controllers\Api;


use App\Models\RecommendOther;
use Illuminate\Http\Request;

class RecommendOtherController extends ApiController
{
    public function autoRecommend(Request $request)
    {
        setEnv(['AUTO_RECOMMEND' => $request->is_open]);

        return $this->message('更新成功');
    }

    public function index()
    {
        return $this->success([
            'data' => RecommendOther::all()
        ]);
    }

    public function update(Request $request, RecommendOther $recommendOther)
    {
        $recommendOther->update([
            'entity_id' => $request->entity_id
        ]);

        return $this->message('更新成功');
    }
}