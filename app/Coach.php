<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
  protected $table = 'Coach';
    protected $fillable = ["User_idUser", "title"];
    public $timestamps = false;
}
