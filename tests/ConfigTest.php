<?php

namespace Solution10\Config\Tests;

use PHPUnit_Framework_TestCase;
use Solution10\Config\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $this->assertInstanceOf('Solution10\\Config\\Config', $c);
        $this->assertEquals(__DIR__.'/testconfig', $c->basePath());
        $this->assertEquals('development', $c->environment());
    }

    public function testConstructDefaultEnv()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertInstanceOf('Solution10\\Config\\Config', $c);
        $this->assertEquals(__DIR__.'/testconfig', $c->basePath());
        $this->assertEquals('production', $c->environment());
    }

    /**
     * @expectedException       \Solution10\Config\Exception
     * @expectedExceptionCode   \Solution10\Config\Exception::INVALID_PATH
     */
    public function testConstructBadPath()
    {
        new Config('/this/path/does/not/exist');
    }

    /*
     * ------------- Testing Basic Getting Config -----------------
     */

    public function testGetConfigSimple()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertEquals('Alex', $c->get('person.name'));
    }

    public function testGetConfigMultiArray()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertEquals('orange', $c->get('person.bike.colour'));
    }

    public function testGetConfigUnknownFile()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertNull($c->get('unknown.key'));
    }

    public function testGetConfigKnownFileUnknownKey()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertNull($c->get('person.unknownkey'));
    }

    public function testGetConfigUnknownFileDefault()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertEquals(27, $c->get('unknown.key', 27));
    }

    public function testGetConfigUnknownKeyDefault()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertEquals('five', $c->get('person.unknown', 'five'));
    }

    public function testGetConfigFromTwoFiles()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertEquals('Alex', $c->get('person.name'));
        $this->assertEquals('Poochie', $c->get('pet.name'));
    }

    public function testGetSubsection()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertEquals(array(
            'colour' => 'orange',
        ), $c->get('person.bike'));
    }

    public function testGetUnknownSubkey()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertEquals('Doodad', $c->get('person.name.unknown', 'Doodad'));
    }

    public function testGetUnknownSubkeys()
    {
        $c = new Config(__DIR__.'/testconfig');
        $this->assertNull($c->get('person.name.unknown.unknown'));
    }

    /*
     * ---------------- Testing Overrides ------------------
     */

    public function testOverrideSimple()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $this->assertEquals('Tuey', $c->get('person.name'));
    }

    public function testOverridePreservesProduction()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $this->assertEquals('orange', $c->get('person.bike.colour'));
    }

    public function testOverrideSubArray()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $this->assertEquals('Tuey', $c->get('person.name'));
        $this->assertEquals('International Space Station', $c->get('person.location.work'));
        $this->assertEquals('Roseville, CA', $c->get('person.location.home'));
    }

    public function testOverrideNotPresentInEnvironment()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $this->assertEquals('Tuey', $c->get('person.name'));
        $this->assertEquals('Poochie', $c->get('pet.name'));
    }

    public function testOverrideNewValues()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $this->assertEquals('Pilot', $c->get('person.job'));
        $this->assertEquals('Honda', $c->get('person.car.make'));
        $this->assertEquals('Civic', $c->get('person.car.model'));
    }
}
