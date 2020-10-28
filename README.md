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
print_r($response->toArray());
/*
array(
    "activeCookies" => string[][],
    "cookies" => string[][],
    "code" => number,
    "headers" => string[],
    "error" => string,
    "timing" => string,
    "body" => string
)
*/
```

### Author

Ignatios Drakoulas - <ignatisnb@gmail.com> - <https://twitter.com/ignatisd><br />

### License

CurlRequest is licensed under the MIT License - see the [LICENSE](LICENSE) file for details
