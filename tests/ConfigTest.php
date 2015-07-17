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

    /*
     * ------------------ Testing Required Files -------------------------
     */

    public function testRequiredFilesBothPresent()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $files = $c->requiredFiles('person');
        $this->assertCount(2, $files);
        $this->assertEquals(realpath(__DIR__.'/testconfig/person.php'), $files[0]);
        $this->assertEquals(realpath(__DIR__.'/testconfig/development/person.php'), $files[1]);
    }

    public function testRequiredFilesOnlyProduction()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $files = $c->requiredFiles('pet');
        $this->assertCount(1, $files);
        $this->assertEquals(realpath(__DIR__.'/testconfig/pet.php'), $files[0]);
    }

    public function testRequiredFilesOnlyEnvironment()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $files = $c->requiredFiles('debugging');
        $this->assertCount(1, $files);
        $this->assertEquals(realpath(__DIR__.'/testconfig/development/debugging.php'), $files[0]);
    }

    public function testRequiredFilesNeitherPresent()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $files = $c->requiredFiles('building');
        $this->assertCount(0, $files);
    }

    /*
     * -------------- Testing Multiple Basepaths ------------------
     */

    public function testRequiredFilesMultipleBasepaths()
    {
        $c = new Config([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ], 'development');

        $this->assertEquals([
            realpath(__DIR__.'/testconfig/person.php'),
            realpath(__DIR__.'/testconfig2/person.php'),
            realpath(__DIR__.'/testconfig/development/person.php')
        ], $c->requiredFiles('person'));
    }

    public function testMultipleBasepathsConstructor()
    {
        $c = new Config([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ], 'development');

        $this->assertEquals([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ], $c->basePaths());

        // Make sure we can load files from both locations:
        $this->assertEquals('Hovercar', $c->get('car.make'));
        $this->assertEquals(true, $c->get('debugging.enabled'));
    }

    public function testMultipleBasepathsAddingLater()
    {
        $c = new Config(__DIR__.'/testconfig', 'development');
        $this->assertEquals($c, $c->addBasepath(__DIR__.'/testconfig2'));

        $this->assertEquals([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ], $c->basePaths());

        // Make sure we can load files from both locations:
        $this->assertEquals('Hovercar', $c->get('car.make'));
        $this->assertEquals(true, $c->get('debugging.enabled'));
    }

    public function testOverwritingOrder()
    {
        // Basepaths are evaluated as 'last set, first read'. So we can overwrite from a later base path.
        $c = new Config([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ]);

        // We should have overloaded the 'name' key from testconfig2/person.php but kept everything
        // else from testconfig/person.php
        $this->assertEquals('Alexander', $c->get('person.name'));
        $this->assertEquals('orange', $c->get('person.bike.colour'));
    }

    public function testOverwritingBetweenEnvsAndBasepaths()
    {
        // Easily the most complex case, the overwrites look like this:
        //
        //  {basepath-1}/person -> sets 'name'
        //  {basepath-2}/person -> overwrites 'name' (production always gets dibs)
        //  {basepath-1}/{env}/person -> overwrites 'name' and other keys
        //  {basepath-2}/{env}/person -> doesn't exist, no overwrite

        $c = new Config([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ], 'development');

        // Name is overridden by everything
        $this->assertEquals('Tuey', $c->get('person.name'));
        // Job is only set in the development person config:
        $this->assertEquals('Pilot', $c->get('person.job'));
        // And the bike is only present in the first basepath person config:
        $this->assertEquals('orange', $c->get('person.bike.colour'));
    }
}
