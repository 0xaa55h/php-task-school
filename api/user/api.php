<?php

session_start();
include_once "../../utils.php";
include_once "../../Db.php";
include_once "update.php";
Db::connect(getenv('DB_HOST') ?: "localhost:3306", getenv('DB_NAME') ?: "final", getenv('DB_USER') ?: "appuser", getenv('DB_PASSWORD') ?: "apppassword");

if (!isset($_POST["latitude"], $_POST["longitude"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("../../index.php", error_message: "Bad request");
}

$latitude = $_POST["latitude"];
$longitude = $_POST["longitude"];

try {
    updateUserLocation($latitude, $longitude);
    redirect("../../index.php", success_message: "Location updated.");
} catch (AppException $e) {
    redirect("../../index.php", error_message: "An error occurred: " . $e->getMessage());
}