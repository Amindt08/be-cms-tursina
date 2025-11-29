<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_code',
        'name',
        'address',
        'no_wa',
        'outlet',
        'outlet_id',
        'points',
        'total_points_earned',
        'total_points_redeemed'
    ];

    protected $attributes = [
        'points' => 0,
        'total_points_earned' => 0,
        'total_points_redeemed' => 0,
    ];

    public function outletRelation()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
}
