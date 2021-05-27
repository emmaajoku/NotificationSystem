<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Request;

class UserRepository
{

    public static function registerUser(Request $request)
    {
        $requestData = $request->all();
        $requestData['password'] = bcrypt($request->password);
        return User::create($requestData);
    }
}
