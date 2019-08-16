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

    <title>Enigmatic Player Singups</title>

    <style>
        #signupbtn {
            width: 80%;
            box-sizing: border-box;
        }

        #signup-input {
            width: 40%;
            min-height: 100vh;
            float: left;
            padding-top: 15px;
            padding-right: 15px;
            border-style: none solid none none;
            border-width: 1px;
            border-color: #DEE2E6;
        }

        #signupform {
            padding-left: 30%;
            padding-right: 30%;
            text-align: center;
        }

        .form-control {
            margin-bottom: 6px;
        }

        #signupinfo {
            float: left;
            padding: 0 15px 0 15px
        }
    </style>
</head>

<body>
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

<div class='d-flex' id='wrapper'>
    <?php
        echo setSidebar($user, $permissions);
    ?>

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
                        "`$('#signupbtn').attr('class', 'btn btn-danger').attr('disabled', '').html('Error');
                        $('.modal-body').html('An error occured while connecting to our database. Please try again later.');
                        $('#errorModal').modal('show');`"
                    );
                }
            ?>
        )
    </script>

    <div id='page-content-wrapper'>
        <div class='container-fluid'>
            <div id='signup-input'>
                <form id='signupform'>
                    <fieldset id='formfieldset'>
                        <div class='form-group'>
                            <select class='form-control' name='tournament' id='tourDropdown' required onchange='addPlayerInput(this.value)'>
                                <option selected disabled hidden>Select a tournament</option>
                                <?php
                                    //add tournaments as options in drop down menu
                                    $sql = "SELECT * FROM tournament";
                                    $result = $conn->query($sql);

                                    $json = "`[";

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option>" . $row["acronym"] . " (" . $row["maxRank"] . "-" . $row["minRank"] . ")" . "</option>";
                                            $json = $json . "{\"tournament\": \"" . $row["acronym"] . " (" . $row["maxRank"] . "-" . $row["minRank"] . ")\", \"maxTeamSize\": " . $row["maxTeamSize"] . ", \"minTeamSize\": " . $row["minTeamSize"] . "},";
                                        }
                                    }

                                    $json = rtrim($json, ",") . "]`";
                                ?>
                            </select>
                        </div>
                        <div class='form-group'>
                            <input id='teamnameinput' type='text' required='text' maxlength='18' name='teamname' class='form-control' id='teamInput' placeholder='Team Name'>
                        </div>
                        <div class='form-group'>
                            <select class='form-control' name='timezone' id='timezoneDropdown' required>
                                <option selected disabled hidden>Select a timezone</option>
                                <option>UTC-12</option>
                                <option>UTC-11</option>
                                <option>UTC-10</option>
                                <option>UTC-9</option>
                                <option>UTC-8</option>
                                <option>UTC-7</option>
                                <option>UTC-6</option>
                                <option>UTC-5</option>
                                <option>UTC-4</option>
                                <option>UTC-3</option>
                                <option>UTC-2</option>
                                <option>UTC-1</option>
                                <option>UTC</option>
                                <option>UTC+1</option>
                                <option>UTC+2</option>
                                <option>UTC+3</option>
                                <option>UTC+4</option>
                                <option>UTC+5</option>
                                <option>UTC+6</option>
                                <option>UTC+7</option>
                                <option>UTC+8</option>
                                <option>UTC+9</option>
                                <option>UTC+10</option>
                                <option>UTC+11</option>
                                <option>UTC+12</option>
                                <option>UTC+13</option>
                                <option>UTC+14</option>
                            </select>
                        </div>
                        <div class='form-group' id='playerInput'>
                            
                        </div>
                        <button id='signupbtn' type='submit' class='btn btn-primary'>Sign Up</button>
                    </fieldset>
                </form>
            </div>
            <div id="signupinfo">
                <h1>Signup Info</h1>
                    <p>rule 1: ice is banned
                    <br>rule 2: you need to be a gamer to sign up
                    <br>rule 3: no derankers allowed (they fucking suck)
                    <br>rule 4: no putting peepee in poopoo
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function addPlayerInput(tournament) {
        //add player inputs based on team size
        $("#playerInput").empty();

        if (tournament !== "Select a tournament") {
            var result = JSON.parse(<?php echo $json ?>);

            var index = result.findIndex(x => x.tournament === tournament);
            var teamsize = result[index].maxTeamSize;

            $("#playerInput").append(`<input type='text' name='players[0]' class='form-control' id='player_0_input' value='<?php echo $user["osuName"] ?>' readonly>`);

            for (i = 1; i < teamsize; i++) {
                var req = "";
                if (i < result[index].minTeamSize) {
                    req = "required='text' ";
                }

                $("#playerInput").append(`<input type='text' ${req}name='players[${i}]' class='form-control' id='player_${i}_input' placeholder='Player ${i + 1}'>`);
            }
        }
    }

    //removes all popovers when clicking anywhere
    $("body").on("click", function(e) {
        e.stopPropagation();
        $(".popover").popover("dispose");
    });

    //post form on submit button click
    $("#signupform").submit(function(e) {
        var postData = $(this).serializeArray();

        if (postData["tournament"] === "Select a tournament") {
            $('#tourDropdown').popover({content: 'Select a tournament', placement: 'right', trigger: 'manual'}).popover('show');
        }
        else if (postData["timezone"] === "Select a timezone") {
            $('#timezoneDropdown').popover({content: 'Select a timezone', placement: 'right', trigger: 'manual'}).popover('show');
        }
        else {
            //$("#signupbtn").html(`<div class='spinner-border spinner-border-sm text-dark' role='status'></div>`).attr("disabled", "");

            $.ajax({
                url: "submit.php",
                type: "POST",
                data: postData,
                success: function (data) {
                    console.log(data);
                    eval(data);
                }
            });
        }

        e.preventDefault();
    });

    eval(
        <?php
            if (!isset($user)) {
                echo(
                    "`$('#signupbtn').attr('class', 'btn btn-danger').attr('disabled', '').html('Error');
                    $('.modal-body').html('Please log in before signing up.');
                    $('#errorModal').modal('show');`"
                );
            }
        ?>
    )
</script>
</body>
</html>

