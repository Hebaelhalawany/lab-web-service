<?php
require_once 'vendor/autoload.php';
require_once 'Weather.php';

// Start session to maintain form data
session_start();

// City selection form processing
$selectedCity = '';
$weatherData = null;
$method = 'curl'; // Default method

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCity = $_POST['city'] ?? '';
    $method = $_POST['method'] ?? 'curl';
    
    if (!empty($selectedCity)) {
        $weather = new Weather();
        
        // Get weather data using the selected method
        if ($method === 'curl') {
            $weatherData = $weather->getWeatherByCurl($selectedCity);
        } else {
            $weatherData = $weather->getWeatherByGuzzle($selectedCity);
        }
        
        // Save selected values in session
        $_SESSION['selectedCity'] = $selectedCity;
        $_SESSION['method'] = $method;
    }
}

// If there are values in session and no POST data, use session values
if (empty($selectedCity) && isset($_SESSION['selectedCity'])) {
    $selectedCity = $_SESSION['selectedCity'];
    $method = $_SESSION['method'] ?? 'curl';
}

// Get the list of Egyptian cities
$cityList = Weather::getEgyptianCities();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egyptian Weather App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, button {
            padding: 8px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .weather-data {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
        }
        .weather-item {
            margin-bottom: 10px;
        }
        .radio-group {
            display: flex;
            gap: 15px;
        }
        .radio-group label {
            display: inline;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Egyptian City Weather</h1>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="city">Select City:</label>
                <select name="city" id="city">
                    <option value="">-- Select a city --</option>
                    <?php foreach ($cityList as $city): ?>
                        <option value="<?php echo htmlspecialchars($city['id']); ?>" 
                                <?php echo ($selectedCity == $city['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>API Method:</label>
                <div class="radio-group">
                    <div>
                        <input type="radio" id="curl" name="method" value="curl" 
                               <?php echo ($method === 'curl') ? 'checked' : ''; ?>>
                        <label for="curl">cURL</label>
                    </div>
                    <div>
                        <input type="radio" id="guzzle" name="method" value="guzzle" 
                               <?php echo ($method === 'guzzle') ? 'checked' : ''; ?>>
                        <label for="guzzle">Guzzle</label>
                    </div>
                </div>
            </div>
            
            <button type="submit">Get Weather</button>
        </form>
        
        <?php if ($weatherData): ?>
            <div class="weather-data">
                <h2>Weather for <?php echo htmlspecialchars($weatherData['city']); ?></h2>
                <div class="weather-item">
                    <strong>Temperature:</strong> <?php echo htmlspecialchars($weatherData['temp']); ?> °C
                </div>
                <div class="weather-item">
                    <strong>Min Temperature:</strong> <?php echo htmlspecialchars($weatherData['temp_min']); ?> °C
                </div>
                <div class="weather-item">
                    <strong>Max Temperature:</strong> <?php echo htmlspecialchars($weatherData['temp_max']); ?> °C
                </div>
                <div class="weather-item">
                    <strong>Humidity:</strong> <?php echo htmlspecialchars($weatherData['humidity']); ?>%
                </div>
                <div class="weather-item">
                    <strong>Description:</strong> <?php echo htmlspecialchars($weatherData['description']); ?>
                </div>
                <div class="weather-item">
                    <small>Data fetched using <?php echo ($method === 'curl') ? 'cURL' : 'Guzzle'; ?></small>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>