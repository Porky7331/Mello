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
//print_r($_POST);

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

if (empty($account)){
    //echo "invalid login";
}else{
    //print_r($account);
}

// Fill competition table with a limit of 5 competitions.
$sql = "SELECT * FROM competition";
$result = $mysqli -> query($sql);
$rows = $result->num_rows;
$toAdd = Clamp(5-$rows, 0, 5);
for ($i = 0; $i < $toAdd; $i++){
    $sql = "INSERT INTO `competition`(`ID`, `StartTime`, `EndTime`, `Location`) VALUES (5-$toAdd+$i+1, 0,0,'')";
    $mysqli -> query($sql);
}

// Get the index of which competition
$comp = 1;
if (!empty($_POST["comp"])){
    $comp = intval($_POST["comp"]);
    echo "comp $comp <br>";
}
$comp = Clamp($comp, 1, 5);

$sql = "SELECT * FROM competition WHERE ID=?";
$stmt = $mysqli -> prepare($sql);
$stmt -> bind_param("i", $comp);
$stmt -> execute();
$competition = $stmt -> get_result() -> fetch_assoc();
//echo empty($competition);
print_r($competition);

if ( empty($competition) ) {
    //echo 'Zero';
}

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
        <form method="POST" action="admin.php">       
        <label>1<input type="radio" name="comp" value="1" checked></label><br>
        <label>2<input type="radio" name="comp" value="2" <?php if($comp == 2){echo "checked";} ?>></label><br>
        <label>3<input type="radio" name="comp" value="3" <?php if($comp == 3){echo "checked";} ?>></label><br>
        <label>4<input type="radio" name="comp" value="4" <?php if($comp == 4){echo "checked";} ?>></label><br>
        <label>5<input type="radio" name="comp" value="5" <?php if($comp == 5){echo "checked";} ?>></label><br>
        <input type="hidden" name="request" Value="newComp">
        <input type="submit" value="Update">
        </form>
    </div>

    <div>
        
        
            
        <form method="POST" action="admin.php">
        
            # <!-- Make loop for each (partician) -->
        <label>Text<input type="radio" name="Value"></label><br>

        <input type="submit" value="Update">
        </form>'
         
         
        

    </div>

    <div>
        <form method="POST" action="admin.php">
        <label>Text<br><input type="text" name="Value" value="<?php echo $row["Value"]?>"></label><br>
        <input type="submit" value="Update">
        </form>
    </div>


</body>
</html>