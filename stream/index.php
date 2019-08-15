<?php
    require("../loginhelper.php");

    if ($_COOKIE["session"]) {
        $user = getUser($_COOKIE["session"]);
        $permissions = getUserPermissions($user);
    }
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href='../css/bootstrap.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../index.css">

    <script src='../js/jquery-3.4.1.min.js'></script>
    <script src='../js/popper.min.js'></script>
    <script src='../js/bootstrap.min.js'></script>
    <script src="https://embed.twitch.tv/embed/v1.js"></script>

    <title>Enigmatic Stream</title>
</head>

<body>
<div class='d-flex' id='wrapper'>
    <?php
        echo setSidebar($user, $permissions);
    ?>

    <div id='page-content-wrapper'>
        <div class='container-fluid'>
            <div id="twitch-embed"></div>

            <script type="text/javascript">
                new Twitch.Embed("twitch-embed", {
                    width: 854,
                    height: 480,
                    channel: "enigmatic_tourneys",
                    theme: "dark"
                });
            </script>
        </div>
    </div>
</div>

<script>

</script>
</body>
</html>

