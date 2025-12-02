<?php
// app/Models/WebVisit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebVisit extends Model
{
    protected $fillable = ['ip_address', 'user_agent', 'page_url'];
}