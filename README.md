# anviz-facepasspro-sdk
Anviz FacePass Pro PHP-SDK (firmware version 2.2.8)

Install
-------
```shell script
composer require yauhenko/anviz-facepasspro-sdk
```
Basic usage
-----------
```php
<?php

require 'vendor/autoload.php';

$facepass = new Yauhenko\Anviz\FacePassPro('192.168.1.195', 'password');
$records = $facepass->getIdentificationRecords('-1 week', 'today');

print_r($records);
```

Output
```
Array
(
    [0] => Array
        (
            [name] => Yauheni Kiryienkau
            [id] => 1
            [card] => 
            [dept] => IT
            [date] => 2019-11-13
            [time] => 16:59:50
            [verification_scores] => 61
            [status] => successful
        )
    ...
)
```

Methods
---
Get Identification Records
```
getIdentificationRecords(?string $from = 'today', ?string $till = null, ?int $id = null, ?string $cardno = null): array
```
