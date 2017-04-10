<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $table = 'Venue';
    protected $fillable = array("location", "stadium");
    public $timestamps = false;
}
