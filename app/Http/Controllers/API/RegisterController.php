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
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'image' => 'required',
            'role' => 'required',
            'designation' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = Hash::make($request['password']);
        if($request->file('image'))
        {
            $files = $request->file('image');
		    $destinationPath = public_path('/uploads/logo/'); // upload path
		    $fileName = date('YmdHis') . "." . $files->getClientOriginalExtension();
		    $files->move($destinationPath, $fileName);
            $input['image'] = $destinationPath.$fileName;
        }

        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['user_setails'] =  $user;
   
        return $this->sendResponse($success, 'User register successfully.');
    }
    
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    

    public function login(Request $request)
    {
        // return auth()->user();
        // print_r($request->all());die;
        if(!empty($request->all()))
        {

            $validator = Validator::make($request->all(), [
                'email' => 'required|exists:users',
                'password' => 'required',
            ]);
            // return bcrypt($request->password);
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
