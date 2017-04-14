<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
  protected $table = 'Player';
    protected $fillable = ["User_idUser", "number"];
    public $timestamps = false;
}
