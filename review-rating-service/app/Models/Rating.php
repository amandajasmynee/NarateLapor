<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = ['report_id', 'supervisor_id', 'rating', 'comment'];
}
