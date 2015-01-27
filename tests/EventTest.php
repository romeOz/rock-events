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
            [
                function (Event $event) {
                    $this->assertTrue($event->owner instanceof Foo);
                    $this->assertSame($event->data['foo'], 'test');
                }, ['foo' => 'test']
            ]
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
            [
                function (Event $event) {
                    $event->handled = true;
                    echo $event->data['foo'];
                },
                ['foo' => 'test']
            ]
        );

        Event::on(
            Foo::className(),
            'foo',
            [
                function (Event $event) {
                    echo $event->data['foo'];
                },
                ['foo' => 'test']
            ]
        );

        Event::trigger(new Foo(), 'foo');
        $this->expectOutputString('test');
    }

    public function testTriggerChild()
    {
        Event::on(
            Foo::className(),
            'foo',
            [
                function (Event $event) {
                    $this->assertTrue($event->owner instanceof SubFoo);
                    $this->assertSame($event->data['foo'], 'test');
                }, ['foo' => 'test']
            ]
        );
        Event::on(
            SubFoo::className(),
            'foo',
            [
                function (Event $event) {
                    $this->assertTrue($event->owner instanceof SubFoo);
                    $this->assertSame($event->data['foo'], 'test');
                }, ['foo' => 'test']
            ]
        );
        Event::trigger(new SubFoo, 'foo');
    }

    public function testOnAsStaticMethod()
    {
        Event::on(
            Foo::className(),
            'bar',
            [[Foo::className(), 'display'], ['foo' => 'static']]
        );
        Event::trigger(Foo::className(), 'bar');
        $this->expectOutputString('static');
    }

    public function testOnAsInstance()
    {
        Event::on(
            Foo::className(),
            'bar',
            [[new Foo(), 'get'], ['foo' => 'instance']]
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
                echo $event->data['foo'];
            }
        );
        $this->assertTrue(Event::exists(Foo::className(), 'foo'));

        // false
        $this->assertFalse(Event::exists(Foo::className(), 'unknown'));
    }

    public function testMultiEventAndCount()
    {
        Event::on(
            Foo::className(),
            'foo',
            [
                function (Event $event) {
                    echo $event->data['foo'];
                }, ['foo' => 'test']
            ]
        );
        Event::on(Foo::className(), 'foo', [[Foo::className(), 'display'], ['foo' => 'static']]);
        Event::on(Foo::className(), 'foo', [[new Foo(), 'get'], ['foo' => 'instance']]);
        $this->assertSame(Event::count(), 1);
        $this->assertSame(Event::countHandlers(Foo::className(), 'foo'), 3);
        Event::trigger(Foo::className(), 'foo');
        $this->expectOutputString('teststaticinstancefoo');
    }

    public function testGetAll()
    {
        Event::on(
            Foo::className(),
            'foo',
            [
                function (Event $event) {
                    echo $event->data['foo'];
                },
                ['foo' => 'test']
            ]
        );
        Event::on(
            Foo::className(),
            'bar',
            [[Foo::className(), 'display'], ['foo' => 'static']]
        );
        Event::on(
            SubFoo::className(),
            'foo',
            [
                function (Event $event) {
                    echo $event->data['foo'];
                },
                ['foo' => 'test']
            ]
        );
        $this->assertSame(
            array_keys(Event::getAll()),
            array(
                Foo::className(),
                SubFoo::className(),
            )
        );
        $this->assertSame(
            array_keys(Event::getAll([SubFoo::className()])),
            array(
                0 => SubFoo::className(),
            )
        );
    }


    public function testOff()
    {
        Event::on(
            Foo::className(),
            'foo',
            [
                function (Event $event) {
                    echo $event->data['foo'];
                }, ['foo' => 'test']
            ]
        );
        $this->assertTrue(Event::exists(Foo::className(), 'foo'));
        Event::offMulti([[Foo::className(), 'foo']]);
        $this->assertFalse(Event::exists(Foo::className(), 'foo'));
    }

    public function testOffWithHandler()
    {
        $handler = [
            function (Event $event) {
                echo $event->data['foo'];
            }, ['foo' => 'test']
        ];
        Event::on(
            Foo::className(),
            'foo',
            $handler
        );
        Event::on(Foo::className(), 'foo', [[Foo::className(), 'display'], ['foo' => 'static']]);
        Event::on(Foo::className(), 'foo', [[new Foo(), 'get'], ['foo' => 'instance']]);
        $this->assertSame(Event::countHandlers(Foo::className(), 'foo'), 3);
        Event::off(Foo::className(), 'foo', $handler);
        $this->assertSame(Event::countHandlers(Foo::className(), 'foo'), 2);
    }
}

class Foo
{
    use ClassName;

    public static function display(Event $event)
    {
        echo $event->data['foo'];
    }

    public function get(Event $event)
    {

        echo $event->owner instanceof static, $event->data['foo'], $event->name;
    }
}

class SubFoo extends Foo
{

}