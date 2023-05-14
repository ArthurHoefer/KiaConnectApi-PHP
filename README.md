# Kia API

This repository contains PHP code for interacting with the Kia API to perform various actions on a Kia vehicle, such as retrieving the vehicle location, locking/unlocking doors, and controlling the climate.

## Prerequisites

Before using this code, make sure you have the following:

- PHP installed on your server or local environment.
- The required PHP extensions: `curl` and `openssl`.

## Getting Started

1. Clone this repository to your local machine or server:

   ```
   git clone https://github.com/your-username/kia-api.git
   ```

2. Update the following variables in the code:

   - `$logDirectory`: The directory where log files will be stored.
   - `$configFile`: The file path for storing user configurations.
   - `$encryptionKey`: A secret key used for encrypting/decrypting sensitive data.

3. Create the log directory if it doesn't exist:

   ```shell
   mkdir logs/
   ```

## Usage

To use the Kia API, you can make GET requests to the PHP script with the required parameters.

### Available Commands

- `command`: The command to execute. Possible values are:
  - `1`: Lock Doors
  - `2`: Unlock Doors
  - `3`: Start Climate Control
  - `5`: Get Vehicle Status
  - `6`: Get Vehicle Location

### Example Request

```shell
GET /kia-api/api.php?username=your-username&password=your-password&command=1
```

### Response

The API will return the following responses:

- `successful`: The command was executed successfully.
- `rate_limit`: The rate limit for the user has been exceeded.

## Logging

The API logs various events to a log file. The log file is stored in the specified `$logDirectory` and has a name in the format `YYYY-MM-DD.log`.

## Security Considerations

When using the Kia API, it's important to consider security measures to protect sensitive information and prevent unauthorized access. Here are a few recommendations:

- **Keep the encryption key secure**: The `$encryptionKey` variable should be kept confidential and not shared or exposed in any way. Make sure to choose a strong encryption key and store it securely.

- **Protect user credentials**: Ensure that user credentials (username and password) are transmitted securely over HTTPS and not exposed in the URL or request headers.

- **Secure server environment**: If hosting the PHP script on a server, make sure it has proper security measures in place, such as firewall configurations, regular security updates, and restricted access to sensitive files.

- **Access control**: Implement access controls and user authentication mechanisms to restrict access to the API and its functionality. Only authorized users should be allowed to interact with the API.

- **Input validation**: Validate and sanitize user input to prevent potential security vulnerabilities such as SQL injection or cross-site scripting (XSS) attacks.


## Disclaimer

This project is not affiliated with or endorsed by Kia Motors. It is an independent implementation based on publicly available information.

Please note that using the Kia API may have limitations and potential risks. Make sure to comply with Kia's terms of service and use the API responsibly and within the boundaries defined by Kia.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.

---

Thank you for using the Kia API! If you have any further questions or need assistance, please don't hesitate to ask.
