<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Weather class to handle weather data operations
 */
class Weather
{
    /**
     * OpenWeatherMap API key - replace with your actual API key after registering
     * @var string
     */
    private $apiKey = 'b6de1af4989ae03601fbfd07e804f454';
    
    /**
     * OpenWeatherMap API base URL
     * @var string
     */
    private $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';
    
    /**
     * Path to the city list JSON file
     * @var string
     */
    private static $cityListFile = 'city.list.json';
    
    /**
     * Get weather information using cURL
     * 
     * @param string $cityId The ID of the city
     * @return array|null Weather data or null if error
     */
    public function getWeatherByCurl($cityId)
    {
        // Build the API URL with parameters
        $url = $this->apiUrl . "?id={$cityId}&units=metric&appid={$this->apiKey}";
        
        // Initialize cURL session
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Execute cURL request and get response
        $response = curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            error_log('cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        // Close cURL session
        curl_close($ch);
        
        // Decode the JSON response
        $data = json_decode($response, true);
        
        // Format and return the weather data
        return $this->formatWeatherData($data);
    }
    
    /**
     * Get weather information using Guzzle HTTP client
     * 
     * @param string $cityId The ID of the city
     * @return array|null Weather data or null if error
     */
    public function getWeatherByGuzzle($cityId)
    {
        try {
            // Create Guzzle HTTP client
            $client = new Client();
            
            // Send GET request to OpenWeatherMap API
            $response = $client->get($this->apiUrl, [
                'query' => [
                    'id' => $cityId,
                    'units' => 'metric',
                    'appid' => $this->apiKey
                ]
            ]);
            
            // Get response body and decode JSON
            $data = json_decode($response->getBody(), true);
            
            // Format and return the weather data
            return $this->formatWeatherData($data);
            
        } catch (GuzzleException $e) {
            error_log('Guzzle Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Format raw API weather data into a simpler structure
     * 
     * @param array $data Raw API data
     * @return array|null Formatted weather data or null if invalid data
     */
    private function formatWeatherData($data)
    {
        if (!isset($data['main']) || !isset($data['weather'][0])) {
            return null;
        }
        
        return [
            'city' => $data['name'],
            'temp' => $data['main']['temp'],
            'temp_min' => $data['main']['temp_min'],
            'temp_max' => $data['main']['temp_max'],
            'humidity' => $data['main']['humidity'],
            'description' => $data['weather'][0]['description']
        ];
    }
    
    /**
     * Get a list of Egyptian cities from the city list JSON file
     * 
     * @return array List of Egyptian cities with id and name
     */
    public static function getEgyptianCities()
    {
        // Check if the file exists
        if (!file_exists(self::$cityListFile)) {
            error_log('City list file not found: ' . self::$cityListFile);
            return [];
        }
        
        // Read the file content
        $content = file_get_contents(self::$cityListFile);
        if ($content === false) {
            error_log('Failed to read city list file');
            return [];
        }
        
        // Decode JSON to array
        $cities = json_decode($content, true);
        if ($cities === null) {
            error_log('Failed to decode city list JSON: ' . json_last_error_msg());
            return [];
        }
        
        // Filter Egyptian cities (country code "EG")
        $egyptianCities = array_filter($cities, function($city) {
            return isset($city['country']) && $city['country'] === 'EG';
        });
        
        // Sort cities by name
        usort($egyptianCities, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        // Return only the needed fields
        return array_map(function($city) {
            return [
                'id' => $city['id'],
                'name' => $city['name']
            ];
        }, $egyptianCities);
    }
}