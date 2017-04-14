<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
  protected $table = 'Stat';
    protected $fillable = ["title", "description"];
    public $timestamps = false;
}
