<?php
namespace rockunit;

use rock\base\ClassName;
use rock\events\Event;

/**
 * @group base
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        static::tearDownAfterClass();
    }

    public static function tearDownAfterClass()
    {
        Event::offAll();
    }

    public function testOnAsCallback()
    {
        Event::trigger(new Foo(), 'foo');
        Event::on(
            Foo::className(),
            'foo',
            function (Event $event) {
                $this->assertTrue($event->owner instanceof Foo);
            }

        );
        Event::trigger(new Foo(), 'foo');

        // get
        $this->assertNotEmpty(Event::get(new Foo(), 'foo'));
        $this->assertEmpty(Event::get(new Foo(), 'unknown'));

        // count by class
        $this->assertSame(1, Event::countClass(new Foo()));

        // off by class
        Event::offClass(new Foo());
        $this->assertFalse(Event::exists(new Foo(), 'foo'));
    }

    public function testHandled()
    {
        Event::on(
            Foo::className(),
            'foo',
            function (Event $event) {
                $event->handled = true;
                echo 'test';
            }
        );

        Event::on(
            Foo::className(),
            'foo',
            function () {
                echo 'test';
            }

        );

        Event::trigger(new Foo(), 'foo');
        $this->expectOutputString('test');
    }

    public function testTriggerChild()
    {
        Event::on(
            Foo::className(),
            'foo',
            function (Event $event) {
                $this->assertTrue($event->owner instanceof SubFoo);
            }
        );
        Event::on(
            SubFoo::className(),
            'foo',
            function (Event $event) {
                $this->assertTrue($event->owner instanceof SubFoo);
            }
        );
        Event::trigger(new SubFoo, 'foo');
    }

    public function testOnAsStaticMethod()
    {
        Event::on(
            Foo::className(),
            'bar',
            [Foo::className(), 'display']
        );
        Event::trigger(Foo::className(), 'bar');
        $this->expectOutputString('static');
    }

    public function testOnAsInstance()
    {
        Event::on(
            Foo::className(),
            'bar',
            [new Foo(), 'get']
        );
        Event::trigger(new Foo, 'bar');
        $this->expectOutputString('1instancebar');
    }

    public function testExists()
    {
        // true
        Event::on(
            Foo::className(),
            'foo',
            function (Event $event) {
            }
        );
        $this->assertTrue(Event::exists(Foo::className(), 'foo'));

        // false
        $this->assertFalse(Event::exists(Foo::className(), 'unknown'));
    }

    public function testMultiEventAndCount()
    {
        $class = Foo::className();
        $eventName = 'foo';
        $handler =
            function () {
                echo 'closure';
            }
        ;
        Event::on($class, $eventName, $handler);
        Event::on(Foo::className(), 'foo', [Foo::className(), 'display']);
        Event::on(Foo::className(), 'foo', [new Foo(), 'get']);
        $this->assertSame(Event::count(), 1);
        $this->assertSame(Event::countHandlers(Foo::className(), 'foo'), 3);
        Event::trigger(Foo::className(), 'foo');
        $this->expectOutputString('closurestaticinstancefoo');
    }

    public function testGetAll()
    {
        Event::on(
            Foo::className(),
            'foo',
            function () {
                echo 'closure';
            }
        );
        Event::on(
            Foo::className(),
            'bar',
            [Foo::className(), 'display']
        );
        Event::on(
            SubFoo::className(),
            'foo',
            function (Event $event) {
                echo 'closure';
            }
        );
        $this->assertSame(
            array_keys(Event::getAll()),
            array(
                Foo::className(),
                SubFoo::className(),
            )
        );
        $this->assertSame(
            [
                SubFoo::className(),
            ],
            array_keys(Event::getAll([SubFoo::className()]))
        );
    }


    public function testOff()
    {
        Event::on(
            Foo::className(),
            'foo',
            function () {
                echo 'closure';
            }

        );
        $this->assertTrue(Event::exists(Foo::className(), 'foo'));
        Event::offMulti([[Foo::className(), 'foo']]);
        $this->assertFalse(Event::exists(Foo::className(), 'foo'));
    }

    public function testOffWithHandler()
    {
        $handler =
            function () {
                echo 'closure';
            }
        ;
        Event::on(
            Foo::className(),
            'foo',
            $handler
        );
        Event::on(Foo::className(), 'foo', [Foo::className(), 'display']);
        Event::on(Foo::className(), 'foo', [new Foo(), 'get']);
        $this->assertSame(Event::countHandlers(Foo::className(), 'foo'), 3);
        Event::off(Foo::className(), 'foo', $handler);
        $this->assertSame(Event::countHandlers(Foo::className(), 'foo'), 2);
    }
}

class Foo
{
    use ClassName;

    public static function display()
    {
        echo 'static';
    }

    public function get(Event $event)
    {

        echo $event->owner instanceof static, 'instance', $event->name;
    }
}

class SubFoo extends Foo
{

}