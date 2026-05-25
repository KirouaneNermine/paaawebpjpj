<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    // demo credentials (replace with database later)
    $correct_email = "admin@gmail.com";
    $correct_password = "1234";

    if (empty($email) || empty($password)) {
        $error = "Fill all fields";
    } 
    elseif ($email === $correct_email && $password === $correct_password) {
        $_SESSION["user"] = $email;
        header("Location: index.html");
        exit();
    } 
    else {
        $error = "Wrong email or password";
    }
}

include "login.html";
?>
