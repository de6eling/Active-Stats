<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
  protected $table = 'Season';
    protected $fillable = ["Year"];
    public $timestamps = false;
}
