<?php

session_start();
include_once "../../utils.php";
include_once "../../Db.php";
Db::connect(getenv('DB_HOST') ?: "localhost:3306", getenv('DB_NAME') ?: "final", getenv('DB_USER') ?: "appuser", getenv('DB_PASSWORD') ?: "apppassword");

if (!isset($_POST["email"], $_POST["password"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("../../index.php", error_message: "Bad request");
}

$email = $_POST["email"];
$password = $_POST["password"];

$user = Db::queryOne("SELECT id, password FROM users WHERE email = ?", $email);

if (!$user) {
    redirect("../../index.php", error_message: "Invalid credentials");
}

if (password_verify($password, $user["password"])) {
    $_SESSION["id"] = $user["id"];
    redirect("../../index.php", success_message: "Logged in successfully");
}

redirect("../../index.php", error_message: "Invalid credentials");