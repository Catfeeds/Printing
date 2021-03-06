<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/6/22
 * Time: 21:29
 */

namespace App\Http\Controllers\Api;


use App\Exceptions\AccountIsExistException;
use App\Exceptions\BaseException;
use App\Exceptions\UserNotFoundException;
use App\Models\UserAuth;
use App\Services\SMS;
use App\Services\VerificationCode;
use Illuminate\Http\Request;

class SmsController extends ApiController
{
    /**
     * 发送短信
     *
     * @param Request $request
     * @return mixed
     * @throws AccountIsExistException
     * @throws BaseException
     * @throws UserNotFoundException
     */
    public function sendSms(Request $request)
    {
        $phone = $request->phone;

        if (!preg_match('/^1[3-9]\d{9}$/', $phone))
            throw new BaseException('请输入正确的手机号');

        $auth = UserAuth::where('identifier', $phone)->first();

        if ($request->is_register && $auth) throw new AccountIsExistException();
        elseif (!$request->is_register && !$auth) throw new UserNotFoundException();

        $verification = VerificationCode::create($phone);

        SMS::sendSms($phone, $verification['verification_code']);

        return $this->success([
            'verification_token' => $verification['verification_token']
        ]);
    }
}