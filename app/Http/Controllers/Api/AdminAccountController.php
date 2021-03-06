<?php
/**
 * Created by PhpStorm.
 * User: 392113643
 * Date: 2018/6/7
 * Time: 9:49
 */

namespace App\Http\Controllers\Api;


use App\Exceptions\AccountIsExistException;
use App\Exceptions\BaseException;
use App\Exceptions\RegisterException;
use App\Http\Requests\StoreAdminAccount;
use App\Http\Resources\AdminAccountResource;
use App\Models\User;
use App\Models\UserAuth;
use DB;
use Illuminate\Http\Request;

class AdminAccountController extends ApiController
{
    public function index()
    {
        return $this->success([
            'data' => AdminAccountResource::collection(
                User::where('is_admin', 1)
                    ->where('id', '>', 1)
                    ->get()
            )
        ]);
    }

    public function show($id)
    {
        return $this->success(new AdminAccountResource(
            User::where('is_admin', 1)
                ->where('id', $id)
                ->firstOrFail()
        ));
    }

    /**
     * 创建管理员账号
     *
     * @param StoreAdminAccount $request
     * @return mixed
     * @throws AccountIsExistException
     * @throws \Exception
     * @throws RegisterException
     */
    public function store(StoreAdminAccount $request)
    {
        $identity = UserAuth::where('platform', 'local')
            ->where('identity_type', 'account')
            ->where('identifier', $request->username)
            ->first();

        if ($identity) {
            throw new AccountIsExistException('该账号已存在');
        }

        if (!preg_match('/^[a-zA-Z][-_a-zA-Z0-9]{5,19}$/', $request->username))
            throw new RegisterException('账号为6~20个字母、数字、下划线或减号，以字母开头');

        if (!preg_match('/^[a-zA-Z](?=.*\d)[_0-9a-zA-Z]{7,17}$/', $request->password))
            throw new RegisterException('密码为8~18位字母、数字或下划线，以字母开头，必须包含字母和数字');

        DB::beginTransaction();
        try {
            // 创建用户信息
            $user = User::create([
                'nickname' => $request->username,
                'avatar' => config('setting.default_avatar_url'),
                'account' => $request->username,
                'is_bind_account' => 1,
                'is_admin' => 1
            ]);

            // 创建用户登录信息
            UserAuth::create([
                'user_id' => $user->id,
                'platform' => 'local',
                'identity_type' => 'account',
                'identifier' => $request->username,
                'credential' => \Hash::make($request->password),
                'verified' => 1
            ]);

            $user->assignRole($request->roles);

            DB::commit();

            return $this->created();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RegisterException();
        }
    }

    /**
     * 更新管理员账号信息
     *
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws BaseException
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$user->is_admin)
            throw new BaseException('该账号不存在');

        DB::transaction(function () use ($request, $user) {
            $userAuth = UserAuth::where('user_id', $user->id)->first();

            $userAuth->update([
                'credential' => \Hash::make($request->password),
            ]);

            if ($request->username) {
                $user->update([
                    'nickname' => $request->username
                ]);
                $userAuth->update([
                    'identifier' => $request->username
                ]);
            }

            if ($request->roles)
                $user->syncRoles($request->roles);
        });

        return $this->message('修改成功');
    }

    /**
     * 删除管理员
     *
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            User::where('id', $id)
                ->where('is_admin', 1)
                ->forceDelete();
            UserAuth::where('user_id', $id)
                ->forceDelete();
        });

        return $this->message('删除成功');
    }

    /**
     * 批量删除管理员账号
     *
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function batchDestroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            User::whereIn('id', $request->ids)
                ->forceDelete();
            UserAuth::whereIn('user_id', $request->ids)
                ->forceDelete();
        });

        return $this->message('删除成功');
    }
}