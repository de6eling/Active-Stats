<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'Game';
    protected $fillable = array("gameid", "weather", "startTime", "endTime", "date", "Venue_idVenue", "attend", "duration", "neutralSite", "nightGame", "postSeason");
    protected $dateformat = "m/d/Y";
}
