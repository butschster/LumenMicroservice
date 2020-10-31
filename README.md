# Lumen Microservice
[![Build Status](https://travis-ci.org/butschster/LumenMicroservice.svg)](https://travis-ci.org/butschster/LumenMicroservice) [![Latest Stable Version](https://poser.pugx.org/butschster/lumen-microservice/v/stable)](https://packagist.org/packages/butschster/lumen-microservice) [![Total Downloads](https://poser.pugx.org/butschster/lumen-microservice/downloads)](https://packagist.org/packages/butschster/lumen-microservice) [![License](https://poser.pugx.org/butschster/lumen-microservice/license)](https://packagist.org/packages/butschster/lumen-microservice)
![heading](https://user-images.githubusercontent.com/773481/96422465-b6bbe800-1200-11eb-914a-8c2c150d80eb.jpg)

This Package can help you to use Lumen framework or Laravel as a microservice without extra knowledge. 
Just install package, create Exchange Point class and start exchanging information between services through MQ.

This package uses [JMS\Serializer](https://github.com/schmittjoh/serializer) for message serialization.

*P.S. You can use this service for testing https://www.cloudamqp.com. They have a free plan.*

## Features
- Easy to use out of the box
- Easy to configure
- jms serializer
- Uses MQ out of the box
- Well tested

## Requirements
- Lumen or Laravel 7.x to 8.x
- PHP 7.4 and above
   
## Installation and Configuration
From the command line run

`composer require butschster/lumen-microservice`

#### Register Service provider
```php
// bootstrap.php
$app->register(Butschster\Exchanger\Providers\ExchangeServiceProvider::class);
```

#### Copy config files from package `config` directory to your Lumen app and register them.

```php
$app->configure('amqp');
$app->configure('microservice');
$app->configure('serializer');
```

#### Add variables to your .env file
```
MICROSERVICE_NAME=...
MICROSERVICE_VERSION=1.0.0
RABBITMQ_HOST=...
RABBITMQ_USERNAME=...
RABBITMQ_PASSWORD=...
RABBITMQ_VHOST=...
JWT_SECRET=...
```

#### Create exchange point in your app
This point will use for receiving request from other services and for sending responses.

```php
namespace App\Exchange\Point;

use Butschster\Exchanger\Contracts\Exchange\Point as PointContract;
use Psr\Log\LoggerInterface;
use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;

class Point implements PointContract
{
    public function getName(): string
    {
        return 'com.test';
    }

    /**
     * @subject com.test.action.test
     */
    public function testSubject(IncomingRequest $request, LoggerInterface $logger)
    {
        $logger->info(
            sprintf('info [%s]:', $request->getSubject()),
            [$request->getBody()]
        );

        // Response
        $payload = new ...;
        $request->sendResponse($payload);

        // or
        $request->sendEmptyResponse();

        // or
        $request->acknowledge();
    }

    /**
     * @subject com.test.action.test1
     */
    public function anotherTestSubject(IncomingRequest $request, LoggerInterface $logger)
    {
        $payload = new ...;
        $request->sendResponse($payload);
    }
}
```

Then register this point

```php
use App\Exchange\Point;
use Illuminate\Support\ServiceProvider;
use Butschster\Exchanger\Contracts\Exchange\Point as PointContract;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PointContract::class, Point::class);
    }
}
```

#### Register console command
```php
use Butschster\Exchanger\Console\Commands\RunMicroservice;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        RunMicroservice::class
    ];
}
```

#### Sending requests
For requesting information from another service you should use `ExchangeManager`

```php
use Butschster\Exchanger\Contracts\ExchangeManager;

class UserController {

    public function getUser(ExchangeManager $manager, int $userId)
    {
        $requestPayload = new GetUserByIdPayload($userId);

        $request = $manager->request('com.test.action.test', $requestPayload);
        $user = $request->send(UserPayload::class);

        // You can set delivery mode to persistent
        $user = $request->send(UserPayload::class, true);
    }
}

// User Request Payload

use JMS\Serializer\Annotation\Type;

class GetUserByIdPayload implements \Butschster\Exchanger\Contracts\Exchange\Payload
{
    /** @Type("string") */
    public string $userId;
    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }
}

// User Payload

use JMS\Serializer\Annotation\Type;

class UserPayload implements \Butschster\Exchanger\Contracts\Exchange\Payload
{
    /** @Type("string") */
    public string $id;

    /** @Type("string") */
    public string $username;

    /** @Type("string") */
    public string $email;

    /** @Type("Carbon\Carbon") */
    public \Carbon\Carbon $createdAt;
}
```

#### Broadcasting
If you want to send an event you should use `ExchangeManager` method `broadcast`

```php
use Illuminate\Http\Request;
use Butschster\Exchanger\Contracts\ExchangeManager;

class UserController {

    public function update(ExchangeManager $manager, Request $request, User $user)
    {
        $payload = new UserPayload();
        $data = $request->validate(...);
        $user->update($data);

        $payload->id = $user->id;
        $payload->username = $user->username;
        ...
        
        $manager->broadcast('com.user.event.updated', $payload);

        // You can set delivery mode to persistent
        $manager->broadcast('com.user.event.updated', $payload, true);
    }
}
```

#### Entity mapping
You can configure entity mapping in `serializer.php` config.

```php
// serializer.php
return [
    'mapping' => [
        Domain\User\Entities\User::class => [
            'to' => Payloads\User::class,
            'attributes' => [
                'id' => ['type' => 'string'],
                'username' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'balance' => ['type' => Domain\Billing\Money\Token::class],
                'createdAt' => ['type' => \Carbon\Carbon::class],
            ]
        ],
        Domain\Billing\Money\Token::class => [
            'to' => Payloads\Billing\Token::class,
            'attributes' => [
                'amount' => ['type' => \Brick\Math\BigDecimal::class],
            ]
        ],
    ],
    // ...
];
```

And then you can easily convert entities to payloads

```php
use Butschster\Exchanger\Contracts\Serializer\ObjectsMapper;

class UserController {

    public function update(ObjectsMapper $mapper, ExchangeManager $manager, Request $request, Domain\User\Entities\User $user)
    {
        $data = $request->validate(...);
        $user->update($data);
        
        $payload = $mapper->toPayload($user);
        
        $manager->broadcast('com.user.event.updated', $payload);
    }
}
```

#### Custom types
If you want to use custom JMS Serializer types you should use handlers. They can be added in `serializer.php` config.

```php
// serializer.php
return [
    'handlers' => [
        Butschster\Exchanger\Jms\Handlers\CarbonHandler::class,
        Infrastructure\Microservice\Jms\Handlers\BigDecimalHandler::class
    ],
    // ..
];

// BigDecimalHandler.php

namespace Infrastructure\Microservice\Jms\Handlers;

use Brick\Math\BigDecimal;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Contracts\Serializer\Handler;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;

class BigDecimalHandler implements Handler
{
    public function serialize(Serializer $serializer, HandlerRegistryInterface $registry): void
    {
        $registry->registerHandler(
            GraphNavigatorInterface::DIRECTION_SERIALIZATION,
            BigDecimal::class,
            'json',
            function ($visitor, BigDecimal $value, array $type) {
                return $value->toInt();
            }
        );
    }

    public function deserialize(Serializer $serializer, HandlerRegistryInterface $registry): void
    {
        $registry->registerHandler(
            GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
            BigDecimal::class,
            'json',
            function ($visitor, $value, array $type) {
                return BigDecimal::of($value);
            }
        );
    }
}
```

#### Service running
This single command will run your microservice and start listening to commands registered in your exchange point.  

`php artisan service:run`

Supervisor is a process monitor for the Linux operating system, and will automatically restart your horizon process if it fails.
To install Supervisor on Ubuntu, you may use the following command:

`sudo apt-get install supervisor`

Supervisor configuration files are typically stored in the /etc/supervisor/conf.d directory. 
Within this directory, you may create any number of configuration files that instruct supervisor how your processes should be monitored.
For example, let's create a microservice.conf file that starts and monitors a process:

```
[program:microservice]
process_name=%(program_name)s
command=php /var/www/app.com/artisan service:run
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/www/app.com/storage/logs/microservice.log
stopwaitsecs=3600
```

Once the configuration file has been created, you may update the Supervisor configuration and start the processes using the following commands:

```
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start microservice
```
