<?php
if ($_POST["players"]) {
    $configstr = file_get_contents('/home/lucan/e_config/config.json');
    $config = json_decode($configstr, true);

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
        $conn->close();
        echo $GLOBALS['errButton'];
        die(
            "$('.modal-body').html('$message');
            $('#errorModal').modal('show');"
        );
    }

    //show error popover on $id with $message
    function errPopover ($message, $id) {
        $conn->close();
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
        errModal("An error occured while connecting to our database. Please try again later.");
    }

    if (!isset($_POST["tournament"]) || $_POST["tournament"] == "" || $_POST["tournament"] == "Select a tournament") {
        errPopover("Select a tournament", "tourDropdown");
    }
    else if (!isset($_POST["teamname"]) || $_POST["teamname"] == "") {
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
        errModal("Bruh");
    }

    //check if a player is entered more than once
    $playersCount = array_count_values($_POST["players"]);

    foreach ($playersCount as $player => $count) {
        if (isset($player) && $count > 1) {
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
            errPopover("Player is already signed up", "player_" . findPlayerIndex($_POST["players"], $row["osuname"]) . "_input");
        }
    }

    //create new player array to remove empty values
    $players = array_filter($_POST["players"]);
    $players = array_values($players);

    //check player eligibility
    for ($i = 0; $i < 8; $i++) {
        if (isset($players[$i])) {
            //check if player exists on osu
            $url = "https://osu.ppy.sh/api/get_user?k=" . $config["token"] . "&u=" . $players[$i] . "&type=string";
            $json = file_get_contents($url);
            $userdata = json_decode($json);

            if (!isset($userdata[0])) {
                errPopover("Player does not exist", "player_" . findPlayerIndex($_POST["players"], $players[$i]) . "_input");
            }
            //check if player is inside rank range
            else if ($userdata[0]->pp_rank < $tournament["rank_max"] || $userdata[0]->pp_rank > $tournament["rank_min"]) {
                errPopover("Player is not in rank range", "player_" . findPlayerIndex($_POST["players"], $players[$i]) . "_input");
            }

            //format player name for sql insert
            $players[$i] = "'" . $players[$i] . "'";
        }
        else {
            //format empty slots for sql insert
            $players[$i] = "DEFAULT";
        }
    }

    if (sizeof($players) < $tournament["team_size_min"]) {
        errModal("Bruh");
    }

    //insert team into signups table
    $sqlTeamInsert = "INSERT INTO signups_" . $tournament["acronym"] . " VALUES(" . "'" . $_POST["teamname"] . "', " . $players[0] . ", " . $players[1] . ", " . $players[2] . ", " . $players[3] . ", " . $players[4] . ", " . $players[5] . ", " . $players[6] . ", " . $players[7] . ")";

    if ($conn->query($sqlTeamInsert) === TRUE) {
        //insert players into user table
        $sqlUserInsert = "INSERT INTO users (osuname, signed_up) VALUES (";

        foreach($players as $player) {
            if ($player !== "DEFAULT") {
                $sqlUserInsert = $sqlUserInsert . $player . ", 1), (";
            }
        }

        $sqlUserInsert = rtrim($sqlUserInsert, ", (") . ";";

        if ($conn->query($sqlUserInsert) === TRUE) {
            //return confirmation on succesful insert and disable form
            echo "$('#signupbtn').attr('class', 'btn btn-success').html('Signed up!')
            $('#formfieldset').attr('disabled', '');";
        }
        else {
            //if this error shows up, the team has signed up but the players are not in the list of registered players
            errModal("An unknown error occured, please notify an admin");
        }
    }
    else {
        //return error popovers for duplicate key entries
        if ($conn->errno === 1062) {
            if (strpos($conn->error, "for key 'PRIMARY'") !== FALSE) {
                errPopover("Team name is already registered", "teamnameinput");
            }
            else {
                errModal("An unknown error occured, please try again later.");
            }
        }
        else {
            errModal("An unknown error occured, please try again later.");
        }
    }

    $conn->close();
}
else {
    haenStop();
}
?>