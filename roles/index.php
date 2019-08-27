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

    <style>
        #createBtn {
            width: 80%;
            box-sizing: border-box;
        }

        #roleCreate {
            width: 40%;
            min-height: 100vh;
            float: left;
            padding-top: 15px;
            padding-right: 15px;
            border-style: none solid none none;
            border-width: 1px;
            border-color: #DEE2E6;
        }

        #createForm {
            padding-left: 30%;
            padding-right: 30%;
            text-align: center;
        }

        .form-control {
            margin-bottom: 6px;
        }

        #roleEdit {
            float: left;
            padding: 0 15px 0 15px
        }
    </style>
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

            $sql = "SELECT * FROM role";
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
            <div id='roleCreate'>
                <form id='createForm'>
                    <h1 class="mt-2">Create</h1>
                    <div class='form-group'>
                        <input id="nameCreate" type="text" required="text" maxlength="50" name="name" class="form-control" placeholder="Name">
                        <div class="form-group form-check">
                            <input id="activeCreate" type="checkbox" name="active" value="0" class="form-check-input">
                            <label class="form-check-label">Active</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="editTournamentCreate" type="checkbox" name="editTournament" value="0" class="form-check-input">
                            <label class="form-check-label">Create/Edit Tournaments</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="editRoleCreate" type="checkbox" name="editRole" value="0" class="form-check-input">
                            <label class="form-check-label">Create/Edit Roles</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="editUserCreate" type="checkbox" name="editUser" value="0" class="form-check-input">
                            <label class="form-check-label">Edit Users</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="takeMatchesCreate" type="checkbox" name="takeMatches" value="0" class="form-check-input">
                            <label class="form-check-label">Take matches to ref</label>
                        </div>
                    </div>
                    <button id='createBtn' type='submit' class='btn btn-primary'>Create Tournament</button>
                </form>
            </div>
            <div id='roleEdit'>
                <form id='editForm'>
                    <h1 class="mt-2">Edit</h1> 
                    <div class='form-group'>
                        <input id='roleId' type='number' name='id' class='form-control' placeholder='id' readonly>
                        <select class='form-control' name='nameDropdown' id='nameDropdown' required onchange='addRoleData(this.value)'>
                            <option selected disabled hidden>Select a role</option>
                            <?php
                                $json = "`[";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option>" . $row["name"] . "</option>";
                                        $json = $json . "{\"id\": " . $row["id"] .
                                            ", \"name\": \"" . $row["name"] .
                                            "\", \"active\":"  . $row["active"] .
                                            ", \"editTournament\": " . $row["editTournament"] .
                                            ", \"editUser\": " . $row["editUser"] .
                                            ", \"takeMatches\": " . $row["takeMatches"] .
                                            ", \"editRole\": " . $row["editRole"] . "}, ";
                                    }
                                }

                                $json = rtrim($json, ", ") . "]`";
                            ?>
                        </select>
                        <input id="nameEdit" type="text" required="text" maxlength="50" name="name" class="form-control" placeholder="Name">
                        <div class="form-group form-check">
                            <input id="activeEdit" type="checkbox" name="active" value="0" class="form-check-input">
                            <label class="form-check-label">Active</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="editTournamentEdit" type="checkbox" name="editTournament" value="0" class="form-check-input">
                            <label class="form-check-label">Create/Edit Tournaments</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="editRoleEdit" type="checkbox" name="editRole" value="0" class="form-check-input">
                            <label class="form-check-label">Create/Edit Roles</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="editUserEdit" type="checkbox" name="editUser" value="0" class="form-check-input">
                            <label class="form-check-label">Edit Users</label>
                        </div>
                        <div class="form-group form-check">
                            <input id="takeMatchesEdit" type="checkbox" name="takeMatches" value="0" class="form-check-input">
                            <label class="form-check-label">Take matches to ref</label>
                        </div>
                    </div>
                    <button id='editBtn' type='submit' class='btn btn-primary'>Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('input[type="checkbox"]').change(function () {
        this.value = (Number(this.checked));
    })

    function addRoleData (role) {
        let roledata = JSON.parse(<?php echo $json ?>);
        let index = roledata.findIndex(x => x.name === role);
        
        $("#roleId").attr("value", roledata[index].id)
        $("#nameEdit").attr("value", roledata[index].name)
        $("#activeEdit").attr("value", roledata[index].active).prop("checked", !!+roledata[index].active);
        $("#editTournamentEdit").attr("value", roledata[index].editTournament).prop("checked", !!+roledata[index].editTournament);
        $("#editRoleEdit").attr("value", roledata[index].editRole).prop("checked", !!+roledata[index].editRole);
        $("#editUserEdit").attr("value", roledata[index].editUser).prop("checked", !!+roledata[index].editUser);
        $("#takeMatchesEdit").attr("value", roledata[index].takeMatches).prop("checked", !!+roledata[index].takeMatches);
    }

    function postData (type, data) {
        data[data.length] = {name: "type", value: type};

        $.ajax({
            url: "submit.php",
            type: "POST",
            data: data,
            success: function (result) {
                eval(result);
            }
        });
    }

    $("#createForm").submit(function(e) {
        let data = $(this).serializeArray();

        $("#createBtn").html(`<div class='spinner-border spinner-border-sm text-dark' role='status'></div>`)//.attr("disabled", "");

        postData("create", data);
        e.preventDefault();
    });

    $("#editForm").submit(function(e) {
        let data = $(this).serializeArray();

        $("#editBtn").html(`<div class='spinner-border spinner-border-sm text-dark' role='status'></div>`).attr("disabled", "");

        postData("edit", data);
        e.preventDefault();
    });
</script>
</body>