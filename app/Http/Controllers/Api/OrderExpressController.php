<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/7/31
 * Time: 18:07
 */

namespace App\Http\Controllers\Api;


use App\Models\OrderExpress;
use Illuminate\Http\Request;

class OrderExpressController extends ApiController
{
    public function store(Request $request)
    {
        OrderExpress::create([
            'order_id' => $request->order_id,
            'company' => $request->company,
            'tracking_no' => $request->tracking_no
        ]);

        return $this->created();
    }
}