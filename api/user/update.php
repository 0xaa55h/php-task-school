<?php

include_once "../../utils.php";
include_once "../../Db.php";
Db::connect(getenv('DB_HOST') ?: "localhost:3306", getenv('DB_NAME') ?: "final", getenv('DB_USER') ?: "appuser", getenv('DB_PASSWORD') ?: "apppassword");

function updateUserLocation($latitude, $longitude)
{
    $id = $_SESSION["id"];
    if (!isset($_SESSION["id"])) {
        throw new AppException("Session has expired");
    }
    try {
        Db::update("users", [
            "latitude" => $latitude,
            "longitude" => $longitude
        ], "WHERE id = ?", $id);
        Db::query("DELETE FROM weather_data WHERE user_id = ?", $id);
    } catch (Exception $e) {
        throw new AppException("Error during updating location: " . $e->getMessage());
    }
}

function updateUserLocationNominatim($query) {
    $id = $_SESSION["id"];
    if (!isset($_SESSION["id"])) {
        throw new AppException("Session has expired");
    }
    $res = json_decode(file_get_contents("https://nominatim.openstreetmap.org/search?q=" . $query . "&format=json&limit=1", false, stream_context_create([
        "http" => [
            "header" => "User-Agent: MyApp/1.0 (prokupekj.07@spst.eu)\r\n"
        ]
    ])), true, 512, JSON_THROW_ON_ERROR);
    updateUserLocation($res[0]["lat"], $res[0]["lon"]);
}