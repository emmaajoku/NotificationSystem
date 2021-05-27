<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Validators\AuthRequestValidatorTrait;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseController
{
    use AuthRequestValidatorTrait;
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request):  \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateUserSignup($request);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        try {
            DB::beginTransaction();
            $user = UserRepository::registerUser($request);
            DB::commit();
            $accessToken = $user->createToken('authToken')->accessToken;
            if ($accessToken) {
                return $this->login($request);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('an error occured : '. $e->getMessage());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function login(Request $request) : \Illuminate\Http\JsonResponse
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        $validator = $this->validateUserLogin($request);

        if (!auth()->attempt($loginData)) {
            return $this->errorResponse('invalid credentials');
        }

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;
        return  $this->successResponse(['user'=> auth()->user(), 'access_token' => $accessToken]);
    }

    /**
     * @param Request $request
     */
    public function logout (Request $request)
    {
        $accessToken = auth()->user()->token();
        $token = $request->user()->tokens->find($accessToken);
        $token->revoke();
        return response(['message' => 'You have been successfully logged out.'], 200);
    }
}
