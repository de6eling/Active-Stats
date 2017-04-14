<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game_has_Period extends Model
{
  protected $table = 'Game_has_Period';
    protected $fillable = ["Game_idGame", "Game_Venue_idVenue", "Period"];
    public $timestamps = false;
}
