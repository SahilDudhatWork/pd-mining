<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\GoogleRegistrationRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\MiningRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\DhruvUsdcUser;
use App\Models\DhruvUsdcUserRefers;
use Illuminate\Support\Facades\Auth;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Storage;

class DhruvUsdcController extends Controller
{
    public function registerUser(UserRegistrationRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $refer_user = '';
            if(isset($request->refer_code) && !empty($request->refer_code)){
                $refer_user = DhruvUsdcUser::where('refer_code',$request->refer_code)->first();
                if($refer_user == null){
                    return $this->responseError('Invalid refer_code', 401);
                }
            }
            $validatedData['refer_code'] = $this->generateUniqueReferralCode($request->name);
            $validatedData['image'] = $request->image;
            $user = DhruvUsdcUser::create($validatedData);
            if($refer_user && isset($refer_user)){
                DhruvUsdcUserRefers::create(['from_user_id' => $refer_user->id,'to_user_id' => $user->id]);
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
            $googleUser = DhruvUsdcUser::where('email',$validatedData['email'])->first();
            
            if(isset($googleUser) && !empty($googleUser)){
                return $this->responseSuccess(['user' => $googleUser]);
            }
            
            $refer_user = '';
            if(isset($request->refer_code) && !empty($request->refer_code)){
                $refer_user = DhruvUsdcUser::where('refer_code',$request->refer_code)->first();
                if($refer_user == null){
                    return $this->responseError('Invalid refer_code', 401);
                }
            }
            $validatedData['refer_code'] = $this->generateUniqueReferralCode($request->name);
            $validatedData['is_verified'] = 1;
            $validatedData['image'] = $request->image;
            $user = DhruvUsdcUser::create($validatedData);
            if($refer_user && isset($refer_user)){
                DhruvUsdcUserRefers::create(['from_user_id' => $refer_user->id,'to_user_id' => $user->id]);
            }
            if(!isset($user) && empty($user)){
                return $this->responseError('Something went wrong!', 401);
            }
            $getUser = DhruvUsdcUser::find($user->id);
            return $this->responseSuccess(['user' => $getUser]);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }

   public function loginUser(UserLoginRequest $request)
    {
        try {
            $googleUser = DhruvUsdcUser::where('email',$request->email)->first();
            
            if((isset($googleUser) && !empty($googleUser)) && $googleUser->social_type == 'google'){
                return $this->responseError('google login', 401);
            }
            
            if (Auth::guard('dhruv_usdc_user')->attempt($request->only('email', 'password'))) {
                $user = Auth::guard('dhruv_usdc_user')->user();
                // if($user->is_verified == 0){
                //     return $this->responseError('Please verify your email address', 401);
                // }
                return $this->responseSuccess(['user' => $user]);
            } else {
                return $this->responseError('Invalid credentials', 401);
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    public function deleteUser($id)
    {
        try {
            $user = DhruvUsdcUser::find($id);
            if(!isset($user) && empty($user)){
                return $this->responseError('User not found', 401);
            }
            
            $user->delete();
            return $this->responseSuccess([],'user deleted succssfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
     public function deleteAccount(Request $request)
    {
        try {
            $user = DhruvUsdcUser::where('email',$request->email)->first();
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
            $users = DhruvUsdcUser::with(['referredUsers.referredUser'])->find($request->user_id);
            if(!isset($users) && empty($users)){
                return $this->responseError('User not found', 401);
            }
            $referredUsers = $users['referredUsers'];
            if ($referredUsers->isEmpty()) {
                return $this->responseSuccess(['referredUsers' => $referredUsers]);
            } else {
                $allReferredUser = [];
                foreach ($referredUsers as $key => $user) {
                   $allReferredUser[] = ['id' => $user->referredUser->id,'image' => $user->referredUser->image,'name' => $user->referredUser->name,'email' => $user->referredUser->email,'is_active' => $user->referredUser->is_active];
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
            $user = DhruvUsdcUser::where('id',$request->user_id)->update(['is_active' => $request->is_active,'mine' => $request->mine,'deactivation_time' => Carbon::now()->addHours(12)]);
            return $this->responseSuccess([],'successfully start mining');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    public function updateMiningTime(Request $request) {
        try {
            $record = DhruvUsdcUser::find($request->user_id);
            if($record->is_active) {
                $record->update(['deactivation_time' => Carbon::parse($record->deactivation_time)->addHours(2)]);
                return $this->responseSuccess('Time Updated');
            }else {
                return $this->responseError('First Start Mining', 400);
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    } 
    
    public function updateProfile(Request $request)
    {

         try {
            $user = DhruvUsdcUser::where('id', $request->user_id)->first();
            if(empty($user)){
                return $this->responseError('User not found', 401);
            }
            $user->update(['image' => $request->image]);
            return $this->responseSuccess('Profile Update Succssfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $user = DhruvUsdcUser::where('email', $request->email)->first();
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
