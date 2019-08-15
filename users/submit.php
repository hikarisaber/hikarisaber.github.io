<?php
require("../loginhelper.php");

//show error modal with $message
function errModal ($message) {
    die(
        "$('.modal-body').html(`$message`);
        $('#errorModal').modal('show');"
    );
}

if ($_COOKIE["session"]) {
    $user = getUser($_COOKIE["session"]);
    $permissions = getUserPermissions($user);

    if ($permissions["editUser"]) {
        $conn = new mysqli(
            $config["sql_ip"],
            $config["sql_user"],
            $config["sql_pass"],
            $config["sql_db"]
        );
        
        if ($conn->connect_error) {
            errModal("Couldn't connect to database");
        }

        $sql = "UPDATE role SET badgeCheckRequired = " . $_POST["badgeCheckRequired"] . " badgeCount = " . $_POST["badgeCount"] . " WHERE id = " . $_POST["id"] . ";";

        if ($conn->query($sql)) {
            echo "$('#createBtn').attr('class', 'btn btn-success').html('Updated')";
        }
        else {
            echo "$('createBtn').removeAttr('disabled');";
            errModal($conn->error);
        }
    }
    else {
        echo "$(document.body).html(`" . haenStop() . "`).attr('style', 'background-color: black');";
    }
}
else {
    echo "$(document.body).html(`" . haenStop() . "`).attr('style', 'background-color: black');";
}