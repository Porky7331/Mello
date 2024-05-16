<?php

echo "<br>Post: ";
print_r($_POST);


function Clamp($val = 0, $min=0, $max=1)
{
    if ($val > $max) {
        return $max;
    } elseif ($val < $min){
        return $min;
    }
    return $val;
}

session_start();
$_SESSION["Username"] = "admin";
$_SESSION["Password"] = "admin";


if (empty($_SESSION["Username"]) or empty($_SESSION["Username"])) {
    //header("Location: Admin.php");
    //echo "NOT LOGGED IN, BACK TO LOGIN PAGE";
}
//print_r($_SESSION);

// Connect to mysql
$mysqli = new mysqli("localhost","root","","melodifestivalen");

//Check if user is logged in, if not, redirect to login page
$sql = "SELECT * FROM admin WHERE username=? AND password=?";
$stmt = $mysqli -> prepare($sql);
$stmt -> bind_param("ss", $_SESSION["Username"], $_SESSION["Password"]);
$stmt -> execute();
$account = $stmt -> get_result() -> fetch_assoc();

// If user isn't logged in, send to login page
if (empty($account)){
    //echo "invalid login";
}else{
    //print_r($account);
}


// Fill competition table with a limit of 5 competitions (if not already full).
$sql = "SELECT * FROM competition";
$result = $mysqli -> query($sql);
$rows = $result->num_rows;
$toAdd = Clamp(5-$rows, 0, 5);
for ($i = 0; $i < $toAdd; $i++){
    $sql = "INSERT INTO `competition`(`ID`, `StartTime`, `EndTime`, `Location`) VALUES (5-$toAdd+$i+1, 0,0,'')";
    $mysqli -> query($sql);
}


// Get the index of which is requested to edit (1 by default)
$comp = 1;
if (!empty($_POST["comp"])){
    $comp = intval($_POST["comp"]);
    echo "<br><br>Competition index: $comp";
}
$comp = Clamp($comp, 1, 5);



$sql = "SELECT * FROM competition WHERE ID=?";
$stmt = $mysqli -> prepare($sql);
$stmt -> bind_param("i", $comp);
$stmt -> execute();
$competition = $stmt -> get_result() -> fetch_assoc();
$ID = $competition["ID"];

echo "<br><br>Competition Array:<br>";
print_r($competition);

echo "<br><br>Song Array:<br>";

$sql = "SELECT * FROM song WHERE Competition=$ID";
$result = $mysqli -> query($sql);

$songs = array();

while ($song = $result -> fetch_assoc()){
    print_r($song);
    echo "<br><br>";
    array_push($songs, [$song["ID"], $song["SongName"]]);
}
print_r($songs);


// Handle requests
if (isset($_POST["request"])) {
    if ($_POST["request"] == "updateComp"){
        $StartTime = isset($_POST["StartTime"]) ? $_POST["StartTime"] : 0;
        $EndTime = isset($_POST["EndTime"]) ? $_POST["EndTime"] : 0;
        $Location = isset($_POST["Location"]) ? $_POST["Location"] : "";

        $sql = "UPDATE `competition` SET `StartTime`=?, `EndTime`=?, `Location`=? WHERE ID=$ID";
        $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param("sss", $StartTime, $EndTime, $Location);
        $stmt -> execute();
    } elseif ($_POST["request"] == "selectSong" && isset($_POST["song"])){
        $sql = "SELECT * FROM song WHERE ID=?";
        $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param("i", $_POST["song"]);
        $stmt -> execute();
        $song = $stmt -> get_result() -> fetch_assoc();
        
        echo "<br><br>SIGGMA:<br>";
        print_r($song);

        $songID = $song["ID"];
        $songName = $song["SongName"];
        $songArtist = 1;
        $songArtistDesc = 1;
        $songURL = $song["VideoURL"];
        $songVotes = $song["Votes"];
    }
}

$StartTime = isset($StartTime) ? $StartTime : $competition["StartTime"];
$EndTime = isset($EndTime) ? $EndTime : $competition["EndTime"];
$Location = isset($Location) ? $Location : $competition["Location"];

$stmt -> close();
/*
$ID = $_SESSION["ID"];

// Connect to mysql
$mysqli = new mysqli("localhost","root","","creator");

$sql = "SELECT * FROM profiles";
$result = $mysqli -> query($sql);

while ($row = $result -> fetch_assoc()) {
    $userID = $row["ID"];
}

$sql = "";

$stmt = $mysqli -> prepare($sql);

$stmt -> bind_param("ss", $type, $val);

$stmt -> execute();

$account = $stmt -> get_result() -> fetch_assoc();

$stmt -> close();
*/
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>inlogkontrol</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div>
        <h4>Competition:</h4>
        <form method="POST" action="admin.php"> 
            <input type="hidden" name="request" Value="newComp">
            <label><input type="radio" name="comp" value="1" checked></label>1<br>
            <label><input type="radio" name="comp" value="2" <?php if($comp == 2){echo "checked";} ?>>2</label><br>
            <label><input type="radio" name="comp" value="3" <?php if($comp == 3){echo "checked";} ?>>3</label><br>
            <label><input type="radio" name="comp" value="4" <?php if($comp == 4){echo "checked";} ?>>4</label><br>
            <label><input type="radio" name="comp" value="5" <?php if($comp == 5){echo "checked";} ?>>5</label><br>
            <input type="submit" value="Choose">
        </form>

        <h4>Edit competition <?php echo "$comp"; ?>:</h4>
        <form method="POST" action="admin.php">
            <input type="hidden" name="request" Value="updateComp">
            <input type="hidden" value="<?php echo "$comp"; ?>" name="comp">

            <label>Start Time<br><input type="text" name="StartTime" value="<?php echo "$StartTime"; ?>" required></label><br><br>
            <label>End Time<br><input type="text" name="EndTime" value="<?php echo "$EndTime"; ?>" required></label><br><br>
            <label>Location<br><input type="text" name="Location" value="<?php echo "$Location"; ?>"required></label><br><br>
            <input type="submit" value="Update">
        </form>
    </div>

    <div>
        <h3>Songs:</h3>
        <form method="POST" action="admin.php" class='<?php if(sizeof($songs) == 0){echo "hidden";}?>'> 
            <?php
            foreach ($songs as $song){
                echo "<label><input type='radio' name='song' value='$song[0]' checked require>$song[1]</label><br>";
            }
            ?>
            
            <input type="hidden" name="comp" Value="<?php echo"$comp"?>">
            <input type="hidden" name="request" Value="selectSong">
            <input type="submit" value="Choose">
        </form>

        <form method="POST" action="admin.php" class='<?php if(sizeof($songs) >= 6){echo "hidden";}?>'>
            <input type="hidden" name="request" Value="addSong">
            <input type="hidden" name="comp" Value="<?php echo"$comp";?>">
            <input type="submit" value="Add Song">
        </form>
    </div>

    <div class="">
        <h3>Edit song:</h3>
        <form method="POST" action="admin.php"> 
            <input type="hidden" name="request" Value="newComp">
            $songID = $song["ID"];
        $songName = $song["SongName"];
        $songArtist = 1;
        $songArtistDesc = 1;
        $songURL = $song["VideoURL"];
        $songVotes = $song["Votes"];
            <label>Name<br><input value="<?php if(isset($songName)){echo "$123";}?>" type="text" name="SongName" require></label><br><br>
            <label>Votes<br><input type="text" name="Votes" require></label><br><br>
            <label>Video URL<br><input type="text" name="VideoURL" require></label><br><br>
            <label><input type="radio" name="song" value="1" checked>Hela världen längtar</label><br>

            <input type="submit" value="Choose">
        </form>
    </div>


</body>
</html>