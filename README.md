# Kia API PHP Library (United States)

## Introduction

*Not Associated with Kia Motors, Kia of America, or any other Kia/Hyundai brand*

The Kia API library is a PHP library that allows you to interact with the Kia API, enabling you to retrieve vehicle location and send commands such as locking/unlocking doors and starting climate control. This PHP library is specifically designed for use in the United States, providing seamless integration with Kia vehicles in this region.

This is a side project I created because I needed to interact with Kia Connect in PHP and all the Kia api libraries that already exist are for different languages, so I decided to reverse engineer the api and create my own.

Whenever I think of new stuff to add or I find bugs i'll update the project.
## Requirements

To use the Kia API library, ensure that your system meets the following requirements:

- PHP 7.0 or higher
- cURL extension enabled

## Installation

Follow the steps below to install the Kia API library:

1. Clone the repository or download the source code.
2. Place the `kia.php` file in your project directory.

## Configuration

Before using the library, you need to configure it as follows:

1. Set the log directory: Edit the `$logDirectory` variable in the `kia.php` file to specify the directory where log files will be stored.

```php
$logDirectory = 'logs/';
```

2. Set the encryption key: Edit the `$encryptionKey` variable in the `kia.php` file to specify the encryption key used for encrypting and decrypting sensitive data.

```php
$encryptionKey = 'your_encryption_key';
```

3. Set the library configuration file: Edit the `$configFile` variable in the `kia.php` file to specify the file where library configuration data will be stored.

```php
$configFile = 'library.config';
```

## Usage

To use the Kia API PHP library, follow these steps:

1. Create an instance of the `KiaAPI` class by providing your Kia username and password.

```php
$api = new KiaAPI($username, $password);
```

2. Retrieve the vehicle's location by calling the `getLocation()` method of the `KiaLocationAPI` class.

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

3. Send commands to the vehicle using the `sendCommand($vinkey, $action)` method of the `KiaAPI` class. Obtain the `$vinkey` parameter from the `login()` method.

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

During API requests, exceptions may be thrown. It is recommended to use try-catch blocks to handle these exceptions appropriately:

```php
try {
    // API code
} catch (Exception $e) {
    // Handle the exception
}
```

## Logging

The library logs information and errors to log files. Log files are created daily with the format `YYYY-MM-DD.log`. To write a log message, use the `writeToLog($message, $level)` function:

```php
writeToLog('Log message', 'LEVEL');
```

The `$message` parameter represents the log message, and the `$level` parameter represents the log level (e.g., INFO, ERROR, SUCCESS). The log message will be appended to the log file with the specified level and timestamp.

## Rate Limiting

The Kia API PHP library includes rate limiting functionality to prevent excessive API requests. By default, users are limited to 5 commands per hour. The rate limit is stored in the library configuration file.

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

The Kia API has a daily command limit. If the command limit is reached, the API will return an error response indicating the limit has been exceeded. The Kia API PHP library includes a method to check for this limit:

```php
$response = $api->sendCommand($vinkey, $action);
$api->checkDailyCommandLimit($response);
```

The `checkDailyCommandLimit($response)` method returns `true` if the daily command limit has been reached and `false` otherwise.

## Security

The Kia API PHP library prioritizes security by encrypting sensitive data using AES-256-CBC encryption with the provided encryption key. The `encryptData($data)` and `decryptData($encryptedData)` functions are used for encryption and decryption, respectively.

## License

This library is licensed under the MIT License. See the `LICENSE` file for more information.

## Troubleshooting

If you encounter any issues or errors while using the Kia API PHP library, refer to the following troubleshooting tips:

- Verify that your Kia username and password are correct.
- Double-check the configuration settings, including the log directory and encryption key.
- Ensure that your PHP version meets the minimum requirements.
- Check for any error messages in the log files generated by the library.

## Contribution

The Kia API PHP library is an open-source project, and contributions from the community are welcome.

**Please note that this library is designed specifically for use in the United States and may not be compatible with Kia APIs in other regions. Make sure to verify the compatibility and availability of the Kia API for your location.**
