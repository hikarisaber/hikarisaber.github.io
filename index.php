<?php
    require("./loginhelper.php");

    if ($_COOKIE["user"]) {
        $user = getUser($_COOKIE["user"]);
    }
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="index.css">
    <title>Enigmatic Tourney Soon</title>
</head>
<body>
<div class="d-flex" id="wrapper">
    <div class="bg-light border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">Enigmatic Tourneys </div>
        <div class="list-group list-group-flush">
            <a href="https://justlucan.xyz/enigmatic/" class="list-group-item list-group-item-action bg-light">Main Page</a>
            <a href="https://justlucan.xyz/enigmatic/signup/" class="list-group-item list-group-item-action bg-light">Sign-ups</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Schedules</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Mappools</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Brackets</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Stats</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Streams</a>
            <a href="#" class="list-group-item list-group-item-action bg-light">Credits</a>
            <div style="display: inline-block; position: absolute; bottom: 0px; width: 15rem; text-align: center; display: table-cell;" class="list-group-item list-group-item-action bg-light">
                <?php
                    echo setSidebarUser($user);
                ?>
            </div>
        </div>
    </div>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <h1 class="mt-2">Enigmatic Tourney RETURNS: The prequel</h1> 
            <div class="card">
                        <div class="card-header">Announcements</div>
                        <div class="card-body">NEW TOURNEY COMING SOON:TM:</div>
            </div>
            <IMG SRC="./Images/magicgif.gif" class="rounded mx-auto d-block" style="margin-top:3cm">
        </div>
    </div>
</div>
</body>