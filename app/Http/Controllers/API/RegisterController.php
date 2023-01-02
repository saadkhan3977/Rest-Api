<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Hash;

class RegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return response()->json(['success'=>false,'error'=>$validator->errors()->first()]);    
        }
   
        $input = $request->except(['_token','c_password'],$request->all());
        if(isset($input)){

            $input['password'] = Hash::make($request['password']);
            if($request->image)
            {
                $files = $request->file('image');
                $destinationPath = public_path('/uploads/users/'); // upload path
                $fileName = date('YmdHis') . "." . $files->getClientOriginalExtension();
                $files->move($destinationPath, $fileName);
                $input['image'] = asset('/uploads/users/'.$fileName);
            }
            
            $user = User::create($input);
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['user_details'] =  $user;
            
        }
        return $this->sendResponse($success, 'User register successfully.');
    }

    public function login(Request $request)
    {
        if(!empty($request->all()))
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|exists:users',
                'password' => 'required',
            ]);
            if ($validator->fails()) 
            {    
                return $this->sendError('Unauthorised.', ['error'=> $validator->errors()]);
            }    
            
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
                $user = Auth::user(); 
                $success['token'] =  $user->createToken('MyApp')-> accessToken; 
                $success['user_info'] =  $user;
                
                return $this->sendResponse($success, 'User login successfully.');
            } 
            else{ 
                return $this->sendError('Unauthorised.', ['error'=> 'Password is Incorrect']);
            } 
        }
        else
        { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    
}
