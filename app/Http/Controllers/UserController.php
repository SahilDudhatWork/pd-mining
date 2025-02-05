<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\GoogleRegistrationRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\MiningRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Models\UserRefers;
use Illuminate\Support\Facades\Auth;
use App\Mail\OtpMail;
use Carbon\Carbon;


class UserController extends Controller
{
    public function registerUser(UserRegistrationRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $refer_user = '';
            if(isset($request->refer_code) && !empty($request->refer_code)){
                $refer_user = User::where('refer_code',$request->refer_code)->first();
                if($refer_user == null){
                    return $this->responseError('Invalid refer_code', 401);
                }
            }
            $validatedData['refer_code'] = $this->generateUniqueReferralCode($request->name);
            $user = User::create($validatedData);
            if($refer_user && isset($refer_user)){
                UserRefers::create(['from_user_id' => $refer_user->id,'to_user_id' => $user->id]);
            }
            // $user->generateAndSendOtp();
            return $this->responseSuccess(['user' => $user]);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }
    public function registerWithGoogle(GoogleRegistrationRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $googleUser = User::where('email',$validatedData['email'])->first();
            
            if(isset($googleUser) && !empty($googleUser)){
                return $this->responseSuccess(['user' => $googleUser]);
            }
            
            $refer_user = '';
            if(isset($request->refer_code) && !empty($request->refer_code)){
                $refer_user = User::where('refer_code',$request->refer_code)->first();
                if($refer_user == null){
                    return $this->responseError('Invalid refer_code', 401);
                }
            }
            $validatedData['refer_code'] = $this->generateUniqueReferralCode($request->name);
            $validatedData['is_verified'] = 1;
            $user = User::create($validatedData);
            if($refer_user && isset($refer_user)){
                UserRefers::create(['from_user_id' => $refer_user->id,'to_user_id' => $user->id]);
            }
            if(!isset($user) && empty($user)){
                return $this->responseError('Something went wrong!', 401);
            }
            $getUser = User::find($user->id);
            return $this->responseSuccess(['user' => $getUser]);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }

   public function loginUser(UserLoginRequest $request)
    {
        try {
            $googleUser = User::where('email',$request->email)->first();
            
            if((isset($googleUser) && !empty($googleUser)) && $googleUser->social_type == 'google'){
                return $this->responseError('google login', 401);
            }
            
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                // if($user->is_verified == 0){
                //     return $this->responseError('Please verify your email address', 401);
                // }
                return $this->responseSuccess(['user' => $user]);
            } else {
                return $this->responseError('Invalid credentials', 401);
            }
        } catch (\Exception $e) {
            dd($e);
            return $this->responseError($e->getMessage(), 500);
        }
    }
    public function deleteUser($id)
    {
        try {
            $user = User::find($id);
            if(!isset($user) && empty($user)){
                return $this->responseError('User not found', 401);
            }
            
            $user->delete();
            return $this->responseSuccess([],'user deleted succssfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    

    public function getReferredUser(Request $request)
    {
        try {
            $users = User::with(['referredUsers.referredUser'])->find($request->user_id);
            if(!isset($users) && empty($users)){
                return $this->responseError('User not found', 401);
            }
            $referredUsers = $users['referredUsers'];
            if ($referredUsers->isEmpty()) {
                return $this->responseSuccess(['referredUsers' => $referredUsers]);
            } else {
                $allReferredUser = [];
                foreach ($referredUsers as $key => $user) {
                   $allReferredUser[] = ['id' => $user->referredUser->id,'name' => $user->referredUser->name,'email' => $user->referredUser->email,'is_active' => $user->referredUser->is_active];
                }
                return $this->responseSuccess(['referredUsers' => $allReferredUser]);
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    public function startMining(MiningRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $user = User::where('id',$request->user_id)->update(['is_active' => $request->is_active,'mine' => $request->mine,'deactivation_time' => Carbon::now()->addDay()]);
            return $this->responseSuccess([],'successfully start mining');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            if ($user && $user->otp == $request->otp) {
                $user->update(['is_verified' => 1,'otp' => null]);
                return $this->responseSuccess();
            }
            return $this->responseError($user ? 'Invalid OTP' : 'User not found!', 401);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }

    public function generateUniqueReferralCode($name)
    {
        $namePart = strtolower(str_replace(' ', '', $name));
        $uniquePart = substr(md5(uniqid()), 0, 4); // You can adjust the length as needed

        return $namePart . '@' . $uniquePart;
    }
    
}
