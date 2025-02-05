<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\WatchUser;
use App\Models\WatchUserRefers;
use Illuminate\Http\Request;
use App\Http\Requests\UserRegistrationRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WatchAndEarnSimulationController extends Controller
{
    public function registerUser(Request $request)
    {
        try {
             $request->validate([
                'email' => 'required|string|email|unique:watch_and_earn_simulation_user|max:255',
                'password' => 'required|string|min:6',
            ]);
            $refer_user = '';
            if(isset($request->refer_code) && !empty($request->refer_code)){
                $refer_user = WatchUser::where('refer_code',$request->refer_code)->first();
                if($refer_user == null){
                    return $this->responseError('Invalid refer_code', 401);
                }
            }
            // dd($refer_user);
            $validatedData = $request->all();
            $validatedData['refer_code'] = $this->generateUniqueReferralCode($request->name);
            $user = WatchUser::create($validatedData);
            if($refer_user && isset($refer_user)){
                WatchUserRefers::create(['from_user_id' => $refer_user->id,'to_user_id' => $user->id]);
            }
            return $this->responseSuccess(['user' => $user]);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }

    public function loginUser(Request $request)
    {
        try {
            $credentials = request(['email', 'password']);
            if (Auth::guard('watch_and_earn_simulation_user')->attempt($credentials)) {
                $user = Auth::guard('watch_and_earn_simulation_user')->user();
                $user->deactivation_time = Carbon::parse($user->deactivation_time)->timestamp;
                return $this->responseSuccess(['user' => $user]);
            } else {
                return $this->responseError('Invalid credentials', 401);
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    public function startActivate(Request $request)
    {
        try {
            $user = WatchUser::find($request->user_id);
            
            if (!$user) {
                return $this->responseError('User not found', 401);
            }
            
            $daily_reward = $user->daily_reward + 1;
            
            if ($daily_reward == 11) {
                $daily_reward = 1;
            }
            
            $user->update([
                'is_active' => $request->is_active,
                'coin' => $request->coin, 
                'daily_reward' => $daily_reward, 
                'deactivation_time' => Carbon::now()->addDay()
            ]);
            
            $res = ['deactivation_time' => Carbon::parse($user->deactivation_time)->timestamp, 'daily_reward' => $user->daily_reward];
            
            return $this->responseSuccess($res, 'User updated successfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }

    
    public function getReferredUser(Request $request)
    {
        try {
            $users = WatchUser::with(['referredUsers.referredUser'])->find($request->user_id);
            if(!isset($users) && empty($users)){
                return $this->responseError('User not found', 401);
            }
            $referredUsers = $users['referredUsers'];
            if ($referredUsers->isEmpty()) {
                return $this->responseSuccess(['referredUsers' => $referredUsers]);
            } else {
                $allReferredUser = [];
                foreach ($referredUsers as $key => $user) {
                   $allReferredUser[] = ['id' => $user->referredUser->id,'name' => $user->referredUser->name,'email' => $user->referredUser->email,'is_active' => $user->referredUser->is_active,'image' => $user->referredUser->image];
                }
                return $this->responseSuccess(['referredUsers' => $allReferredUser]);
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    public function deleteUser($id)
    {
        try {
            $user = WatchUser::find($id);
            if(!isset($user) && empty($user)){
                return $this->responseError('User not found', 401);
            }
            
            $user->delete();
            return $this->responseSuccess([],'user deleted succssfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    public function updateProfile(Request $request)
    {
         try {
            $user = WatchUser::where('id', $request->user_id)->first();
            if(empty($user)){
                return $this->responseError('User not found', 401);
            }
            $user->update(['image' => $request->image]);
            return $this->responseSuccess('Profile Update Succssfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    public function updateCoin(Request $request) {
        try {
             $user = WatchUser::where('id', $request->user_id)->first();
             if(empty($user)){
                return $this->responseError('User not found', 401);
            }
            $updateCoin = $user->coin - $request->coin;
            $user->update(['coin' => $updateCoin]);
            return $this->responseSuccess('Coin Update Succssfully!');
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
       
     public function deleteAccount(Request $request)
    {
        try {
            $user = WatchUser::where('email',$request->email)->first();
            if(!isset($user) && empty($user)){
                return $this->responseError('User not found', 401);
            }
            
            $user->delete();
            return $this->responseSuccess([],'user deleted succssfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
}