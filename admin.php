<?php
   print_r($_POST);
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
        <label>2<input type="radio" name="comp" value="2"></label><br>
        <label>3<input type="radio" name="comp" value="3"></label><br>
        <label>4<input type="radio" name="comp" value="4"></label><br>
        <label>5<input type="radio" name="comp" value="5"></label><br>
        <input type="hidden" name="request" Value="newComp">
        <input type="submit" value="Update">
        </form>
    </div>

    <div>
        <?php 
        if(!empty($_POST["request"])){
            
            echo('<form method="POST" action="admin.php">');
            
               # <!-- Make loop for each (partician) -->
            echo('<label>Text<input type="radio" name="Value"></label><br>');

            echo('<input type="submit" value="Update"> ');
            echo('</form>');
         } 
         ?>
        

    </div>

    <div>
        <form method="POST" action="admin.php">
        <label>Text<br><input type="text" name="Value" value="<?php echo $row["Value"]?>"></label><br>
        <input type="submit" value="Update">
        </form>
    </div>


</body>
</html>