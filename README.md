# reinvently/yii2-twilio

## Installation

#### Via composer:

```bash
$ composer require reinvently/yii2-twilio
```

## Configure

Add the following config to the `components` section to the `config/web.php`, `config/console.php`, etc.:

```php
...
    'twilio' => [
        'class' => 'Reinvently\Twilio\Twilio',
        'number' => 'XXXXXXXX', // phone number
        'retries' => 5, // call retries in fail cases
        'account' => [
            'sid' => 'ACXXXXXXXXXXXXXXXXXXXXXX',
            'token' => 'XXXXXXXXXXXXXXXXXXXXXXXX',
        ],
        'twiMLApp' => [
            'sid' => 'APXXXXXXXXXXXXXXXXXXXXXX',
        ],
        'apiKey' => [
            'sid' => 'SKXXXXXXXXXXXXXXXXXXXXXX',
            'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXX',
        ],
        'configurationProfile' => [
            'sid' => 'VSXXXXXXXXXXXXXXXXXXXXXX',
        ],
    ],
...
]
```

Replace the necessary fields with your own twilio account data, which you can find in twilio [control panel](https://www.twilio.com/console) 

## Reference

```php

```

