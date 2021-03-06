<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/11/11
 * Time: 20:18
 */

namespace App\Http\Controllers\Api;


use App\Models\Order;
use App\Services\Tokens\TokenFactory;
use Illuminate\Http\Request;

class ShoppingBillController extends ApiController
{
    public function index(Request $request)
    {
        $order = Order::findOrFail($request->order_id);

        $goods = [];
        $content = json_decode($order->snap_content, true);
        foreach ($content as $good) {
            array_push($goods, [
                'image' => $good['image']['src'],
                'describe' => $good['name'] . ',' . implode(',', explode('|', $good['combination']['name'])),
                'type' => $good['type'],
                'count' => $good['count'],
                'price' => $good['price']
            ]);
        }

        return $this->success([
            'order_no' => $order->order_no,
            'created_at' => (string)$order->created_at,
            'address' => json_decode($order->snap_address, true),
            'goods' => $goods,
            'goods_price' => $order->goods_price,
            'total_weight' => $order->total_weight / 1000,
            'total_count' => $order->goods_count,
            'freight' => $order->freight,
            'discount_amount' => $order->discount_amount + $order->member_discount,
            'total_price' => $order->total_price,
            'remark' => $order->remark,
            'clerk' => TokenFactory::getCurrentUser()->nickname
        ]);
    }
}