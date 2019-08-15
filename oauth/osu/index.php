<?php
require("../../loginhelper.php");

//osu oauth client
$client_id = $config["osu_client_id"];
$client_secret = $config["osu_client_secret"];

$conn = new mysqli(
    $config["sql_ip"],
    $config["sql_user"],
    $config["sql_pass"],
    $config["sql_db"]
);

if ($conn->connect_error) {
    atEveryone();
}

if ($_COOKIE["session"]) {
    header("Location: https://justlucan.xyz/enigmatic/");
}
else if ($_GET["code"]) {
    $token = osuOauthCall("https://osu.ppy.sh/oauth/token", FALSE, array(
        "grant_type" => "authorization_code",
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "redirect_uri" => "https://justlucan.xyz/enigmatic/oauth/osu",
        "code" => $_GET["code"]
    ));
    
    $user = osuOauthCall("https://osu.ppy.sh/api/v2/me", $token->access_token);
    
    if (isset($user->statistics->badges[0])) {
        $hasBadge = ", 1";
    }
    else {
        $hasBadge = ", 0";
    }

    setcookie("session", $token->refresh_token, time() + 86400 * 30, "/", "justlucan.xyz", TRUE, TRUE);
    $_COOKIE["session"] = $token->refresh_token;

    $sql = "INSERT IGNORE INTO user (osuId, osuName, avatar, cover, rank, rawPP, playCount, hasBadge, isRanked) VALUES ($user->id, '$user->username', '$user->avatar_url', '$user->cover_url', " . $user->statistics->pp_rank . ", " . $user->statistics->pp . ", " . $user->statistics->play_count . $hasBadge . ", " . $user->statistics->is_ranked . ");";

    if($conn->query($sql) === TRUE) {
        $sql = "SELECT id FROM user WHERE osuId = $user->id";
        $result = $conn->query($sql);
        $row = $result->fetch_array(MYSQLI_ASSOC);

        $sql = "INSERT INTO permission (userId, roleId) VALUES (" . $row["id"] . ", 1)";
        
        if ($conn->query($sql)) {
            $conn->close();
            header("Location: https://justlucan.xyz/enigmatic/");
        }
        else {
            atEveryone();
        }
    }
    else if ($conn->error) {
        $conn->close();
        atEveryone();
    }
    else {
        $conn->close();
        header("Location: https://justlucan.xyz/enigmatic/");
    }
}
else {
    haenStop();
}
?>