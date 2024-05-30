<?php


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
    //header("Location: index.php");
    //echo "NOT LOGGED IN, BACK TO LOGIN PAGE";
}
//print_r($_SESSION);

$url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$website = false;
if(strpos($url, "melo-voting-2025"))
{
    $website = true;
}
$dbUser = $website ? "ntigskov_melo-voting-2025" : "melodifestivalen";
$dbPass = $website ? "wsoNXXbQ0h7J6ZcCFDXN" : "";
$dbRoot = $website ? "ntigskov_melo-voting-2025" : "root";


// Connect to mysql
$mysqli = new mysqli("localhost", "$dbRoot", "$dbPass","$dbUser");

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
}
$comp = Clamp($comp, 1, 5);



$sql = "SELECT * FROM competition WHERE ID=?";
$stmt = $mysqli -> prepare($sql);
$stmt -> bind_param("i", $comp);
$stmt -> execute();
$competition = $stmt -> get_result() -> fetch_assoc();
$ID = $competition["ID"];


$sql = "SELECT * FROM song WHERE Competition=$ID";
$result = $mysqli -> query($sql);

$songs = array();

while ($song = $result -> fetch_assoc()){
    array_push($songs, [$song["ID"], $song["SongName"]]);
}


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

    } if ($_POST["request"] == "updateSong" && isset($_POST["song"])) {
        $sn = isset($_POST["SongName"]) ? $_POST["SongName"] : "";
        $su = isset($_POST["VideoURL"]) ? $_POST["VideoURL"] : "";
        $sv = isset($_POST["Votes"]) ? $_POST["Votes"] : 0;

        $sql = "UPDATE song SET `Competition`=?,`SongName`=?,`VideoURL`=?,`Votes`= ?";
        $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param("issi", $comp, $sn, $su, $sv);
        $stmt -> execute();

    } if (isset($_POST["song"])){
        $sql = "SELECT * FROM song WHERE ID=?";
        $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param("i", $_POST["song"]);
        $teest =  $_POST["song"];

        $stmt -> execute();
        $song = $stmt -> get_result() -> fetch_assoc();

        $songID = $song["ID"];
        $songName = $song["SongName"];
        $songArtist = 1;
        $songArtistDesc = 1;
        $songURL = $song["VideoURL"];
        $songVotes = $song["Votes"];
    }
}

$Location = isset($Location) ? $Location : $competition["Location"];

$stmt -> close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>inlogkontrol</title>
    <link rel="stylesheet" href="../css/admin.css">
    <script src="../js/admin.js" defer></script>
</head>
<body>
    <div class="container">
        <h4>Competition:</h4>
        <div id="pickCompDiv">

        </div>

        <h4>Edit competition <?php echo "$comp"; ?>:</h4>
        <form method="POST" action="index.php">
            <input type="hidden" name="request" Value="updateComp">
            <input type="hidden" value="<?php echo "$comp"; ?>" name="comp">

            <label>Location<br><input type="text" name="Location" value="<?php echo "$Location"; ?>"required></label><br><br>
            <input type="submit" value="Update">

        </form>

        <h4>Edit Schedule:</h4>
        <label>Start Time<br><input type="datetime-local" ID="StartTime"></label><br><br>
        <label>Competition Duration (Hours)<br><input type="text" ID="CompDuration"></label><br><br>
        <button onclick="setTime()">Set</button>
        <button>Next Competition</button>
    </div>

    <div class="container">
        <h4>Songs:</h4>
        <div class="songListDiv">

        </div>

        <div class="addSongDiv">
            <button onclick="addSong()">AddSong</button>
        </div>
    </div>

    <div class="hidden" id="editSongSection" class="container">
        <h4>Edit song:</h4>
        <div class="editSongDiv">
            <label>Song Name<br><input type="text" id="SongName"></label><br><br>
            <label>Video URL<br><input type="text" id="VideoURL"></label><br><br>
            <label>Edit Votes<br><input type="text" id="Votes"></label><br><br>
            <br>
            <label>Artist Name<br><input type="text" id="ArtistName"></label><br><br>
            <label>Artist Description<br><input type="text" id="ArtistDescription"></label><br><br>
            <button onclick="editSong()">Update</button>
            <button onclick="deleteSong()">Delete</button>
        </div>
    </div>


</body>
</html>