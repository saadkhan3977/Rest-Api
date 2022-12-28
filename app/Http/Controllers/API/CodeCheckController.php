<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResetCodePassword;
use App\Models\User;
use Validator;
class CodeCheckController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:6',
        ]);
        
        
        // return $request->code;
        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);
        // return $passwordReset;
        // check if it does not expired: the time is one hour
        if($passwordReset != null){

            if ($passwordReset->created_at > now()->addHour()) {
                $passwordReset->delete();
                return response()->json(['message' => trans('passwords.code_is_expire')], 422);
            }
        }
        else
        {
            return response()->json(['error' => $validator->errors()], 422);
        }
        // find user's email 
        $user = User::firstWhere('email', $passwordReset->email);
        
        // update user password
        $user->update($request->only('password'));
        
        // delete current code 
        ResetCodePassword::Where('code', $passwordReset->email)->delete();

        return response(['message' =>'password has been successfully reset'], 200);
    }
}
