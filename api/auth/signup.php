<?php

session_start();
include_once "../../utils.php";
include_once "../../Db.php";

try {
    Db::connect(getenv('DB_HOST') ?: "localhost:3306", getenv('DB_NAME') ?: "final", getenv('DB_USER') ?: "appuser", getenv('DB_PASSWORD') ?: "apppassword");

    if (!isset($_POST["username"], $_POST["email"], $_POST["password"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
        redirect("../../index.php", error_message: "Bad request");
    }

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $exists = Db::queryOne("SELECT password FROM users WHERE email = ?", $email);

    if ($exists) {
        redirect("../../index.php", error_message: "User with this e-mail already exists");
    }

    Db::insert("users", [
        "username" => $username,
        "email" => $email,
        "password" => password_hash($password, PASSWORD_BCRYPT)
    ]);

    $_SESSION["id"] = Db::getLastId();
    redirect("../../index.php", success_message: "Successfuly logged in");
} catch (Exception $e) {
    redirect("../../index.php", error_message: "Error while creating user: " . $e->getMessage());
}