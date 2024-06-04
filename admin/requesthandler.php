<?php
// req (request)
$req = $_POST;

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

// Get how many songs there are in a comp
if (isset($req["songCount"])){
    $compID = $req["songCount"];
    $count = compSongAmount($compID, $mysqli);
    echo json_encode("$count");
}
// Add new song
elseif (isset($req["addSong"])){
    $compID = $req["addSong"];
    $count = compSongAmount($compID, $mysqli);
    if (intval($count) >= 6 || $compID > 4) {
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
// Get all songs from a comp
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
// Get an artist from its ID
elseif (isset($req["getArtistFromID"])){
    $artistID = $req["getArtistFromID"];

    $sql = "SELECT * FROM artist WHERE ID=?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $artistID);
    $stmt -> execute();
    $result = $stmt -> get_result() -> fetch_assoc();
    
    echo json_encode($result);
}
// Edit a song
elseif (isset($req["editSong"])){
    $sql = "UPDATE `song` SET `SongName`=?,`VideoURL`=?,`Votes`=? WHERE ID = ?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("ssii", $req["SongName"], $req["VideoURL"], $req["Votes"], $req["SongID"]);
    $stmt -> execute();
    
    $sql = "UPDATE `artist` SET `Name`=?,`Description`=? WHERE ID=?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("ssi", $req["ArtistName"], $req["ArtistDescription"], $req["ArtistID"]);
    $stmt -> execute();
}
// Delete a song
elseif (isset($req["deleteSong"])){
    $sql = "DELETE FROM `song` WHERE ID = ?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $req["SongID"]);
    $stmt -> execute();
    
    $sql = "DELETE FROM `artist` WHERE ID = ?";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $req["ArtistID"]);
    $stmt -> execute();
}
// Set the time of comp start and duration
elseif (isset($req["SetTime"])){
    $StartTime = $req["StartTime"];
    $CompDuration = $req["CompDuration"];

    $sql = "SELECT * FROM time";
    $result = $mysqli -> query($sql);
    $rows = $result->num_rows;
    $row = $result -> fetch_assoc();
    $ID = $row["ID"];

    if ($rows == 0) {
        $sql = "INSERT INTO `time`(`StartTime`, `CompDuration`) VALUES (?,?)";
        $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param("ii", $StartTime, $CompDuration);
        $stmt -> execute();
        echo json_encode("created new");
    } else {
        $sql = "UPDATE `time` SET `StartTime`=?,`CompDuration`=? WHERE ID = ?";
        $stmt = $mysqli -> prepare($sql);
        $stmt -> bind_param("sii", $StartTime, $CompDuration, $ID);
        $stmt -> execute();
        echo json_encode("updated, $StartTime");
    }
}
// Get time saved in database
elseif (isset($req["GetTime"])){
    $sql = "SELECT * FROM time";
    $query = $mysqli-> query($sql);
    $result = $query -> fetch_assoc();
    echo json_encode($result);
}
// Vote for a song
elseif (isset($req["Vote"])){
    $songID = $req["Vote"];
    if ($req["Final"]){
        $sql = "UPDATE `song` SET FinalVotes=FinalVotes+1 WHERE ID = ?";
    } else {
        $sql = "UPDATE `song` SET Votes=Votes+1 WHERE ID = ?";
    }
    
    $stmt = $mysqli -> prepare($sql);
    $stmt -> bind_param("i", $songID);
    $stmt -> execute();
}
// Get the song qualafied for the finals
elseif (isset($req["GetTopSongs"])){
    $sql = "SELECT * FROM song ORDER BY Votes DESC";
    $stmt = $mysqli -> prepare($sql);
    $stmt -> execute();
    $result = $stmt -> get_result();
    $array = $result->fetch_all(MYSQLI_ASSOC);
    
    $finalists = array();
    for ($i = 1; $i <= 4; $i++) {
        $added = 0;
        foreach ($array as $song) {
            if ($added >= 2) {continue;}
            if ($song["Competition"]==$i){
                $added += 1;
                $finalists[] = $song;
            }
          }
    }
    echo json_encode($finalists);
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