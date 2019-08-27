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

        #tournamentCreate {
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

        #tournamentEdit {
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

            $sql = "SELECT * FROM tournament";
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
            <div id='tournamentCreate'>
                <form id='createForm'>
                    <h1 class="mt-2">Create</h1> 
                    <div class='form-group'>
                        <input id='nameCreate' type='text' required='text' maxlength='100' name=name class='form-control' placeholder='Name'>
                        <input id='acronymCreate' type='text' required='text' maxlength='10' name=acronym class='form-control' placeholder='Acronym'>
                        <input id='minTeamSizeCreate' type='number' required='number' min='1' max='8' name=minTeamSize class='form-control' placeholder='Min Team Size'>
                        <input id='maxTeamSizeCreate' type='number' required='number' min='1' max='8' name=maxTeamSize class='form-control' placeholder='Max Team Size'>
                        <input id='minRankCreate' type='number' required='number' min='1' name=minRank class='form-control' placeholder='Min Rank (HIGHEST NUMBER)'>
                        <input id='maxRankCreate' type='number' required='number' min='1' name=maxRank class='form-control' placeholder='Max Rank (LOWEST NUMBER)'>
                        <input id='maxParticipantsCreate' type='number' required='number' name=maxParticipants class='form-control' placeholder='Max Participants'>
                        <input id='signUpOpenCreate' type='date' required='date' name=signUpOpen class='form-control'>
                        <input id='signUpCloseCreate' type='date' required='date' name=signUpClose class='form-control' onchange='checkDate(this.value, "Create")'>
                        <textarea id='detailsCreate' type='text' required='text' name=details class="form-control" rows="10" placeholder='Details'></textarea>
                    </div>
                    <button id='createBtn' type='submit' class='btn btn-primary'>Create Tournament</button>
                </form>
            </div>
            <div id='tournamentEdit'>
                <form id='editForm'>
                    <h1 class="mt-2">Edit</h1> 
                    <div class='form-group'>
                        <input id='tourId' type='number' name='id' class='form-control' placeholder='id' readonly>
                        <select class='form-control' name='nameDropdown' id='tourDropdown' required onchange='addTournamentData(this.value)'>
                            <option selected disabled hidden>Select a tournament</option>
                            <?php
                                $json = "`[";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option>" . $row["name"] . "</option>";
                                        $json .= "{\"id\": " . $row["id"] .
                                            ", \"name\": \"" . $row["name"] .
                                            "\", \"acronym\": \"" . $row["acronym"] .
                                            "\", \"minTeamSize\": " . $row["minTeamSize"] .
                                            ", \"maxTeamSize\": " . $row["maxTeamSize"] .
                                            ", \"minRank\": " . $row["minRank"] .
                                            ", \"maxRank\": " . $row["maxRank"] .
                                            ", \"maxParticipants\": " . $row["maxParticipants"] .
                                            ", \"signUpOpen\": \"" . $row["signUpOpen"] .
                                            "\", \"signUpClose\": \"" . $row["signUpClose"] .
                                            "\", \"details\": \"" . $row["details"] . "\"}, ";
                                    }
                                }
                                $json = rtrim($json, ", ") . "]`";
                            ?>
                        </select>
                        <input id='acronymEdit' type='text' required='text' maxlength='10' name=acronym class='form-control' placeholder='Acronym'>
                        <input id='minTeamSizeEdit' type='number' required='number' min='1' max='8' name=minTeamSize class='form-control' placeholder='Min Team Size'>
                        <input id='maxTeamSizeEdit' type='number' required='number' min='1' max='8' name=maxTeamSize class='form-control' placeholder='Max Team Size'>
                        <input id='minRankEdit' type='number' required='number' min='1' name=minRank class='form-control' placeholder='Min Rank (HIGHEST NUMBER)'>
                        <input id='maxRankEdit' type='number' required='number' min='1' name=maxRank class='form-control' placeholder='Max Rank (LOWEST NUMBER)'>
                        <input id='maxParticipantsEdit' type='number' required='number' name=maxParticipants class='form-control' placeholder='Max Participants'>
                        <input id='signUpOpenEdit' type='date' required='date' name=signUpOpen class='form-control'>
                        <input id='signUpCloseEdit' type='date' required='date' name=signUpClose class='form-control' onchange='checkDate(this.value, "Edit")'>
                        <textarea id='detailsEdit' type='text' required='text' name=details class="form-control" rows="10" placeholder='Details'></textarea>
                    </div>
                    <button id='editBtn' type='submit' class='btn btn-primary'>Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function checkDate (closeString, type) {
        let openString = $(`#signUpOpen${type}`).val();

        closeDate = new Date(`${closeString}T18:00:00Z`);
        openDate = new Date(`${openString}T18:00:00Z`);

        if (openDate > closeDate) {
            $(`#signUpClose${type}`).attr("invalid", "").val("");
        }
        else {
            $(`#signUpClose${type}`).attr("valid", "");
        }
    }

    function addTournamentData (tournament) {
        let tourdata = JSON.parse(<?php echo $json ?>);
        let index = tourdata.findIndex(x => x.name === tournament); 
        
        $("#tourId").attr("value", tourdata[index].id);
        $("#acronymEdit").attr("value", tourdata[index].acronym);
        $("#minTeamSizeEdit").attr("value", tourdata[index].minTeamSize);
        $("#maxTeamSizeEdit").attr("value", tourdata[index].maxTeamSize);
        $("#minRankEdit").attr("value", tourdata[index].minRank);
        $("#maxRankEdit").attr("value", tourdata[index].maxRank);
        $("#maxParticipantsEdit").attr("value", tourdata[index].maxParticipants);
        $("#signUpOpenEdit").attr("value", tourdata[index].signUpOpen);
        $("#signUpCloseEdit").attr("value", tourdata[index].signUpClose);
        $("#detailsEdit").val(tourdata[index].details);
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