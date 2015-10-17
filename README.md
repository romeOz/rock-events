Implementation of Publisher-Subscriber for PHP
=================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-events/v/stable.svg)](https://packagist.org/packages/romeOz/rock-events)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-events/downloads.svg)](https://packagist.org/packages/romeOz/rock-events)
[![Build Status](https://travis-ci.org/romeOz/rock-events.svg?branch=master)](https://travis-ci.org/romeOz/rock-events)
[![HHVM Status](http://hhvm.h4cc.de/badge/romeoz/rock-events.svg)](http://hhvm.h4cc.de/package/romeoz/rock-events)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-events/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-events?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-events/license.svg)](https://packagist.org/packages/romeOz/rock-events)

Features
-------------------

 * Handler can be a closure, instance, and static class
 * Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)

Installation
-------------------

From the Command Line:

```
composer require romeoz/rock-events
```

In your composer.json:

```json
{
    "require": {
        "romeoz/rock-events": "*"
    }
}
```

Quick Start
-------------------

```php
use rock\events\Event;

class Foo 
{
    public $str = 'Rock!';
}

$object = new Foo();
$eventName = 'onAfter';
$handler = function (Event $event) {
    echo "Hello {$event->owner->str}"; 
};

Event::on($object, $eventName, $handler);

Event::trigger($object,  'onAfter'); // output: Hello Rock!
```

Documentation
-------------------

####on(string|object $class, string $name, callable $handler)

To subscribe to the event.

Set a handler can be as follows:

```php
$handler = function (Event $event) { 
    echo "Hello Rock!"; 
};
Event::on(new Foo, 'onAfter', $handler);
```

Options:

 * `function (Event $event) { ... }`
 * `[new Foo, 'method']`
 * `['Foo', 'static_method']`

####trigger(string|object $class, string $name, Event $event = null)

To publish event.

```php
Event::trigger(new Foo,  'onEvent'); 

// or

Event::trigger('test\Foo',  'onEvent');
```
    
####off(string|object $class, string $name, callable $handler = null)

Detach event.

```php
$handler = 
    function (Event $event) {
        echo 'Hello Rock!'
    };
$instance =  new Foo;
Event::on($instance, 'onAfter', $handler);

Event::off($instance, 'onAfter');
```

Requirements
-------------------
 * **PHP 5.4+**

License
-------------------

The Rock Events is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).