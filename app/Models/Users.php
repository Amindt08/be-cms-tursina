<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Users extends Model
{
  use HasApiTokens;
  use HasFactory;
  protected $table = 'users';
  protected $fillable = [
    'name',
    'email',
    'role',
    'password',
    'status'
  ];
}
