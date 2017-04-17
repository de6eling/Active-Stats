<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team_has_Game extends Model
{
  protected $table = 'Team_has_Game';
    protected $fillable = ["Team_idTeam", "Game_idGame", "wonGame"];
    public $timestamps = false;
}
