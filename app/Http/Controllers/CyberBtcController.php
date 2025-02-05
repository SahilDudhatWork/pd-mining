<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\CyberBtcUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class CyberBtcController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        // return $this->responseSuccess(['user' => []]);

        try {
            $user = Socialite::driver('google')->stateless()->user();
            $finduser = CyberBtcUser::where('social_id', $user->id)->first();

            if ($finduser)  // if user found then do this
            {
                return $this->responseSuccess(['user' => $finduser]);
            }
            // $token = $user->token;
            // $refreshToken = $user->refreshToken;
            // $expiresIn = $user->expiresIn;

            // Your logic to authenticate or register the user here
            // For example, create a new user if not exists, or log in the user
            // $response = [
            //     'user' => $user,
            //     'access_token' => $token,
            //     'refresh_token' => $refreshToken,
            //     'expires_in' => $expiresIn,
            // ];

            $data = CyberBtcUser::create([
                'email' => $user->email,
                'social_id' => $user->id,
                'social_type' => 'google',  
                'password' => 'my-google',
            ]);
            return $this->responseSuccess(['user' => $data]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Google authentication failed.'], 401);
        }
    }

    public function registerUser(Request $request)
    {
        try {
             $request->validate([
                'email' => 'required|string|email|unique:cyber_btc_users|max:255',
                'password' => 'required|string|min:6',
            ]);
            $user = CyberBtcUser::create($request->all());
            return $this->responseSuccess(['user' => $user]);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
       
    }

    public function loginUser(Request $request)
    {
        try {
            $credentials = request(['email', 'password']);
            if (Auth::guard('cyber_btc_user')->attempt($credentials)) {
                $user = Auth::guard('cyber_btc_user')->user();
                return $this->responseSuccess(['user' => $user]);
            } else {
                return $this->responseError('Invalid credentials', 401);
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
    
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required',
            ]);
            $user = CyberBtcUser::where('email',$request->email)->first();
            if($user){
                $user->generateAndSendOtp();
                return $this->responseSuccess([],'Please check your mail!');
            } else {
                return $this->responseError('User Not Found!', 401);
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
       
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required',
                'otp'  => 'required',
                'new_password' => 'required|string|min:6',
            ]);
            $user = CyberBtcUser::where('email', $request->email)->first();
            if ($user && $user->otp == $request->otp) {
                $user->update(['password' => $request->new_password,'otp' => null]);
                return $this->responseSuccess([],'Password changed successfully!');
            }
            return $this->responseError($user ? 'Invalid OTP' : 'User not found!', 401);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }
    }
}
