<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Game;
use App\Venue;
use App\Team;
use App\User;
use App\Team_has_User;
use App\User_has_Game;
use App\Player;
use App\Game_has_Period;
use App\Stat;
use App\Player_has_Stat;
use App\Team_has_Stat;
use DateTime;

class uploadXML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploadXML {file}';

    /**
     * The consoluse App\User;
ew command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      // Load XML file //
        $xml = simplexml_load_file($this->argument('file')) or die("Error: Cannot create object");
        // Get the venue and game information //
          $game = $xml->venue[0];

          // Add the venue information //
          $venue = Venue::firstOrNew([
            "location" => $game['location'],
            "stadium" => $game['stadium']
          ]);
          $venueId = 0;
          // Get the venue id for adding other records //
          if ($venue->save()) {
            if ($venue->id == null) {
              $venueId = $venue->idVenue;
            }
            else {
              $venueId = $venue->id;
            }
          }

          // Set the game start and end date and time //
          $formattedStart = date('Y-m-d h:i:s', strtotime($game['date'].' '.$game['start']));
          $formattedEnd = date('Y-m-d h:i:s', strtotime($game['date'].' '.$game['end']));
          $formattedDuration = $game['duration'];
          $time = strtotime($game['date']);
          $newformat = date('Y-m-d',$time);
          $newGame = Game::firstOrNew(
            [
              "gameid" => $game['gameid'],
              "date" => $newformat,
              "startTime" => $formattedStart,
              "endTime" => $formattedEnd,
              "duration" => $formattedDuration,
              "weather" => $game['weather'],
              "attend" => $game['attend'],
              "Venue_idVenue" => $venueId
            ]
          );
          $newGameId = 0;
          // Get the game id for adding other records //
          if ($newGame->save()) {
            if ($newGame->id == null) {
              $newGameId = $newGame->idGame;
            }
            else {
              $newGameId = $newGame->id;
            }
          }

          // Iterate through teams in the XML file //
          foreach($xml->team as $team) {
            // Add a team //
            $addTeam1 = Team::firstOrNew(
              [
                "Title" => $team['name'],
                "teamid" => $team['id'],
                "abb" => $team['abb']
              ]
            );
            $teamId = 0;
            // Get the team id for adding other records //
            if ($addTeam1->save()) {
              if ($addTeam1->id == null) {
                $teamId = $addTeam1->idTeam;
              }
              else {
                $teamId = $addTeam1->id;
              }
            }

            // Iterate through tags in the team attribute //
            foreach($team->children() as $teamUser) {
              // Gets the quarter scoring for the perspective team //
              if ($teamUser->getName() == "linescore") {
                foreach ($teamUser->children() as $period) {
                  // Get the period by checking if points were scored //
                  if ($period['score'] > 0) {
                    // Add the period //
                    $gamePeriod = Game_has_Period::firstOrNew([
                      "Game_idGame" => $newGameId,
                      "Game_Venue_idVenue" => $venueId,
                      "Period" => $period['prd']
                    ]);
                    $gamePeriodId = 0;
                    if ($gamePeriod->save()) {
                      if ($gamePeriod->id == null) {
                        $gamePeriodId = $gamePeriod->idGame_has_Period;
                      }
                      else {
                        $gamePeriodId = $gamePeriod->id;
                      }
                    }

                    // Create stat for quarter scoring //
                    $statName1 = Stat::firstOrNew([
                      "title" => $period['prd']." ".$period['score']
                    ]);
                    $stat_Id = 0;

                    if ($statName1->save()) {
                      if ($statName1->id == null) {
                        $stat_Id = $statName1->idStat;
                      }
                      else {
                        $stat_Id = $statName1->id;
                      }
                    }

                    $team_Stat1 = Team_has_Stat::firstOrNew([
                      "Team_idTeam" => $teamId,
                      "Stat_idStat" => $stat_Id,
                      "value" => $period['score'],
                      "Game_idPeriod" => $gamePeriodId
                    ]);
                    $team_Stat1->save();
                    echo "Quarter Scoring Added!\n";
                  }
               }
            }
              if ($teamUser->getName() == "player") {
                $fullName = $teamUser['name'];
                $firstName = "";
                $lastName = "";
                  if (sizeof(explode(',', $fullName)) > 1) {
                    $firstName = explode(',', $fullName)[1];
                    $lastName = explode(',', $fullName)[0];
                  }
                  else {
                    $firstName = $fullName;
                  }
                $user1 = User::firstOrNew([
                  "firstName" => $firstName,
                  "lastName" => $lastName
                ]);
                $user1->save();

                echo "User added!\n";

                $id = 0;
                if ($user1->save()) {
                  if ($user1->id == null) {
                    $id = $user1->idUser;
                  }
                  else {
                    $id = $user1->id;
                  }
                }

                $teamplayer1 = Team_has_User::firstOrNew([
                  "Team_idTeam" => $addTeam1->idTeam,
                  "User_idUser" => $id
                ]);
                $teamplayer1->save();

                echo "Team_has_User added!\n";

                $user1Game = User_has_Game::firstOrNew([
                  "User_idUser" => $id,
                  "Game_idGame" => $newGameId,
                  "Game_Venue_idVenue" => $venueId
                ]);
                $user1Game->save();

                echo "User_has_Game added!\n";

                $player1 = Player::firstOrNew([
                  "User_idUser" => $id
                ]);
                $playerId = 0;
                if ($player1->save()) {
                  if ($player1->id == null) {
                    $playerId = $player1->idPlayer;
                  }
                  else {
                    $playerId = $player1->id;
                  }
                }

                echo "Player added!\n";

                foreach ($teamUser->children() as $stat) {
                  foreach($stat->attributes() as $a => $b) {
                    $statName = Stat::firstOrNew([
                      "title" => $stat->getName()." ".$a
                    ]);
                    $statId = 0;

                    if ($statName->save()) {
                      if ($statName->id == null) {
                        $statId = $statName->idStat;
                      }
                      else {
                        $statId = $statName->id;
                      }
                    }

                    echo "Stat added!\n";

                    $playerStat = Player_has_Stat::firstOrNew([
                      "Player_idPlayer" => $playerId,
                      "Player_User_idUser" => $id,
                      "Stat_idStat" => $statId,
                      "Game_idPeriod" => $gamePeriodId,
                      "value" => $b
                    ]);
                    $playerStat->save();
                    echo "Player_has_Stat added!\n";
                  }
                }
              }
              if ($teamUser->getName() == "totals") {
                foreach ($teamUser->children() as $teamStat) {
                  foreach($teamStat->attributes() as $a => $b) {
                    $statName = Stat::firstOrNew([
                      "title" => $teamStat->getName()." ".$a
                    ]);
                    $statId = 0;

                    if ($statName->save()) {
                      if ($statName->id == null) {
                        $statId = $statName->idStat;
                      }
                      else {
                        $statId = $statName->id;
                      }
                    }

                    $team_Stat = Team_has_Stat::firstOrNew([
                      "Team_idTeam" => $teamId,
                      "Stat_idStat" => $statId,
                      "value" => $b,
                      "Game_idPeriod" => $gamePeriodId
                    ]);
                    $team_Stat->save();
                }
              }
            }
          }
        }

          // Test code
          /*foreach($xml->children() as $game) {
            if ($game->getName() == "venue")
            foreach($game->attributes() as $a => $b) {
              echo $a,'="',$b,"\"\n";
            }
            foreach($game->children() as $team) {
              echo "-------",$team->getName(),"-------\n";
              foreach($team->attributes() as $c => $d) {
                echo $c,'="',$d,"\"\n";
              }
              foreach($team->children() as $stats) {
                echo '-------',$stats->getName(),"-------\n";
                foreach($stats->attributes() as $e => $f) {
                  echo $e,'="',$f,"\"\n";
                }
              }
            }
          }*/
    }
}
