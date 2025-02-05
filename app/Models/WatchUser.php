<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class WatchUser extends Model implements AuthenticatableContract
{
    use HasFactory,Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'watch_and_earn_simulation_user';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'coin',
        'daily_reward',
        'collect_reward_time',
        'device_id',
        'os_version',
        'social_type',
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
        return $this->hasMany(WatchUserRefers::class, 'from_user_id');
    }

    // Define the relationship with referring users
    public function referringUsers()
    {
        return $this->hasMany(WatchUserRefers::class, 'to_user_id');
    }
}