<?php

session_start();
include_once "../../utils.php";
include_once "../../Db.php";
include_once "update.php";
Db::connect(getenv('DB_HOST') ?: "localhost:3306", getenv('DB_NAME') ?: "final", getenv('DB_USER') ?: "appuser", getenv('DB_PASSWORD') ?: "apppassword");

if (!isset($_POST["query"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("../../index.php", error_message: "Bad request");
}

$query = $_POST["query"];

try {
    updateUserLocationNominatim($query);
    redirect("../../index.php", success_message: "Location updated.");
} catch (Exception $e) {
    redirect("../../index.php", error_message: "An error occurred: " . $e->getMessage());
}