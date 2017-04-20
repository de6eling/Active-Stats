<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_has_Game extends Model
{
  protected $table = 'User_has_Game';
    protected $fillable = ["User_idUser", "Game_idGame", "Game_Venue_idVenue", "startedGame"];
    public $timestamps = false;
}
