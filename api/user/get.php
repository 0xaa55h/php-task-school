<?php

include_once "utils.php";
include_once "Db.php";
Db::connect(getenv('DB_HOST') ?: "localhost:3306", getenv('DB_NAME') ?: "final", getenv('DB_USER') ?: "appuser", getenv('DB_PASSWORD') ?: "apppassword");

function getUserWeatherData()
{
    $id = $_SESSION["id"];
    if (!isset($_SESSION["id"])) {
        throw new AppException("Session has expired");
    }
    return Db::queryAll("SELECT * FROM weather_data WHERE user_id = ?", $id);
}

function getUserData()
{
    $id = $_SESSION["id"];
    if (!isset($_SESSION["id"])) {
        throw new AppException("Session has expired");
    }
    return Db::queryOne("SELECT * FROM users WHERE id = ?", $id);
}

function requestNewData() {
    $user = getUserData();
    if (!isset($user["latitude"], $user["longitude"])) {
        throw new AppException("You did not specify your location, you may update it below.");
    }
    return json_decode(file_get_contents("https://api.openweathermap.org/data/2.5/weather?lat=" . $user["latitude"] . "&lon=" . $user["longitude"] . "&units=metric&mode=json&appid=" . getenv("OW_API_KEY")), true, 512, JSON_THROW_ON_ERROR);
}