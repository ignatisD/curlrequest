# CurlRequest - A curl helper


## Installation

Install the latest version with

```bash
$ composer require iggi/curlrequest
```

## Basic Usage

```php
<?php

use Iggi\CurlRequest;

$curlRequest = new CurlRequest();
$response = $curlRequest->get("https://ignatisd.gr/hello")->exec();
print_r($response);
/*
array(
    "activeCookies" => string[][],
    "body" => string,
    "code" => number,
    "cookies" => string[][],
    "header" => string,
    "error" => string,
    "timing" => string,
    "request" => array(
         "method" => string,
         "url" => string,
         "headers" => string[],
         "body" => string,
         "proxy" => string
    )
)
*/
```

### Author

Ignatios Drakoulas - <ignatisnb@gmail.com> - <https://twitter.com/ignatisd><br />

### License

CurlRequest is licensed under the MIT License - see the [LICENSE](LICENSE) file for details
