<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div id="melotitle">
        <img src="../images/title.png" alt="melotitle">
    </div>
    <div id="container">
        <form method="post" action="inlog.php"> <!-- skicka till en annan sida sen till admin om inloget är rätt    på andra sidan sätt session för att kolla om den är på i admin sidan-->
            <div>
            <label>Username</label>
            <input type="text" name="Username" placeholder="Write your Username here" autofocus required>
            <label>Password</label>
            <input type="password" name="Password" placeholder="Write your password here" required>
            <input id="login" type="submit" value="Login">                
            </div>

        </form>        
    </div>
</body>
</html>