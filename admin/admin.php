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

if (empty($_POST["Username"]) or empty($_POST["Username"])) {
    header("Location: index.php");
}

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
$stmt -> bind_param("ss", $_POST["Username"], $_POST["Password"]);
$stmt -> execute();
$account = $stmt -> get_result() -> fetch_assoc();

if (!$account){
    header("Location: index.php");
}

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