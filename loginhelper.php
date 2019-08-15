<?php
$configstr = file_get_contents('/home/lucan/e_config/config.json');
$config = json_decode($configstr, true);

function osuOauthCall($url, $access_token, $post=FALSE, $headers=array()) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);

    if ($post) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
    }
    else {
        $headers[] = 'Accept: application/json';
        $headers[] = 'Authorization: Bearer ' . $access_token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    return json_decode($response);
}

function getUser($refresh_token) {
    $conn = new mysqli(
        $GLOBALS["config"]["sql_server"],
        $GLOBALS["config"]["sql_user"],
        $GLOBALS["config"]["sql_pass"],
        $GLOBALS["config"]["sql_db"]
    );

    if ($conn->connect_error) {
        atEveryone();
    }

    $token = osuOauthCall("https://osu.ppy.sh/oauth/token", $refresh_token, array(
        "grant_type" => "refresh_token",
        "client_id" => $GLOBALS["config"]["osu_client_id"],
        "client_secret" => $GLOBALS["config"]["osu_client_secret"],
        "refresh_token" => $refresh_token
    ));

    $user = osuOauthCall("https://osu.ppy.sh/api/v2/me", $token->access_token);
    
    setcookie("session", $token->refresh_token, time() + 86400 * 30, "/", "justlucan.xyz", TRUE, TRUE);
    $_COOKIE["session"] = $token->refresh_token;

    $sql = "SELECT * FROM user WHERE osuId = $user->id;";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $conn->close();
        
        return $row;
    }
    else {
        $conn->close();
        atEveryone();
    }
}

function getUserPermissions ($user) {
    $conn = new mysqli(
        $GLOBALS["config"]["sql_server"],
        $GLOBALS["config"]["sql_user"],
        $GLOBALS["config"]["sql_pass"],
        $GLOBALS["config"]["sql_db"]
    );

    if ($conn->connect_error) {
        atEveryone();
    }

    $sql = "SELECT roleId FROM permission WHERE userId = " . $user["id"] . ";";
    $role = $conn->query($sql);

    if ($role->num_rows > 0) {
        $perms = array();

        while ($roleRow = $role->fetch_assoc()) {
            $sql = "SELECT * FROM role WHERE id = " . $roleRow["roleId"] . ";";
            $perm = $conn->query($sql);
            $permRow = $perm->fetch_array(MYSQLI_ASSOC);
            
            foreach ($permRow as $key => $value) {
                if ($value === "1" && $key !== "id" && $key !== "name" && $key !== "active") {
                    $perms[$key] = 1;
                }
            }
        }

        return $perms;
    }
    else {
        return array();
    }
}

function setSidebarUser ($user) {
    if ($user) {
        $sidebarUser = "<img style='float: left; object-fit: contain; width:25%; border-radius: 50%' src='" . $user["avatar"] . "'>
        <span style='float: left; margin-left: 5px;'>" . $user["osuName"] . "</span>";
        if(!$user["discordId"]) {
            $sidebarUser = $sidebarUser . "<a style='margin-left: 5px;' class='btn btn-secondary' href='https://justlucan.xyz/enigmatic/oauth/discord?action=login' role='button'>Link Discord account</a>";
        }

        return $sidebarUser;
    }
    else {
        return "<a class='btn btn-secondary' href='https://osu.ppy.sh/oauth/authorize?response_type=code&client_id=153&redirect_uri=https://justlucan.xyz/enigmatic/oauth/osu&scope=identify' role='button'>Log in with osu!</a>";
    }
}

function setSidebar ($user, $permissions) {
    $sidebarUser = setSidebarUser($user);

    $permLinks = "";

    if ($permissions["editTournament"])
        $permLinks .= "<a href='https://justlucan.xyz/enigmatic/tournaments' style='color: red' class='list-group-item list-group-item-action bg-light'>Tournaments</a>";
    if ($permissions["editRole"])
        $permLinks .= "<a href='https://justlucan.xyz/enigmatic/roles' style='color: red' class='list-group-item list-group-item-action bg-light'>Roles</a>";
    if ($permissions["editUser"])
        $permLinks .= "<a href='https://justlucan.xyz/enigmatic/users' style='color: red' class='list-group-item list-group-item-action bg-light'>Users</a>";

    return "<div class='bg-light border-right' id='sidebar-wrapper'>
        <div class='sidebar-heading'>Enigmatic Tourneys </div>
        <div class='list-group list-group-flush'>
            <a href='https://justlucan.xyz/enigmatic/' class='list-group-item list-group-item-action bg-light'>Main Page</a>
            <a href='https://justlucan.xyz/enigmatic/signup/' class='list-group-item list-group-item-action bg-light'>Sign-ups</a>
            <a href='#' class='list-group-item list-group-item-action bg-light'>Schedules</a>
            <a href='#' class='list-group-item list-group-item-action bg-light'>Mappools</a>
            <a href='#' class='list-group-item list-group-item-action bg-light'>Brackets</a>
            <a href='#' class='list-group-item list-group-item-action bg-light'>Stats</a>
            <a href='https://justlucan.xyz/enigmatic/stream/' class='list-group-item list-group-item-action bg-light'>Streams</a>
            <a href='#' class='list-group-item list-group-item-action bg-light'>Credits</a>
            $permLinks
            <div style='display: inline-block; position: absolute; bottom: 0px; width: 15rem; text-align: center; display: table-cell;' class='list-group-item list-group-item-action bg-light'>
                $sidebarUser
            </div>
        </div>
    </div>";
}

function atEveryone () {
    return "<body style='margin: 0 0 0 0; text-align: center; background-color: black'><IMG SRC='https://justlucan.xyz/enigmatic/Images/ree.gif' class='rounded mx-auto d-block' style='min-height: 100vh; '></body>";
}

function haenStop () {
    return "<body style='margin: 0 0 0 0; text-align: center; background-color: black'><IMG SRC='https://justlucan.xyz/enigmatic/Images/ree.gif' class='rounded mx-auto d-block' style='min-height: 100vh; '></body>";
}
?>