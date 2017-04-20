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
use App\Team_has_Game;
use App\Coach;
use DateTime;

class uploadXML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploadXML {file} {qtr} {v team hc first name} {v team hc last name} {h team hc first name} {h team hc last name} {v won game} {h won game}';

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
          $neutralGame = 0;
          $nightGame = 0;
          $postSeasonGame = 0;
          if ($game['neutralgame'] == "Y") {
            $neutralGame = 1;
          }
          if ($game['nitegame'] == "Y") {
            $nightGame = 1;
          }
          if ($game['postseason'] == "Y") {
            $postSeasonGame = 1;
          }
          $newGame = Game::firstOrNew(
            [
              "gameid" => $game['gameid'],
              "date" => $newformat,
              "startTime" => $formattedStart,
              "endTime" => $formattedEnd,
              "duration" => $formattedDuration,
              "weather" => $game['weather'],
              "attend" => $game['attend'],
              "Venue_idVenue" => $venueId,
              "neutralSite" => $neutralGame,
              "nightGame" => $nightGame,
              "postSeason" => $postSeasonGame
            ]
          );
          $newGameId = 0;
          // Get the game id for adding other records //
          if ($newGame->save()) {
            if ($newGame->id == null) {
              $newGameId = $newGame->idGame;
              echo "Game has been found!\n";
            }
            else {
              $newGameId = $newGame->id;
              echo "Game has been added!\n";
            }
          }

          // Iterate through teams in the XML file //
          foreach($xml->team as $team) {
            // Add a team //
            $addTeam1 = Team::firstOrNew(
              [
                "Title" => $team['name'],
                "teamid" => $team['id']
              ]
            );
            $teamId = 0;
            // Get the team id for adding other records //
            if ($addTeam1->save()) {
              if ($addTeam1->id == null) {
                $teamId = $addTeam1->idTeam;
                echo $team['name']." has been found!\n";

              }
              else {
                $teamId = $addTeam1->id;
                echo $team['name']." has been added!\n";

              }
            }


            // Get winnning and home team //
            $teamWonGame = 0;
            $homeTeam = 0;

            // Initalize Head Coach for team //
            $hcFirstName = "";
            $hcLastName = "";

            // Check home/visiting team and get coach name from argument //
            if ($team['vh'] == "V") {
              if ($this->argument('v won game') == "true" || $this->argument('v won game') == 1) {
                $teamWonGame = 1;
              }
              $hcFirstName = $this->argument('v team hc first name');
              $hcLastName = $this->argument('v team hc last name');
            }
            else if ($team['vh'] == "H") {
              $homeTeam = 1;
              if ($this->argument('h won game') == "true" || $this->argument('h won game') == 1) {
                $teamWonGame = 1;
              }
              $hcFirstName = $this->argument('h team hc first name');
              $hcLastName = $this->argument('h team hc last name');
            }

            // Add team to game //
            $teamGame = Team_has_Game::firstOrNew([
              "Team_idTeam" => $teamId,
              "Game_idGame" => $newGameId,
              "wonGame" => $teamWonGame,
              "homeTeam" => $homeTeam
            ]);
            $teamGame->save();

            echo "Team_has_Game has been added!\n";

            // Save head coach //
            $headCoach = User::firstOrNew([
              "firstName" => $hcFirstName,
              "lastName" => $hcLastName
            ]);
            // Get the coach's UserId //
            $hcUserId = 0;
            if ($headCoach->save()) {
              if ($headCoach->id == null) {
                $hcUserId = $headCoach->idUser;
                echo "Head Coach has been found!\n";
              }
              else {
                $hcUserId = $headCoach->id;
                echo "Head Coach has been added!\n";
              }
            }

            // Add coach as a User //
            $newCoach = Coach::firstOrNew([
              "User_idUser" => $hcUserId,
              "title" => "Head Coach"
            ]);
            $newCoach->save();


            // Assign coach to the team //
            $coachTeam = Team_has_User::firstOrNew([
              "User_idUser" => $hcUserId,
              "Team_idTeam" => $teamId,
            ]);
            $coachTeam->save();

            // Coach is in a game //
            $coachGame = User_has_Game::firstOrNew([
              "User_idUser" => $hcUserId,
              "Game_idGame" => $newGameId,
              "Game_Venue_idVenue" => $venueId
            ]);
            $coachGame->save();

            // Iterate through tags in the team attribute //
            foreach($team->children() as $teamUser) {
              // Gets the quarter scoring for the perspective team //
              if ($teamUser->getName() == "linescore") {
                foreach ($teamUser->children() as $period) {
                  // Get the period by checking if points were scored //
                  if ($period['prd'] == $this->argument('qtr')) {
                    // Add the period //
                    $gamePeriod = Game_has_Period::firstOrNew([
                      "Game_idGame" => $newGameId,
                      "Game_Venue_idVenue" => $venueId,
                      "Period" => $period['prd']
                    ]);
                    // Get the gamePeriodId for use in adding other records //
                    $gamePeriodId = 0;
                    if ($gamePeriod->save()) {
                      if ($gamePeriod->id == null) {
                        $gamePeriodId = $gamePeriod->idGame_has_Period;
                      }
                      else {
                        $gamePeriodId = $gamePeriod->id;
                      }
                    }
                    echo "Quarter Scoring Added!\n";

                    // Create stat for quarter scoring //
                    $statName1 = Stat::firstOrNew([
                      "title" => $period['prd']." Period points"
                    ]);
                    $stat_Id = 0;
                    // Get stat id for adding other records //
                    if ($statName1->save()) {
                      if ($statName1->id == null) {
                        $stat_Id = $statName1->idStat;
                        echo "Stat has been found!\n";
                      }
                      else {
                        $stat_Id = $statName1->id;
                        echo "Stat has been added!\n";
                      }
                    }

                    // Add Team_has_Stat with created StatId //
                    $team_Stat1 = Team_has_Stat::firstOrNew([
                      "Team_idTeam" => $teamId,
                      "Stat_idStat" => $stat_Id,
                      "value" => $period['score'],
                      "Game_idPeriod" => $gamePeriodId
                    ]);
                    $team_Stat1->save();
                  }
               }
            }
            // Check if XML attribute is a player //
              if ($teamUser->getName() == "player") {
                // Get and parse player's name //
                $fullName = $teamUser['name'];
                $firstName = "";
                $lastName = "";
                // Check if first and last name exists //
                  if (sizeof(explode(',', $fullName)) > 1) {
                    $firstName = explode(',', $fullName)[1];
                    $lastName = explode(',', $fullName)[0];
                  }
                  else {
                    $firstName = $fullName;
                  }
                  // Save new user to the database //
                $user1 = User::firstOrNew([
                  "firstName" => $firstName,
                  "lastName" => $lastName
                ]);
                $user1->save();


                // Get User id for other records //
                $id = 0;
                if ($user1->save()) {
                  if ($user1->id == null) {
                    $id = $user1->idUser;
                    echo "User found!\n";
                  }
                  else {
                    $id = $user1->id;
                    echo "User added!\n";
                  }
                }

                // Add user to the team //
                $teamplayer1 = Team_has_User::firstOrNew([
                  "Team_idTeam" => $teamId,
                  "User_idUser" => $id
                ]);
                $teamplayer1->save();

                echo "Team_has_User added!\n";

                // Check if player started in the game //
                $startedGame = 0;
                if ($teamUser['gs'] != null || $teamUser['gs'] != "") {
                  $startedGame = 1;
                }

                // The user played in the game, so they are added to the User_has_Game table //
                $user1Game = User_has_Game::firstOrNew([
                  "User_idUser" => $id,
                  "Game_idGame" => $newGameId,
                  "Game_Venue_idVenue" => $venueId,
                  "startedGame" => $startedGame
                ]);
                $user1Game->save();

                echo "User_has_Game added!\n";

                // Make the user a player //
                $player1 = Player::firstOrNew([
                  "User_idUser" => $id
                ]);
                $playerId = 0;
                // Get the playerId for other records //
                if ($player1->save()) {
                  if ($player1->id == null) {
                    $playerId = $player1->idPlayer;
                    echo "Player found!\n";
                  }
                  else {
                    $playerId = $player1->id;
                    echo "Player added!\n";
                  }
                }



                foreach ($teamUser->children() as $stat) {
                  foreach($stat->attributes() as $a => $b) {
                    // Get the stats for the player //
                    $statName = Stat::firstOrNew([
                      "title" => $stat->getName()." ".$a
                    ]);
                    $statId = 0;
                    // get the statId for other records //
                    if ($statName->save()) {
                      if ($statName->id == null) {
                        $statId = $statName->idStat;
                        echo "Stat found!\n";
                      }
                      else {
                        $statId = $statName->id;
                        echo "Stat added!\n";
                      }
                    }


                    // Add the player's stats to the database //
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
              // Get the team stats //
              if ($teamUser->getName() == "totals") {
                foreach ($teamUser->children() as $teamStat) {
                  foreach($teamStat->attributes() as $a => $b) {
                    // Get the team stat //
                    $statName = Stat::firstOrNew([
                      "title" => $teamStat->getName()." ".$a
                    ]);
                    $statId = 0;
                    // Get the statId for other records //
                    if ($statName->save()) {
                      if ($statName->id == null) {
                        $statId = $statName->idStat;
                        echo "Stat found!\n";
                      }
                      else {
                        $statId = $statName->id;
                        echo "Stat added!\n";
                      }
                    }

                    // Add the stat to the team //
                    $team_Stat = Team_has_Stat::firstOrNew([
                      "Team_idTeam" => $teamId,
                      "Stat_idStat" => $statId,
                      "value" => $b,
                      "Game_idPeriod" => $gamePeriodId
                    ]);
                    $team_Stat->save();

                    echo "Team_has_Stat added!\n";
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
