<?php ?>

<?php
   #print_r($_POST);
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
            <!-- Make loop for each (competition) -->
        <label>1<input type="radio" name="Comp"></label><br>
        <label>2<input type="radio" name="Comp"></label><br>
        <input type="submit" value="Update">
        <input type="hidden" name="showComp" Value=1>
        </form>
    </div>

    <div>
        <?php 
        if(isset($_POST["showComp"])){
            
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