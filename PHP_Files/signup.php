<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'circusas_db');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = htmlspecialchars(trim($_POST['username']));
    $email    = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    $bdate    = htmlspecialchars(trim($_POST['bdate']));

    if (empty($username)) {
        $errors['username'] = "Username is required";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $errors['email'] = "Email already exists";
        }
    }

    if (empty($password)) {
        $errors['password'] = "Password is required";
    } else if (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }

    if (empty($confirm)) {
        $errors['confirm'] = "Please confirm your password";
    } else if ($confirm != $password) {
        $errors['confirm'] = "Passwords do not match";
    }

    if (empty($bdate)) {
        $errors['bdate'] = "Birth date is required";
    }

    if (count($errors) == 0) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $insert = mysqli_query($conn, "INSERT INTO users (username, email, password, bdate) VALUES ('$username', '$email', '$hashed', '$bdate')");

        if (!$insert) {
            die("Error: " . mysqli_error($conn));
        }

        if (mysqli_affected_rows($conn) > 0) {
            $_SESSION['user_id']    = mysqli_insert_id($conn);
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name']  = $username;
            mysqli_close($conn);
            header("Location: present.php");
            exit();
        } else {
            die("Something went wrong");
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
    <link rel="stylesheet" href="signup.css">
    <title>Sign Up</title>
</head>
<body>
    <section id="body">

    
    <main>
        <section>
            <img src="signup.jpg" alt="logosignup">
        </section>
        <section>
            <form action="" method="POST">
        <fieldset>
                <legend>Sign Up: </legend>
                    <div class="email"> 

                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email">
                        <?php if (isset($errors['email'])): ?>
                            <span class="error"><?php echo $errors['email']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="username">

                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username">
                        <?php if (isset($errors['username'])): ?>
                            <span class="error"><?php echo $errors['username']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="password">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password">
                        <?php if (isset($errors['password'])): ?>
                            <span class="error"><?php echo $errors['password']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="confirm">Confirm password:</label>
                        <input type="password" name="confirm" id="confirm">
                        <?php if (isset($errors['confirm'])): ?>
                           <span class="error">
                            <?php echo $errors['confirm']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="bdate">
                        <label for="bdate">Birth Date:</label>
                        <input type="date" name="bdate" id="bdate">
                        <?php if (isset($errors['bdate'])): ?>
                            <span class="error">
                                <?php echo $errors['bdate']; ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="register">Sign Up</button>
                    <p>you have an account <a href="#login.php">login </a></p>
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
