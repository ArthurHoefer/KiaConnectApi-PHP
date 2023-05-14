# Kia API Wrapper

This is a PHP code snippet that serves as a wrapper for the Kia API, allowing you to interact with your Kia vehicle programmatically. It provides functionality for retrieving the vehicle's location, executing remote commands (such as locking/unlocking doors and starting climate control), and checking rate limits.

## Prerequisites

Before using this code, make sure you have the following:

- PHP installed on your server or local development environment.
- Kia vehicle owner account credentials.
- A valid `client.config` file (for storing encrypted authentication data).

## Getting Started

1. Clone or download the code to your local machine or server.
2. Set the `$logDirectory` variable to specify the directory where log files will be stored.
3. Set the `$logFilename` variable to specify the filename of the log file (it includes the date).
4. Set the `$configFile` variable to specify the path and filename of the `client.config` file.
5. Set the `$encryptionKey` variable to a strong encryption key for encrypting sensitive data.
6. Ensure that the log directory exists by running the code block responsible for creating it (`mkdir` function).
7. Include the necessary dependencies and classes in your PHP file:

```php
require_once 'path/to/KiaAPI.php';
require_once 'path/to/KiaLocationAPI.php';
require_once 'path/to/KiaCommandBuilder.php';
```

8. Initialize the KiaAPI class with your Kia owner account credentials:

```php
$api = new KiaAPI($username, $password);
```

## Usage

### Retrieving Vehicle Location

To retrieve the current location of your Kia vehicle, use the `getLocation` method of the `KiaLocationAPI` class:

```php
$locationAPI = new KiaLocationAPI($username, $password);
$location = $locationAPI->getLocation();

if ($location !== null) {
    $latitude = $location['latitude'];
    $longitude = $location['longitude'];
    // Use latitude and longitude as needed
} else {
    // Failed to retrieve the location
}
```

### Sending Remote Commands

To send remote commands to your Kia vehicle (e.g., lock/unlock doors, start climate control), use the `sendCommand` method of the `KiaAPI` class. The `KiaCommandBuilder` class provides a convenient way to build the command payload.

```php
$action = KiaCommandBuilder::buildAction($command, $temp, $defr);

try {
    $vinkey = $api->login();
    $response = $api->sendCommand($vinkey, $action);

    if (strpos($response, 'Success') !== false) {
        // Command executed successfully
    } else {
        // Command execution failed
    }
} catch (Exception $e) {
    // An error occurred
}
```

Replace `$command` with the desired command code (e.g., '1' for lock doors, '2' for unlock doors), and optionally provide the `$temp` and `$defr` parameters for climate control commands.

### Handling Rate Limits

The code includes functionality for checking and updating rate limits. The `checkRateLimit` method checks if the rate limit for a given user has been exceeded, and the `updateRateLimit` method updates the rate limit status after a command has been sent.

```php
$hash = hash('sha512', $username . $password);

if (!$api->checkRateLimit($hash)) {
    // Rate limit exceeded
} else {
    // Rate limit not exceeded
    // Send command and update rate limit
    $api->updateRateLimit($hash);
}
```

## Logging

The code provides basic logging functionality to record important events and errors in a log file. The log file is created in the specified `$logDirectory` with the filename `$logFilename` appended with the current date.

To write log messages, you can use the `writeToLog` function:

```php
writeToLog($message, $level);
```

- The `$message` parameter is the content of the log message.
- The `$level` parameter is optional and represents the log level (e.g., INFO, ERROR). It defaults to INFO if not specified.

## Encryption

Sensitive data, such as authentication tokens and cookies, are encrypted using AES-256-CBC encryption. The encryption key is provided in the `$encryptionKey` variable.

To encrypt and decrypt data, you can use the `encryptData` and `decryptData` functions:

```php
$encryptedData = encryptData($data);
$decryptedData = decryptData($encryptedData);
```

- The `$data` parameter is the data to be encrypted or decrypted.

## Running the Code

To use the code, follow these steps:

1. Ensure you have PHP installed on your server or local machine.
2. Copy the code to a PHP file (e.g., `kia-api.php`).
3. Modify the necessary variables and settings in the code, as explained earlier.
4. Include the required dependencies and classes in your PHP file using the appropriate paths.
5. Use the provided classes and methods to interact with the Kia API according to your needs.

Remember to handle exceptions appropriately and customize the code according to your application's requirements.

Please note that this code is intended as a starting point and may require modifications or enhancements based on your specific use case.
