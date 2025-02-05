<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\NewUSDTRegistrationRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\MiningRequest;
use App\Models\NewUSDTUser;
use App\Models\NewUSDTUserRefers;
use App\Models\NewUSDTTransfer;
use Illuminate\Support\Facades\Auth;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Storage;

class NewUSDTControllers extends Controller
{
    public function registerUser(NewUSDTRegistrationRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $refer_user = '';
            if(isset($request->refer_code) && !empty($request->refer_code)){
                $refer_user = NewUSDTUser::where('refer_code',$request->refer_code)->first();
                if($refer_user == null){
                    return $this->responseError('Invalid refer_code', 401);
                }
            }
            $validatedData['refer_code'] = $this->generateUniqueReferralCode($request->name);
            $validatedData['image'] = $request->image;
            $validatedData['wallet_id'] = $this->generatewalletId();
            $user = NewUSDTUser::create($validatedData);
            if($refer_user && isset($refer_user)){
                NewUSDTUserRefers::create(['from_user_id' => $refer_user->id,'to_user_id' => $user->id]);
            }
            // $user->generateAndSendOtp();
            return $this->responseSuccess(['user' => $user]);
        } catch (\Exception $e) {
                return $this->responseError($e->getMessage(), 500);
        } 
    }
    
    public function loginUser(UserLoginRequest $request)
    {
        try {
            $googleUser = NewUSDTUser::where('email',$request->email)->first();
            
            if((isset($googleUser) && !empty($googleUser)) && $googleUser->social_type == 'google'){
                return $this->responseError('google login', 401);
            }
            
            if (Auth::guard('new_usdt_users')->attempt($request->only('email', 'password'))) {
                $user = Auth::guard('new_usdt_users')->user();
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
    
    public function startMining(MiningRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $user = NewUSDTUser::where('id',$request->user_id)->update(['is_active' => $request->is_active,'mine' => $request->mine,'deactivation_time' => Carbon::now()->addHours(12)]);
            return $this->responseSuccess([],'successfully start mining');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    
    public function getReferredUser(Request $request)
    {
        try {
            $users = NewUSDTUser::with(['referredUsers.referredUser'])->find($request->user_id);
           
            if(!isset($users) && empty($users)){
                return $this->responseError('User not found', 401);
            }
            $referredUsers = $users['referredUsers'];
            // dd($referredUsers);
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
    
    public function sendTransfer(Request $request) 
    {
        try {
            $reciver_user = NewUSDTUser::where('wallet_id', $request->wallet_id)->first();
            $sender_users = NewUSDTUser::find($request->user_id);
            
            if($reciver_user == null){
                return $this->responseError('Invalid wallet_id', 401);
            }
            if($sender_users->mine < $request->mine_transfer) {
                return $this->responseError('Influence Balance', 401);
            } else if ($sender_users -> super_coin < $request->super_coin)  {
                return $this->responseError('Influence Super Coin', 401);
            }
            else {
                
                $sender_users->mine -= $request->mine_transfer;
                $sender_users->super_coin -= $request->super_coin;
                $sender_users->save();
                
                NewUSDTTransfer::create(['from_user_id' => $sender_users->id,'to_user_id' => $reciver_user->id, 'mine_transfer' => $request->mine_transfer]);
                return $this->responseSuccess("Request send successfully...");
            }
            
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }
    
    public function reciveTransfer(Request $request) 
    {
        try {
            $userId = $request->user_id;
            $user = NewUSDTUser::find($userId);
    
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
    
            $transfers = NewUSDTTransfer::where('to_user_id', $userId)
                ->with('fromUser')
                ->get();
    
            $response = $transfers->map(function ($transfer) {
                return [
                    'transaction_id' => $transfer->id,
                    'user_id' => $transfer->from_user_id,
                    'name' => $transfer->fromUser->name,
                    'email' => $transfer->fromUser->email,
                    'image' => $transfer->fromUser->image,
                    'is_active' => $transfer->fromUser->is_active,
                    'mine_transfer' => $transfer->mine_transfer,
                    'wallet_id' => $transfer->fromUser->wallet_id
                ];
            });

            return $this->responseSuccess(['transfer' => $response]);
            
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }
    
    public function collectTransfer(Request $request) 
    {
        try {
            
            // $transfer = NewUSDTTransfer::where('id', $request->transaction_id)->first();
            $transfer = NewUSDTTransfer::find($request->transaction_id);
            if(!isset($transfer) && empty($transfer)) {
                return $this->responseError('Invalid transaction_id', 401);
            }
            
            // NewUSDTUser::where('id',$users->to_user_id)->update(['mine' => $request->mine,'deactivation_time' => Carbon::now()->addHours(12)]);
            $reciver_user = NewUSDTUser::find($transfer->to_user_id);
            // $newMineValue = $reciver_user->mine + $users->mine_transfer;
            $reciver_user->update(['mine' => $reciver_user->mine + $transfer->mine_transfer]);
            
            // Delete the transfer record
            $transfer->delete();
            
            return $this->responseSuccess(['transfered' => "Usdt transfer successfully"]);
            
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }
    
    public function deleteUser($id)
    {
        try {
            $user = NewUSDTUser::find($id);
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
            $user = NewUSDTUser::where('id', $request->user_id)->first();
            if(empty($user)){
                return $this->responseError('User not found', 401);
            }
            $user->update(['image' => $request->image]);
            return $this->responseSuccess('Profile Update Succssfully!');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
      
    public function updateMiningTime(Request $request) {
        try {
            $record = NewUSDTUser::find($request->user_id);
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
    
    public function updateSuperCoin(Request $request)
    {
         try {
            $user = NewUSDTUser::where('id', $request->user_id)->first();
            if(empty($user)){
                return $this->responseError('User not found', 401);
            }
            $user->update(['super_coin' => $request->super_coin]);
            return $this->responseSuccess('Super Coin Update Succssfully!');
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
    
    public function generatewalletId($minLength = 12, $maxLength = 18) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $length = rand($minLength, $maxLength);
        $randomString = '';
    
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
    
        return $randomString;
    }

}