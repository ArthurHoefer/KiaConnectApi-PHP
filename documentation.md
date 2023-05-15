# Kia API Wrapper Library Documentation

This is a comprehensive documentation for the Kia API Wrapper Library, a PHP library that helps developers interact with Kia's API more conveniently.

## Disclaimer

This library is not officially associated with Kia. Please use it responsibly and consider Kia's terms of service. The library has only been tested within the United States. The use of this library outside the US has not been verified.

## Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Classes & Functions](#classes-functions)
5. [Testing](#testing)

## Requirements

- PHP 7.0
- OpenSSL PHP Extension
- cURL PHP Extension

## Installation

This library is provided as a PHP script that you can include in your projects. Download the PHP file and include it in your PHP script:

```php
require_once 'kia.php';
```

## Usage

### Initialization

To start using the library, you need to create an instance of the `KiaAPI` class. You will need your Kia account username and password.

```php
$kiaAPI = new KiaAPI($username, $password);
```

### Logging In

Before you can send commands or retrieve data, you need to log in:

```php
$vinKey = $kiaAPI->login();
```

### Sending Commands

You can send commands to the vehicle using the `sendCommand` method. This method requires the `vinKey` and an action string:

```php
$response = $kiaAPI->sendCommand($vinKey, $action);
```

## Classes & Functions

### `KiaAPI` Class

This is the main class of the library. It handles authentication, sending commands, and processing responses.

#### Methods

- `__construct($username, $password)`: Class constructor. Initializes the KiaAPI instance with the provided username and password.
- `sendRequest($url, $requestBody = "", $vinkey = "")`: Sends a HTTP request to the specified URL. Optionally takes a request body and a `vinkey`.
- `login($force = false)`: Logs in to the Kia API. If `force` is true, it forces a new login even if a previous session exists.
- `sendCommand($vinkey, $action)`: Sends a command to the vehicle. Requires the `vinkey` and the action string.
- Other helper methods for processing responses and handling rate limits.

### `KiaLocationAPI` Class

This class provides a simpler interface for getting the vehicle's location.

#### Methods

- `__construct($username, $password)`: Class constructor. Initializes the KiaLocationAPI instance with the provided username and password.
- `getLocation()`: Retrieves the vehicle's location.

### `KiaCommandBuilder` Class

This class provides utility methods for building command strings.

#### Static Methods

- `buildAction($command, $temp = null, $defr = null)`: Builds a command string based on the provided parameters.

### Global Functions

- `writeToLog($message, $level = 'INFO')`: Writes a message to the log file.
- `encryptData($data)`: Encrypts the provided data using AES-256-CBC encryption.
- `decryptData($encryptedData)`: Decrypts the provided data.

## Testing

Make sure to test all your calls and handle all exceptions properly. The library provides error messages that can help you diagnose and fix problems.

### Example

```php
try {
    $kiaAPI = new KiaAPI('myUsername', 'myPassword');
    $vinKey = $kiaAPI->login();
```php
    $response = $kiaAPI->sendCommand($vinKey, KiaCommandBuilder::buildAction('1'));
    print_r($response);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

This code will attempt to lock the vehicle and print the response. If an error occurs, it will print the error message.


This documentation is provided "as is" without warranty of any kind, either express or implied, including without limitation warranties of merchantability, fitness for a particular purpose, and non-infringement. Use of this documentation and the corresponding Kia API Wrapper Library is at your own risk.
