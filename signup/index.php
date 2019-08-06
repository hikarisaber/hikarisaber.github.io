<?php
    require("../loginhelper.php");

    if ($_COOKIE["user"]) {
        $user = getUser($_COOKIE["user"]);
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
        ?>
    )
</script>

<div class='d-flex' id='wrapper'>
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
                                    $sql = "SELECT * FROM tournaments";
                                    $result = $conn->query($sql);

                                    $json = "`[";

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option>" . $row["acronym"] . " (" . $row["rank_max"] . "-" . $row["rank_min"] . ")" . "</option>";
                                            $json = $json . "{\"tournament\": \"" . $row["acronym"] . " (" . $row["rank_max"] . "-" . $row["rank_min"] . ")\", \"team_size\": " . $row["team_size"] . ", \"team_size_min\": " . $row["team_size_min"] . "},";
                                        }
                                    }

                                    $json = rtrim($json, ",") . "]`";
                                ?>
                            </select>
                        </div>
                        <div class='form-group'>
                            <input id='teamnameinput' type='text' required='text' maxlength='18' name='teamname' class='form-control' id='teamInput' placeholder='Team Name'>
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
            var teamsize = result[index].team_size;

            for (i = 0; i < teamsize; i++) {
                var req = "";
                if (i < result[index].team_size_min) {
                    req = "required='text' ";
                }

                $("#playerInput").append(`<input type='text' ${req}name='players[${i}]' class='form-control' id='player_${i}_input' placeholder='Player ${i + 1}'>`)
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
        var formURL = $(this).attr("action");

        $("#signupbtn").html(`<div class='spinner-border spinner-border-sm text-dark' role='status'></div>`);

        $.ajax({
            url: "submit.php",
            type: "POST",
            data: postData,
            success: function (data) {
                console.log(data);
                eval(data);
            }
        });

        e.preventDefault();
    });
</script>
</body>
</html>

