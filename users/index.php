<?php
    require("../loginhelper.php");

    if ($_COOKIE["session"]) {
        $user = getUser($_COOKIE["session"]);
        $permissions = getUserPermissions($user);
    }

    if (!$permissions["editTournament"]) {
        die(haenStop());
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

    <title>Enigmatic Admin</title>
</head>
<body>
<script>
    eval(
        <?php
            $conn = new mysqli(
                $config["sql_ip"],
                $config["sql_user"],
                $config["sql_pass"],
                $config["sql_db"]
            );

            if ($conn->connect_error) {
                //shows error modal on database connection failure
                echo(
                    "`$('#signupbtn').attr('class', 'btn btn-danger').html('Error');
                    $('.modal-body').html('An error occured while connecting to our database. Please try again later.');
                    $('#errorModal').modal('show');`"
                );
            }

            $sql = "SELECT * FROM user ORDER BY badgeCheckRequired DESC";
            $result = $conn->query($sql);
        ?>
    )
</script>

<div class='modal fade' id='errorModal' role='dialog'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-body'>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
            </div>
        </div>
    </div>
</div>

<div class="d-flex" id="wrapper">
    <?php
        echo setSidebar($user, $permissions);
    ?>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <input type="text" id="searchInput" placeholder="Search osu! Usernames">

            <ul class='list-group list-group-horizontal'>
                <li class='list-group-item'>ID</li>
                <li class='list-group-item'>osu! ID</li>
                <li class='list-group-item'>Discord ID</li>
                <li class='list-group-item'>osu! Username</li>
                <li class='list-group-item'>Discord Username</li>
                <li class='list-group-item'>Rank</li>
                <li class='list-group-item'>Playcount</li>
                <li class='list-group-item'>Badge Check Required</li>
                <li class='list-group-item'>Badges</li>
                <li class='list-group-item'>Registered on</li>
                <li class='list-group-item'>Last logged in on</li>
            </ul>

            <ul id ="userList" class="list-group list-group-flush">
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<form>
                                <ul class='list-group list-group-horizontal'>
                                    <li class='list-group-item'><input name='id' type='number' class='form-control' value='" . $row["id"] . "' readonly></li>
                                    <li class='list-group-item'><input name='osuId' type='number' class='form-control' value='" . $row["osuId"] . "' readonly></li>
                                    <li class='list-group-item'><input name='discordId' type='number' class='form-control' value='" . $row["discordId"] . "' readonly></li>
                                    <li class='list-group-item'><input name='osuName' type='string' class='form-control' value='" . $row["osuName"] . "' readonly></li>
                                    <li class='list-group-item'><input name='discordName' type='string' class='form-control' value='" . $row["discordName"] . "' readonly></li>
                                    <li class='list-group-item'><input name='rank' type='number' class='form-control' value='" . $row["rank"] . "' readonly></li>
                                    <li class='list-group-item'><input name='playCount' type='number' class='form-control' value='" . $row["playCount"] . "' readonly></li>
                                    <li class='list-group-item'><input name='badgeCheckRequired' type='number' class='form-control' max='1' value='" . $row["badgeCheckRequired"] . "'></li>
                                    <li class='list-group-item'><input name='badgeCount' type='number' class='form-control' max='1' value='" . $row["badgeCount"] . "'></li>
                                    <li class='list-group-item'><input name='registered' type='string' class='form-control' value='" . $row["registered"] . "' readonly></li>
                                    <li class='list-group-item'><input name='lastLogin' type='string' class='form-control' value='" . $row["lastLogin"] . "' readonly></li>
                                    <li class='list-group-item'><button id='signupbtn' type='submit' class='btn btn-primary'>Update</button></li>
                                </ul>
                            </form>";
                        }
                    }
                ?>
            </ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#searchInput").on("keyup", function(){
            let value = $(this).val().toLowerCase();

            $("#userList ul").filter(function(){
                //it works ok
                $(this).toggle($($(this).find(':input')[3]).val().toLowerCase().indexOf(value) > -1);
            });
        });
    });

    //post form on submit button click
    $("form").submit(function(e) {
        var postData = $(this).serializeArray();
        console.log(postData)

        $("#signupbtn").html(`<div class='spinner-border spinner-border-sm text-dark' role='status'></div>`).attr("disabled", "");

        $.ajax({
            url: "submit.php",
            type: "POST",
            data: postData,
            success: function (data) {
                eval(data);
            }
        });

        e.preventDefault();
    });
</script>
</body>