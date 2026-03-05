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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Weather Data</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <?php if (isset($_SESSION["error_message"])): ?>
            <p class="text-red-600 text-sm"><?= read_error_message() ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION["success_message"])): ?>
            <p class="text-green-600 text-sm"><?= read_success_message() ?></p>
        <?php endif; ?>

        <p class="text-gray-500 text-sm">You are not logged in. Sign in or create an account below.</p>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Create account</h2>
            <form action="./api/auth/signup.php" method="POST" class="space-y-3">
                <label class="block">
                    <span class="text-sm text-gray-600">Username</span>
                    <input required min="3" max="64" name="username" type="text" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <label class="block">
                    <span class="text-sm text-gray-600">E-mail</span>
                    <input required name="email" type="email" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <label class="block">
                    <span class="text-sm text-gray-600">Password</span>
                    <input required min="8" max="64" name="password" type="password" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <button type="submit" class="w-full rounded-md bg-gray-800 text-white text-sm py-2 hover:bg-gray-700 transition-colors">Create</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Login</h2>
            <form action="./api/auth/signin.php" method="POST" class="space-y-3">
                <label class="block">
                    <span class="text-sm text-gray-600">E-mail</span>
                    <input required name="email" type="email" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <label class="block">
                    <span class="text-sm text-gray-600">Password</span>
                    <input required name="password" type="password" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <button type="submit" class="w-full rounded-md bg-gray-800 text-white text-sm py-2 hover:bg-gray-700 transition-colors">Login</button>
            </form>
        </div>
    </div>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-gray-50 p-4 md:p-8">
    <div class="max-w-2xl mx-auto space-y-6">

        <?php if (read_error_message()): ?>
            <p class="text-red-600 text-sm"><?= read_error_message() ?></p>
        <?php endif; ?>
        <?php if (read_success_message()): ?>
            <p class="text-green-600 text-sm"><?= read_success_message() ?></p>
        <?php endif; ?>

        <?php if (isset($data) && count($data) > 0): ?>
            <h1 class="text-2xl font-semibold text-gray-800">
                Current temperature in <?= htmlspecialchars($resolved["address"]["city"]) ?>:
                <span class="text-blue-600"><?= $data[count($data) - 1]["temp"] ?> °C</span>
            </h1>
        <?php endif; ?>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <canvas id="myChart"></canvas>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
            <h2 class="text-base font-semibold text-gray-800">Update location (coordinates)</h2>
            <form method="POST" action="api/user/api.php" class="space-y-3">
                <label class="block">
                    <span class="text-sm text-gray-600">Latitude</span>
                    <input required name="latitude" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <label class="block">
                    <span class="text-sm text-gray-600">Longitude</span>
                    <input required name="longitude" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <button type="submit" class="rounded-md bg-gray-800 text-white text-sm px-4 py-2 hover:bg-gray-700 transition-colors">Update</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
            <h2 class="text-base font-semibold text-gray-800">Update location (approximation)</h2>
            <form method="POST" action="api/user/nominatim.php" class="space-y-3">
                <label class="block">
                    <span class="text-sm text-gray-600">Place</span>
                    <input required name="query" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </label>
                <button type="submit" class="rounded-md bg-gray-800 text-white text-sm px-4 py-2 hover:bg-gray-700 transition-colors">Update</button>
            </form>
        </div>

        <div class="flex justify-end">
            <form method="POST" action="./api/auth/signout.php">
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 transition-colors">Logout</button>
            </form>
        </div>

    </div>
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
                responsive: true,
                maintainAspectRatio: true,
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

