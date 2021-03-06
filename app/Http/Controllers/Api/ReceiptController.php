<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/7/31
 * Time: 11:01
 */

namespace App\Http\Controllers\Api;


use App\Exceptions\BaseException;
use App\Http\Requests\StoreReceipt;
use App\Http\Requests\UpdateReceipt;
use App\Http\Resources\ReceiptCollection;
use App\Http\Resources\ReceiptResource;
use App\Models\Order;
use App\Models\Receipt;
use App\Services\Tokens\TokenFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReceiptController extends ApiController
{
    public function index(Request $request)
    {
        $receipts = (new Receipt())
            ->when($request->is_receipted, function ($query) use ($request) {
                $query->where('is_receipted', $request->is_receipted);
            })
            ->when($request->tax_no, function ($query) use ($request) {
                $query->where('tax_no', $request->tax_no);
            })
            ->when($request->begin_time, function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    Carbon::parse(date('Y-m-d H:i:s', $request->begin_time)),
                    Carbon::parse(date('Y-m-d H:i:s', $request->end_time))
                ]);
            })
            ->orderBy('is_receipted', 'asc')
            ->latest()
            ->paginate(Receipt::getLimit());

        return $this->success(new ReceiptCollection($receipts));
    }

    /**
     * @param Receipt $receipt
     * @return mixed
     * @throws BaseException
     * @throws \App\Exceptions\TokenException
     */
    public function show(Receipt $receipt)
    {
        if (!TokenFactory::isAdmin() && !TokenFactory::isValidOperate($receipt->user_id))
            throw new BaseException('不能查看别人的发票信息');

        return $this->success(new ReceiptResource($receipt));
    }

    /**
     * @param StoreReceipt $request
     * @return mixed
     * @throws \Throwable
     */
    public function store(StoreReceipt $request)
    {
        \DB::transaction(function () use ($request) {
            $money = 0;
            Order::whereIn('id', $request->order_ids)
                ->each(function ($order) use (&$money) {
                    if ($order->receipt_id) throw new BaseException('订单' . $order->order_no . '已开过发票');
                    $money += $order->total_price;
                });

            $receipt = Receipt::receipted($request->receipt_info, $money);

            Order::whereIn('id', $request->order_ids)
                ->update(['receipt_id' => $receipt->id]);
        });

        return $this->created();
    }

    public function update(UpdateReceipt $request, Receipt $receipt)
    {
        Receipt::updateField($request, $receipt, ['is_receipted']);

        return $this->message('更新成功');
    }

    public function batchReceipted(Request $request)
    {
        $ids = $request->ids;

        Receipt::whereIn('id', $ids)
            ->update(['is_receipted' => 1]);

        return $this->message('更新成功');
    }
}