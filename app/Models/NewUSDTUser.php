<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class NewUSDTUser extends Model implements AuthenticatableContract
{
    use HasFactory,Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'new_usdt_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'mine',
        'super_coin',
        'wallet_id',
        'device_id',
        'os_version',
        'social_type',
        'otp',
        'is_verified',
        'is_active',
        'last_active',
        'refer_code',
        'deactivation_time'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'otp',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
     public function referredUsers()
    {
        return $this->hasMany(NewUSDTUserRefers::class, 'from_user_id');
    }

    // Define the relationship with referring users
    public function referringUsers()
    {
        return $this->hasMany(NewUSDTUserRefers::class, 'to_user_id');
    }

    public function sentTransfers()
    {
        return $this->hasMany(NewUSDTTransfer::class, 'from_user_id');
    }

    public function receivedTransfers()
    {
        return $this->hasMany(NewUSDTTransfer::class, 'to_user_id');
    }

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