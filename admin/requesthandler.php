<?php
// req (request)
$req = $_POST;

$url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$website = false;
if(strpos($url, "melo-voting-2025"))
{
    echo "I FOUND WEBDISTE";
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

if (isset($req["songCount"])){
    $compID = $req["songCount"];
    $count = compSongAmount($compID, $mysqli);
    echo json_encode("$count");
}
elseif (isset($req["addSong"])){
    $compID = $req["addSong"];
    $count = compSongAmount($compID, $mysqli);
    if (intval($count) >= 6) {
        echo json_encode("limitReached");
        exit();
    }

    $sql = "INSERT INTO `artist`(`Name`, `Description`) VALUES ('','')";
    $mysqli -> query($sql);

    $sql = "SELECT * FROM artist ORDER BY ID DESC LIMIT 1";
    $result = mysqli_query($mysqli, $sql);
    $row = mysqli_fetch_assoc($result);
    $aristID = $row["ID"];

    $sql = "INSERT INTO `song`(`ArtistID`, `Competition`, `SongName`) VALUES ('$aristID','$compID', 'New Song')";
    mysqli_query($mysqli, $sql);

    echo json_encode("success");
}
elseif (isset($req["getCompSongs"])){
    $compID = $req["getCompSongs"];

    $sql = "SELECT * FROM song WHERE competition=?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $compID);
    $stmt -> execute();
    $result = $stmt -> get_result();
    $artistArray = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($artistArray);
}
else if (isset($req["getArtistFromID"])){
    $artistID = $req["getArtistFromID"];

    $sql = "SELECT * FROM artist WHERE ID=?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $artistID);
    $stmt -> execute();
    $result = $stmt -> get_result() -> fetch_assoc();
    
    echo json_encode($result);
}
else if (isset($req["editSong"])){
    $sql = "UPDATE `song` SET `SongName`=?,`VideoURL`=?,`Votes`=? WHERE ID = ?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("ssii", $req["SongName"], $req["VideoURL"], $req["Votes"], $req["SongID"]);
    $stmt -> execute();
    
    $sql = "UPDATE `artist` SET `Name`=?,`Description`=? WHERE ID=?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("ssi", $req["ArtistName"], $req["ArtistDescription"], $req["ArtistID"]);
    $stmt -> execute();
}
else if (isset($req["deleteSong"])){
    $sql = "DELETE FROM `song` WHERE ID = ?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $req["SongID"]);
    $stmt -> execute();
    
    $sql = "DELETE FROM `artist` WHERE ID = ?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $req["ArtistID"]);
    $stmt -> execute();
}

// Get the song count in a competition
function compSongAmount($ID, $mysqli){
    // Check if competition exists with given id
    $sql = "SELECT * FROM competition WHERE ID=?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $ID);
    $stmt -> execute();
    $competition = $stmt -> get_result() -> fetch_assoc();
    if ($competition == null){
        return false;
    }

    $sql = "SELECT * FROM song WHERE competition = $ID";
    $result = $mysqli -> query($sql);
    $rows = $result->num_rows;
    return $rows;
}

?>