<?php
session_start();
//cookie
$conn = mysqli_connect('localhost', 'root', '', 'circusas_db');

$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    if(empty($username)){
        $errors['username'] = "username required";
    }

    if(empty($password)){
        $errors['password'] = "password required";
    }

    if(count($errors) == 0){
        $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        $user = mysqli_fetch_assoc($result);

        if(!$user){
            $errors['username'] = "Username not found";
        } else if(!password_verify($password, $user['password'])){
            $errors['password'] = "Wrong password";
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email']= $user['email'];
            mysqli_close($conn);
            header("Location: present.php");
            exit();
            // http have no memory so 
            //every time you open a new page the server
            //forget who you are 
            //storing cookie look alike
            // expire when browse closes
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&family=Cinzel+Decorative&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Style_Pages/signup.css">
    <title>Login</title>
</head>
<body>
    <section id="body">
    <main>
        <section>
            <img src="signup.jpg" alt="logosignup">
        </section>
        <section>
            <form action="login.php" method="POST">
                <fieldset>
                    <legend>Login:</legend>

                    <div class="username">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username">
                        <?php if(isset($errors['username'])): ?>

                            <span class="error">
                                <?php echo $errors['username']; ?>
                            </span>
                                <?php endif; ?>
                    </div>

                    <div class="password">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password">
                        <?php if(isset($errors['password'])): ?>
                            <span class="error"><?php echo $errors['password']; ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="login">Login</button>
                    <p>Don't have an account? <a href="signup.php">Sign up</a></p>

                </fieldset>
            </form>
        </section>
    </main>
    </section>
    <footer>
        <p>&copy; 2025 Circusas. All rights reserved.</p>
    </footer>
</body>
</html>
