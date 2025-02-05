<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsManagerModel extends Model
{
    // use HasFactory,Authenticatable;
    protected $table = 'AdsManager';
    protected $fillable = ['packgname', 'ads'];
}
