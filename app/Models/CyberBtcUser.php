<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class CyberBtcUser extends Model implements AuthenticatableContract
{
    use HasFactory,Authenticatable;
    protected $table = 'cyber_btc_users';
    protected $fillable = [
        'email',
        'password',
        'social_id',
        'social_type',
        'otp'
    ];
    protected $casts = [
        'password' => 'hashed',
    ];
    protected $hidden = [
        'password',
        'remember_token',
        'otp'
    ];
    
     public function generateAndSendOtp()
    {
        // Generate a random 6-digit OTP
        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        // Save the OTP to the user model
        $this->update(['otp' => $otp]);

        // Send the OTP via email
        Mail::to($this->email)->send(new OtpMail($otp));
    }

}
