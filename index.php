<?php
    require("./loginhelper.php");

    if ($_COOKIE["session"]) {
        $user = getUser($_COOKIE["session"]);
        $permissions = getUserPermissions($user);
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
    <?php
        echo setSidebar($user, $permissions);
    ?>
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