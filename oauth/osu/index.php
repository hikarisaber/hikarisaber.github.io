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
    unset($_COOKIE["session"]);
    setcookie("session", null, time() - 3600, "/", "justlucan.xyz", TRUE, TRUE);
    header("Location: https://osu.ppy.sh/oauth/authorize?response_type=code&client_id=153&redirect_uri=https://justlucan.xyz/enigmatic/oauth/osu&scope=identify");
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
        foreach ($user->statistics->badges as $badge) {
            $sqlBadge = "INSERT INTO badge (name, image) VALUES ($badge->description, $badge->image_url)";

            if ($conn->query($sqlBadge) !== TRUE) {
                die(atEveryone());
            }
        }
    }

    $bytes = openssl_random_pseudo_bytes(128);
    $sessionValue = bin2hex($bytes);

    setcookie("session", $sessionValue, time() + 86400 * 30, "/", "justlucan.xyz", TRUE, TRUE);
    $_COOKIE["session"] = $sessionValue;

    $sqlInsertUser = "INSERT INTO user (osuId, osuName, avatar, cover, rank, rawPP, playCount, isRanked, sessionValue, refreshToken) VALUES ($user->id, '$user->username', '$user->avatar_url', '$user->cover_url', " . $user->statistics->pp_rank . ", " . $user->statistics->pp . ", " . $user->statistics->play_count . ", " . $user->statistics->is_ranked . ", '" . $sessionValue . "', '" . $token->refresh_token . "') ON DUPLICATE KEY UPDATE sessionValue = '$sessionValue', refreshToken = '$token->refresh_token';";
    
    if($conn->query($sqlInsertUser) === TRUE) {
        $sqlSelectUser = "SELECT id FROM user WHERE osuId = $user->id";
        $result = $conn->query($sqlSelectUser);
        $row = $result->fetch_array(MYSQLI_ASSOC);

        if (isset($user->statistics->badges[0])) {
            foreach ($user->statistics->badges as $badge) {
                $sqlBadgeSelect = "SELECT id FROM badge WHERE image = $badge->image_url)";
                $badgeSelectResult = $conn->query($sqlBadge);
                $badge = $badgeSelectResult->fetch_array(MYSQLI_ASSOC);

                $sqlBadgeOwner = "INSERT INTO badgeowner (badgeId, userId) VALUES ($badge->id, $row->id)";
    
                if ($conn->query($sqlBadge) !== TRUE) {
                    die(atEveryone());
                }
            }
        }
        else {
            header("Location: https://justlucan.xyz/enigmatic/");
            exit();
        }
    }
    else if ($conn->error) {
        die($token->refresh_token);
        $conn->close();
        echo atEveryone();
    }
    else {
        $conn->close();
        header("Location: https://justlucan.xyz/enigmatic/");
        exit();
    }
}
else {
    echo haenStop();
}
?>