<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewUSDTTransfer extends Model
{
    use HasFactory;

    protected $table = 'new_usdt_transfer';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'mine_transfer'
    ];

    
    public function fromUser()
    {
        return $this->belongsTo(NewUSDTUser::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(NewUSDTUser::class, 'to_user_id');
    }
}
