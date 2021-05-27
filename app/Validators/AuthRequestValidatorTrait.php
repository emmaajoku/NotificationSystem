<?php

namespace App\Validators;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait AuthRequestValidatorTrait
{

    private function validateUserSignup(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
        return Validator::make($request->all(), $rules);
    }

    private function validateUserLogin(Request $request)
    {
        $rules = [
            'email' => 'email|required',
            'password' => 'required'
        ];
        return Validator::make($request->all(), $rules);
    }

}
