<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRefers extends Model
{
    use HasFactory;
    protected $fillable = [
        'from_user_id',
        'to_user_id'
    ];
 public function referringUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // Define the relationship with the referred user
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
