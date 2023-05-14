# Kia API Client

This is a PHP client for interacting with the Kia API. It provides methods for retrieving vehicle location and sending commands such as locking/unlocking doors and starting climate control.

## Prerequisites

- PHP 7.0 or higher
- cURL extension enabled

## Installation

1. Clone the repository or download the source code.
2. Place the `kia.php` file in your project directory.

## Configuration

Before using the client, you need to set up the following configuration:

1. Set the log directory: Edit the `$logDirectory` variable in the `kia.php` file to specify the directory where log files will be stored.

```php
$logDirectory = 'logs/';
```

2. Set the encryption key: Edit the `$encryptionKey` variable in the `kia.php` file to specify the encryption key used for encrypting and decrypting sensitive data.

```php
$encryptionKey = 'your_encryption_key';
```

3. Set the client configuration file: Edit the `$configFile` variable in the `kia.php` file to specify the file where client configuration data will be stored.

```php
$configFile = 'client.config';
```

## Usage

To use the Kia API client, follow these steps:

1. Create an instance of the `KiaAPI` class by providing your Kia username and password.

```php
$api = new KiaAPI($username, $password);
```

2. Call the `getLocation()` method of the `KiaLocationAPI` class to retrieve the vehicle's latitude and longitude.

```php
$locationAPI = new KiaLocationAPI($username, $password);
$location = $locationAPI->getLocation();

if ($location !== null) {
    $latitude = $location['latitude'];
    $longitude = $location['longitude'];
    // Use the latitude and longitude values
} else {
    // Failed to retrieve the location
}
```

3. Call the `sendCommand($vinkey, $action)` method of the `KiaAPI` class to send a command to the vehicle. The `$vinkey` parameter is obtained from the `login()` method.

```php
$vinkey = $api->login();
$response = $api->sendCommand($vinkey, $action);

if (strpos($response, 'Success') !== false) {
    // Command executed successfully
} else {
    // Command execution failed
}
```

4. Customize and use the `KiaCommandBuilder` class to build commands easily. For example, to build a command to lock the doors:

```php
$command = KiaCommandBuilder::buildAction('1');
```

## Error Handling

Exceptions may be thrown during the execution of API requests. You can use a try-catch block to handle these exceptions:

```php
try {
    // API code
} catch (Exception $e) {
    // Handle the exception
}
```

## Logging

The client logs information and errors to log files. The log directory is specified in the `$logDirectory` variable. Log files are created daily with the format `YYYY-MM-DD.log`.

To write a log message, use the `writeToLog($message, $level)` function:

```php
writeToLog('Log message', 'LEVEL');
```

The `$message` parameter represents the log message, and the `$level` parameter represents the log level (e.g., INFO, ERROR, SUCCESS). The log message will be appended to the log file with the specified level and timestamp.

## Rate Limiting

The client includes rate limiting functionality to prevent excessive API requests. By default, a user is limited to 5 commands per hour. The rate limit is stored in the client configuration file.

To check the rate limit for a user, use the `checkRateLimit($hash)` method:

```php
$hash = hash('sha512', $username . $password);
$api->checkRateLimit($hash);
```

To update the rate limit after a successful command execution, use the `updateRateLimit($hash)` method:

```php
$api->updateRateLimit($hash);
```

## Daily Command Limit

The Kia API has a daily command limit. If the command limit is reached, the API will return an error response indicating the limit has been exceeded. The client includes a method to check for this limit:

```php
$response = $api->sendCommand($vinkey, $action);
$api->checkDailyCommandLimit($response);
```

The `checkDailyCommandLimit($response)` method returns `true` if the daily command limit has been reached and `false` otherwise.

## Security

The client encrypts sensitive data using AES-256-CBC encryption with the provided encryption key. The `encryptData($data)` and `decryptData($encryptedData)` functions are used for encryption and decryption, respectively.

## License

This client is licensed under the MIT License. See the `LICENSE` file for more information.
