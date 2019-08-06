

<?php
require("../loginhelper.php");

if ($_POST["players"]) {
    $conn = new mysqli(
        $config["sql_ip"],
        $config["sql_user"],
        $config["sql_pass"],
        $config["sql_db"]
    );
    
    //disable error button on error to prevent spamming
    $GLOBALS['errButton'] = "$('#signupbtn').attr('class', 'btn btn-danger').html('Error').attr('disabled', '');
    setTimeout(function()" . "{" . "$('#signupbtn').removeAttr('disabled').attr('class', 'btn btn-primary').html('Sign up')}, 5000);";

    //show error modal with $message
    function errModal ($message) {
        echo $GLOBALS['errButton'];
        die(
            "$('.modal-body').html('$message');
            $('#errorModal').modal('show');"
        );
    }

    //show error popover on $id with $message
    function errPopover ($message, $id) {
        echo $GLOBALS['errButton'];
        die("$('#$id').popover({content: '$message', placement: 'right', trigger: 'manual'}).popover('show');");
    }

    //find index of $player in $players
    function findPlayerIndex ($players, $player) {
        foreach ($_POST["players"] as $index => $value) {
            if ($value === $player) {
                return $index;
            }
        }
    }

    if ($conn->connect_error) {
        $conn->close();
        errModal("An error occured while connecting to our database. Please try again later.");
    }
    
    if (!isset($_POST["tournament"]) || $_POST["tournament"] == "" || $_POST["tournament"] == "Select a tournament") {
        $conn->close();
        errPopover("Select a tournament", "tourDropdown");
    }
    else if (!isset($_POST["teamname"]) || $_POST["teamname"] == "") {
        $conn->close();
        errModal("Bruh");
    }

    //find tournament data
    $sqlTours = "SELECT * FROM tournaments";
    $resultTours = $conn->query($sqlTours);

    if ($resultTours->num_rows > 0) {
        while ($row = $resultTours->fetch_assoc()) {
            if ($row["acronym"] === substr($_POST["tournament"], 0, 3)) {
                $tournament = $row;
                break;
            }
        }
    }

    if (!isset($tournament)) {
        $conn->close();
        errModal("Bruh");
    }

    //check if a player is entered more than once
    $playersCount = array_count_values($_POST["players"]);

    foreach ($playersCount as $player => $count) {
        if (isset($player) && $count > 1) {
            $conn->close();
            errPopover("Duplicate player", "player_" . findPlayerIndex($_POST["players"], $player) . "_input");
        }
    }

    //check if players are already signed up
    $sqlUserCheck = "SELECT * FROM users WHERE osuname IN (";
    foreach($_POST["players"] as $player) {
        if (isset($player) && $player !== "") {
            $sqlUserCheck = $sqlUserCheck . "'" . $player . "', ";
        }
    }

    $sqlUserCheck = rtrim($sqlUserCheck, ", ") . ") AND signed_up = 1;";
    $userCheckResult = $conn->query($sqlUserCheck);

    if ($userCheckResult->num_rows > 0) {
        while ($row = $userCheckResult->fetch_assoc()) {
            $conn->close();
            errPopover("Player is already signed up", "player_" . findPlayerIndex($_POST["players"], $row["osuname"]) . "_input");
        }
    }

    //create new player array to remove empty values
    $players = array_filter($_POST["players"]);
    $players = array_values($players);
    $playerids = array();

    //check player eligibility
    for ($i = 0; $i < 8; $i++) {
        if (isset($players[$i])) {
            //check if player exists on osu
            $url = "https://osu.ppy.sh/api/get_user?k=" . $config["token"] . "&u=" . $players[$i] . "&type=string";
            $json = file_get_contents($url);
            $userdata = json_decode($json);

            if (!isset($userdata[0])) {
                $conn->close();
                errPopover("Player does not exist", "player_" . findPlayerIndex($_POST["players"], $players[$i]) . "_input");
            }
            //check if player is inside rank range
            else if ($userdata[0]->pp_rank < $tournament["rank_max"] || $userdata[0]->pp_rank > $tournament["rank_min"]) {
                $conn->close();
                errPopover("Player is not in rank range", "player_" . findPlayerIndex($_POST["players"], $players[$i]) . "_input");
            }

            //format player name for sql insert
            $players[$i] = "'" . $players[$i] . "'";
            $playerids[$i] = $userdata[0]->user_id;
        }
        else {
            //format empty slots for sql insert
            $players[$i] = "DEFAULT";
        }
    }

    if (sizeof($players) < $tournament["team_size_min"]) {
        $conn->close();
        errModal("Bruh");
    }

    //insert team into signups table
    $sqlTeamInsert = "INSERT INTO signups_" . $tournament["acronym"] . " VALUES(" . "'" . $_POST["teamname"] . "', " . $players[0] . ", " . $players[1] . ", " . $players[2] . ", " . $players[3] . ", " . $players[4] . ", " . $players[5] . ", " . $players[6] . ", " . $players[7] . ")";

    if ($conn->query($sqlTeamInsert) === TRUE) {
        //insert players into user table
        $sqlUserInsert = "INSERT INTO users (osuname, signed_up) VALUES (";

        for ($i = 0; $i < 8; $i++) {
            if ($players[$i] !== "DEFAULT") {
                $sqlUserInsert = "INSERT INTO users (osu_id, osu_user, signed_up) VALUES (" . $playerids[$i] . ", " . $players[$i] . ", 1) ON DUPLICATE KEY UPDATE signed_up = 1";

                if ($conn->query($sqlUserInsert) === FALSE) {
                    //if this error shows up, the team has signed up but not all players are in the list of registered players
                    $conn->close();
                    echo($sqlUserInsert);
                    errModal("An unknown error occured, please notify an admin");
                }
            }
        }

        echo "$('#signupbtn').attr('class', 'btn btn-success').html('Signed up!')
        $('#formfieldset').attr('disabled', '');";
    }
    else {
        //return error popovers for duplicate key entries
        if ($conn->errno === 1062) {
            if (strpos($conn->error, "for key 'PRIMARY'") !== FALSE) {
                $conn->close();
                errPopover("Team name is already registered", "teamnameinput");
            }
            else {
                $conn->close();
                errModal("An unknown error occured, please try again later.");
            }
        }
        else {
            $conn->close();
            errModal("An unknown error occured, please try again later.");
        }
    }

    $conn->close();
}
else {
    haenStop();
}
?>