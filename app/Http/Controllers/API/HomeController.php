<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Auth;
use HasApiTokens;


class HomeController extends BaseController
{
    public function logout(Request $request)
    {
        // Auth::logout();
        $user = Auth::user()->token();
        $user->revoke();
        return $this->sendResponse('Success.', ['success'=>'Logout Successfully']);
 
    }
}
