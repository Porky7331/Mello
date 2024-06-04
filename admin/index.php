<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <img src="../images/title.png" alt="melotitle"> 
    <div id="container">
        <form method="post" action="admin.php">
            <div>
            <label>Username</label>
            <input type="text" name="Username" placeholder="Username" autofocus required>
            <label>Password</label>
            <input type="password" name="Password" placeholder="Password" required>
            <input id="login" type="submit" value="Login">
            <p>Example login: User=Admin, Password=Admin</p>             
            </div>
        </form>        
    </div>
</body>
</html>