<?php

$logDirectory = 'logs/';
$logFilename = $logDirectory . date('Y-m-d') . '.log';
$configFile = 'client.config';
$encryptionKey = 'Your_Encryption_Key';

// Create the log directory if it doesn't exist
if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0755, true);
}

class KiaLocationAPI
{
    private $username;
    private $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getLocation()
    {
        $api = new KiaAPI($this->username, $this->password);
        $vinkey = $api->login();

        try {
            $response = $api->sendCommand($vinkey, '"GET_LOCATION"');
            if (strpos($response, 'gpsDetail') !== false) {
                $data = json_decode($response, true);

                if (isset($data['payload']['gpsDetail']['coord']['lat']) && isset($data['payload']['gpsDetail']['coord']['lon'])) {
                    // Latitude and longitude are available
                    $latitude = $data['payload']['gpsDetail']['coord']['lat'];
                    $longitude = $data['payload']['gpsDetail']['coord']['lon'];
                    writeToLog("Successfully retrieved vehicle location");
                    return ['latitude' => $latitude, 'longitude' => $longitude];
                }
            }
        } catch (Exception $e) {
            // Handle the exception
        }

        return null;
    }
}

class KiaAPI
{
    private $username;
    private $password;
    private $cookies = array();

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        writeToLog('Initializing KiaAPI for username: ' . $this->username);
    }

    public function sendRequest($url, $requestBody = "", $vinkey = "")
    {
        $headers = $this->getHeaders($vinkey, $requestBody);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($requestBody !== "") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        if (!empty($this->cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, implode("; ", $this->cookies));
        }

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) {
            if (stripos($header, "Set-Cookie:") === 0) {
                $this->cookies[] = trim(substr($header, strlen("Set-Cookie:")));
            }
            return strlen($header);
        });

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        return $responseBody;
    }

    private function getHeaders($vinkey, $requestBody = "")
    {
        $headers = [
            "Accept: application/json, text/plain, */*",
            "Accept-Encoding: gzip, deflate, br",
            "Accept-Language: en-US,en;q=0.9",
            "Origin: https://owners.kia.com",
            "Referer: https://owners.kia.com/content/owners/en/locations.html?page=my-locations",
            "Sec-Fetch-Dest: empty",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Site: same-origin",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36 Edg/113.0.1774.35",
        ];

        if ($vinkey != "") {
            array_push($headers, "vinkey: $vinkey");
        }

        if ($requestBody === '{"requestType":0}') {
            $headers[] = "Content-Type: application/json;charset=utf-8";
        }

        return $headers;
    }

    public static function getCommandName($command)
    {
        $commandData = json_decode($command, true);
        switch ($commandData['action']) {
            case 'ACTION_EXEC_REMOTE_LOCK_DOORS':
                return 'Lock Doors';
            case 'ACTION_EXEC_REMOTE_UNLOCK_DOORS':
                return 'Unlock Doors';
            case 'ACTION_EXEC_REMOTE_CLIMATE_ON':
                return 'Start Climate Control';
            case 'ACTION_GET_LAST_REFRESHED_STATUS_FULL_LOOP':
                return 'Get Vehicle Status';
            case 'GET_LOCATION':
                return 'Get vehicle location';
            default:
                return 'Unknown Command';
        }
    }

    public function login($force = false)
    {
        global $configFile;

        $hash = hash('sha512', $this->username . $this->password);
        $config = $this->loadConfig($configFile);

        if ($force || !isset($config[$hash])) {
            $json_body = $this->convertToJSON($this->username, $this->password);
            $responseBody = $this->sendRequest("https://owners.kia.com/apps/services/owners/apiGateway", $json_body);
            $vinkey = $this->parseVinkey($responseBody);
            $config[$hash] = [
                'vinkey' => $this->encryptData($vinkey),
                'cookies' => $this->encryptData(implode("; ", $this->cookies)),
                'lastCommandTimestamp' => time()
            ];
            $this->saveConfig($configFile, $config);
            writeToLog('Logging in user: ' . $this->username);
            writeToLog('Obtained VIN key: ' . $vinkey);
        } else {
            $this->cookies = explode("; ", $this->decryptData($config[$hash]['cookies']));
            $vinkey = $this->decryptData($config[$hash]['vinkey']);
        }

        return $vinkey;
    }

    public function sendCommand($vinkey, $action)
    {
        if ($action === '"GET_LOCATION"') {
            writeToLog('Getting location of vehicle');
            $responseBody = $this->sendRequest("https://owners.kia.com/apps/services/owners/location/vehicle.html", '{"requestType":0}', $vinkey);
        } else {
            writeToLog('Sending command: ' . self::getCommandName($action));
            $responseBody = $this->sendRequest("https://owners.kia.com/apps/services/owners/remotevehicledata?requestJson=" . $action, "", $vinkey);
        }

        return $responseBody;
    }

    private function parseVinkey($jsonstr)
    {
        $jsons = json_decode($jsonstr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse VIN key response.');
        }
        if (!isset($jsons["payload"]["vehicleSummary"][0]["vehicleKey"])) {
            throw new Exception('Invalid VIN key response.');
        }
        return $jsons["payload"]["vehicleSummary"][0]["vehicleKey"];
    }

    private function convertToJSON($username, $password)
    {
        $json = [
            "userId" => $username,
            "password" => $password,
            "userType" => "1",
            "vin" => "",
            "action" => "authenticateUser"
        ];

        return json_encode($json);
    }

    private function loadConfig($filename)
    {
        if (file_exists($filename)) {
            $encryptedConfig = file_get_contents($filename);
            $config = unserialize($this->decryptData($encryptedConfig));
            return $config;
        } else {
            return [];
        }
    }

    private function saveConfig($filename, $config)
    {
        $encryptedConfig = $this->encryptData(serialize($config));
        file_put_contents($filename, $encryptedConfig);
    }

    private function encryptData($data)
    {
        global $encryptionKey;
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encryptedData);
    }

    private function decryptData($encryptedData)
    {
        global $encryptionKey;
        $encryptedData = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($encryptedData, 0, $ivLength);
        $encryptedData = substr($encryptedData, $ivLength);
        return openssl_decrypt($encryptedData, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
    }

    public function checkRateLimit($hash)
    {
        global $configFile;

        $config = $this->loadConfig($configFile);

        if (isset($config[$hash])) {
            $lastCommandTimestamp = $config[$hash]['lastCommandTimestamp'];
            $elapsedTime = time() - $lastCommandTimestamp;
            if ($elapsedTime < 3600) {
                return ($config[$hash]['commandCount'] < 5);
            }
        }

        return true;
    }

    public function updateRateLimit($hash)
    {
        global $configFile;

        $config = $this->loadConfig($configFile);

        if (isset($config[$hash])) {
            $config[$hash]['lastCommandTimestamp'] = time();
            if (isset($config[$hash]['commandCount'])) {
                $config[$hash]['commandCount']++;
            } else {
                $config[$hash]['commandCount'] = 1;
            }

            $this->saveConfig($configFile, $config);
        }
    }

    public function checkDailyCommandLimit($response)
    {
        $json = json_decode($response, true);

        if (
            isset($json['status']['statusCode']) && $json['status']['statusCode'] === 1 &&
            isset($json['status']['errorType']) && $json['status']['errorType'] === 1 &&
            isset($json['status']['errorCode']) && $json['status']['errorCode'] === 1126
        ) {
            return true;
        }

        if (
            isset($json['status']['statusCode']) && $json['status']['statusCode'] === 1 &&
            isset($json['status']['errorType']) && $json['status']['errorType'] === 1 &&
            isset($json['status']['errorCode']) && $json['status']['errorCode'] === 404
        ) {
            return true;
        }

        return false;
    }
}
class KiaCommandBuilder
{
    public static function buildAction($command, $temp = null, $defr = null)
    {
        $action = [];

        switch ($command) {
            case '1':
                $action = ["action" => "ACTION_EXEC_REMOTE_LOCK_DOORS"];
                break;
            case '2':
                $action = ["action" => "ACTION_EXEC_REMOTE_UNLOCK_DOORS"];
                break;
            case '3':
                $action = [
                    "action" => "ACTION_EXEC_REMOTE_CLIMATE_ON",
                    "remoteClimate" => [
                        "airTemp" => ["value" => $temp, "unit" => 1],
                        "airCtrl" => true,
                        "defrost" => $defr,
                        "ventilationWarning" => false,
                        "ignitionOnDuration" => ["value" => "10", "unit" => 4],
                        "heatingAccessory" => ["steeringWheel" => 0, "sideMirror" => 0, "rearWindow" => 0]
                    ]
                ];
                break;
            case '5':
                $action = ["action" => "ACTION_GET_LAST_REFRESHED_STATUS_FULL_LOOP"];
                break;
            case '6':
                $action = "GET_LOCATION";
                break;
            default:
                return false;
        }

        return json_encode($action);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $username = $_GET['username'] ?? '';
    $password = $_GET['password'] ?? '';
    $defr = $_GET['defrost'] ?? null;
    $command = $_GET['command'] ?? '';
    $temp = isset($_GET['temp']) ? $_GET['temp'] : null;

    if (empty($username) || empty($password) || empty($command)) {
        writeToLog('Invalid request parameters.', 'ERROR');
        exit;
    }

    $hash = hash('sha512', $username . $password);
    $api = new KiaAPI($username, $password);

    if (!$api->checkRateLimit($hash)) {
        writeToLog('Rate limit exceeded for user: ' . $username, 'ERROR');
        echo "rate_limit";
        exit;
    }

    if ($command === '6') {
        $firstrun = true;
        fir:
        $locationAPI = new KiaLocationAPI($username, $password);
        $location = $locationAPI->getLocation();

        if ($location !== null) {
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];

            echo $latitude . ',' . $longitude;
            exit();
        } else {
            if ($firstrun) {
                writeToLog('Failed to retrieve the location, attempting again with new session', 'ERROR');
                $api->login(true);
                goto fir;
                exit;
            } else
            {
                writeToLog('Failed to retrieve the location', 'ERROR');
            }
        }
    } else {
        $action = KiaCommandBuilder::buildAction($command, $temp, $defr);

        if ($action === false) {
            writeToLog('Invalid command.', 'ERROR');
            exit;
        }

        try {
            $vinkey = $api->login();
            $response = $api->sendCommand($vinkey, $action);

            if ($api->checkDailyCommandLimit($response)) {
                writeToLog('Daily command limit reached for user: ' . $username);
                $api->updateRateLimit($hash);
                exit;
            }

            if (strpos($response, 'Success') !== false) {
                $api->updateRateLimit($hash);
                writeToLog('Command executed successfully', 'SUCCESS');
                echo "successful";
                exit();
            } else {
                writeToLog('Command execution failed. Attempting to login again.');
                $vinkey = $api->login(true);
                $response = $api->sendCommand($vinkey, $action);

                if (strpos($response, 'Success') !== false) {
                    $api->updateRateLimit($hash);
                    writeToLog('Successful command execution after re-login.', 'SUCCESS');
                    echo "successful";
                    exit();
                } else {
                    writeToLog('Command execution failed after re-login.', 'ERROR');
                }
            }
        } catch (Exception $e) {
            writeToLog('An error occurred: ' . $e->getMessage(), 'ERROR');
        }
    }
}

function writeToLog($message, $level = 'INFO')
{
    global $logFilename;

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents($logFilename, $logMessage, FILE_APPEND);
}

function encryptData($data)
{
    global $encryptionKey;
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encryptedData);
}

function decryptData($encryptedData)
{
    global $encryptionKey;
    $encryptedData = base64_decode($encryptedData);
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    $iv = substr($encryptedData, 0, $ivLength);
    $encryptedData = substr($encryptedData, $ivLength);
    return openssl_decrypt($encryptedData, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
}
?>
