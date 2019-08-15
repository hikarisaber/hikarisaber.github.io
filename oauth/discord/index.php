<?php
require("../../loginhelper.php");

$client_id = $config["discord_client_id"];
$client_secret = $config["discord_client_secret"];

function apiRequest($url, $post=FALSE, $access_token=FALSE, $headers=array()) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);

    if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    
    $headers[] = 'Accept: application/json';

    if($access_token)
        $headers[] = 'Authorization: Bearer ' . $access_token;
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    return json_decode($response);
}

if ($_COOKIE["session"]) {
    if ($_GET["action"] == "login") {
        $params = array(
            "client_id" => $client_id,
            "redirect_url" => "https://justlucan.xyz/enigmatic/oauth/discord",
            "response_type" => "code",
            'scope' => "identify"
        );

        header("Location: https://discordapp.com/api/oauth2/authorize" . "?" . http_build_query($params));
        die();
    }
    else if ($_GET["code"]) {
        $conn = new mysqli(
            $config["sql_ip"],
            $config["sql_user"],
            $config["sql_pass"],
            $config["sql_db"]
        );

        if ($conn->connect_error) {
            atEveryone();
        }
        
        $token = apiRequest("https://discordapp.com/api/oauth2/token", array(
            "grant_type" => "authorization_code",
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $_GET["code"]
        ));
        
        $user = getUser($_COOKIE["session"]);
        
        $client_id = $config["discord_client_id"];
        $client_secret = $config["discord_client_secret"];

        $discordUser = apiRequest("https://discordapp.com/api/users/@me", FALSE, $token->access_token);

        $sql = "UPDATE user SET discordId = $discordUser->id, discordName =  '$discordUser->username" . "#" . $discordUser->discriminator . "' WHERE id = " . $user["id"];
        
        if ($conn->query($sql) === TRUE) {
            $conn->close();
            header('Location: ' . "https://justlucan.xyz/enigmatic/");
        }
        else {
            $conn->close();
            atEveryone();
        }
    }
    else {
        haenStop();
    }
}
else {
    haenStop();
}
?>

