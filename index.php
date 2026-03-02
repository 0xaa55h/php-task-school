<?php

include_once "utils.php";
include_once "api/user/get.php";

session_start();

if (!isset($_SESSION["id"])) {

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Weather Data</title>
    <style>
        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        form * {
            width: fit-content;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION["error_message"])): ?>
        <p style="color: red"><?= read_error_message() ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION["success_message"])): ?>
        <p style="color: green"><?= read_success_message() ?></p>
    <?php endif; ?>
    <h1>You are not logged in, please sign in or create an account below:</h1>
    <form action="./api/auth/signup.php" method="POST">
        <h2>Create account</h2>
        <label>
            Username
            <input name="username" type="text">
        </label>
        <label>
            E-mail
            <input name="email" type="email">
        </label>
        <label>
            Password
            <input name="password" type="password">
        </label>
        <button type="submit">
            Create
        </button>
    </form>
    <hr>
    <form action="./api/auth/signin.php" method="POST">
        <h2>Login</h2>
        <label>
            E-mail
            <input name="email" type="email">
        </label>
        <label>
            Password
            <input name="password" type="password">
        </label>
        <button type="submit">
            Login
        </button>
    </form>
</body>
</html>
<?php
    die();
}

try {
    $user = getUserData();
    $to_store = requestNewData();
    Db::insert("weather_data", [
        "temp" => $to_store["main"]["temp"],
        "user_id" => $user["id"]
    ]);
    $data = Db::queryAll("SELECT * FROM weather_data WHERE user_id = ?", $user["id"]);
    $resolved = json_decode(file_get_contents("https://nominatim.openstreetmap.org/reverse?lat=" . urlencode($user["latitude"]) ."&lon=". urlencode($user["longitude"]) . "&format=json&addressdetails=1", false, stream_context_create([
            "http" => [
                    "header" => "User-Agent: MyApp/1.0 (prokupekj.07@spst.eu)\r\n"
            ]
    ])), true, 512, JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    if (isset($_SESSION["error_message"])) {
        $_SESSION["error_message"] .= $e->getMessage();
    }
    else $_SESSION["error_message"] = $e->getMessage();
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <p style="color: red"><?= read_error_message() ?></p>
    <p style="color: green"><?= read_success_message() ?></p>

    <?php if (isset($data) && count($data) > 0): ?>
        <h1>Current temperature in <?= $resolved["address"]["city"] ?>: <?= $data[count($data) - 1]["temp"] ?> °C</h1>
    <?php endif; ?>

    <canvas id="myChart" width="800" height="400"></canvas>

    <hr>

    <form method="POST" action="api/user/api.php">
        <h2>Update location (coordinates)</h2>
        <label>
            Latitude
            <input required name="latitude">
        </label>
        <label>
            Longitude
            <input required name="longitude">
        </label>
        <button type="submit">
            Update
        </button>
    </form>
    <hr>

    <form method="POST" action="api/user/nominatim.php">
        <h2>Update location (approximation)</h2>
        <label>
            Place
            <input required name="query">
        </label>
        <button type="submit">
            Update
        </button>
    </form>

    <hr>

    <form method="POST" action="./api/auth/signout.php">
        <button type="submit">Logout</button>
    </form>
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');

        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(fn($entry) => $entry['created_at'], $data), JSON_THROW_ON_ERROR) ?>,
                datasets: [{
                    label: 'Teplota v čase',
                    data: [<?= implode /* Gaza Children */(", ", array_map(static fn ($entry) => $entry["temp"], $data)) ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Teplota v čase'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>