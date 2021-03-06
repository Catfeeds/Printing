<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/8/15
 * Time: 10:20
 */

namespace App\Http\Controllers\Api;


use App\Exceptions\BaseException;
use App\Models\Order;
use App\Services\WxPay;
use Illuminate\Http\Request;

class WxPayController extends ApiController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws BaseException
     * @throws \App\Exceptions\TokenException
     */
    public function pay(Request $request)
    {
        return $this->success(
            (new WxPay())
                ->setOrder(Order::findOrFail($request->order_id))
                ->pay()
        );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function recharge(Request $request)
    {
        return $this->success(
            (new WxPay())->recharge($request->price)
        );
    }

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function notify()
    {
        return (new WxPay())->notify();
    }
}