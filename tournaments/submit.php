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
    $checkUser = getUser($_COOKIE["session"]);
    $user = checkUserLogin($checkUser, $_COOKIE["session"]);
    $permissions = getUserPermissions($user);

    if ($permissions["editTournament"]) {
        $conn = new mysqli(
            $config["sql_ip"],
            $config["sql_user"],
            $config["sql_pass"],
            $config["sql_db"]
        );

        if ($conn->connect_error) {
            errModal("Couldn't connect to database");
        }
        
        if ($_POST["type"] === "create") {
            $columns = "";
            $values = "";

            foreach ($_POST as $key => $value) {
                if ($key !== "type") {
                    $columns .= "$key, ";
                    if (is_numeric($value)) {
                        $values .= "$value, ";
                    }
                    else {
                        $values .= "'$value', ";
                    }
                }
            }

            $sql = "INSERT INTO tournament (" . rtrim($columns, ", ") . ") VALUES (" . rtrim($values, ", ") . ");";

            if ($conn->query($sql)) {
                echo "$('#createBtn').attr('class', 'btn btn-success').html('Tournament Created')";
            }
            else {
                echo "$('createBtn').removeAttr('disabled');";
                errModal($conn->error);
            }
        }
        else if ($_POST["type"] === "edit") {
            $set = "";

            foreach ($_POST as $key => $value) {
                if ($key !== "type" && $key !== "id" && $key !== "nameDropdown") {
                    if (is_numeric($value)) {
                        $set .= "$key = $value, ";
                    }
                    else {
                        $set .= "$key = '$value', ";
                    }
                }
            }

            $sql = "UPDATE tournament SET" . rtrim($set, ", ") . " WHERE id = " . $_POST["id"] . ";";

            if ($conn->query($sql)) {
                echo "$('#editBtn').attr('class', 'btn btn-success').html('Tournament Edited').removeAttr('disabled');";
            }
            else {
                echo "$('#editBtn').attr('class', 'btn btn-danger').html('Error').removeAttr('disabled');";
                errModal($sql);
            }
        }
    }
    else {
        echo "$(document.body).html(`" . haenStop() . "`).attr('style', 'background-color: black');";
    }
}
else {
    echo "$(document.body).html(`" . haenStop() . "`).attr('style', 'background-color: black');";
}