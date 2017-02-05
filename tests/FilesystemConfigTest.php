<?php

namespace Solution10\Config\Tests;

use PHPUnit\Framework\TestCase;
use Solution10\Config\FilesystemConfig;

class FilesystemConfigTest extends TestCase
{
    public function testConstructWithoutPaths()
    {
        $c = new FilesystemConfig();
        $this->assertNull($c->get('person'));
        $this->assertNull($c->get('person.name'));
        $this->assertNull($c->get('person.location.home'));
    }

    public function testConstructWithPaths()
    {
        $c = new FilesystemConfig([
            __DIR__.'/testconfig'
        ]);
        $this->assertEquals([
            'name' => 'Alex',
            'bike' => [
                'colour' => 'orange',
            ],
            'location' => [
                'home' => 'London',
                'work' => 'International Space Station',
            ],
        ], $c->get('person'));
        $this->assertEquals('Alex', $c->get('person.name'));
        $this->assertEquals('London', $c->get('person.location.home'));
    }

    /**
     * @expectedException           \Solution10\Config\Exception
     * @expectedExceptionMessage    Invalid or unreadable path:
     */
    public function testBadPath()
    {
        new FilesystemConfig([__DIR__.'/unknown']);
    }

    public function testSetGetEnvironment()
    {
        $c = new FilesystemConfig();
        $this->assertEquals('production', $c->getEnvironment());
        $this->assertEquals($c, $c->setEnvironment('development'));
        $this->assertEquals('development', $c->getEnvironment());
    }

    /*
     * ------------- Testing Basic Getting FilesystemConfig -----------------
     */

    public function testGetConfigSimple()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('Alex', $c->get('person.name'));
    }

    public function testGetConfigMultiArray()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('orange', $c->get('person.bike.colour'));
    }

    public function testGetConfigUnknownFile()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertNull($c->get('unknown.key'));
    }

    public function testGetConfigKnownFileUnknownKey()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertNull($c->get('person.unknownkey'));
    }

    public function testGetConfigUnknownFileDefault()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals(27, $c->get('unknown.key', 27));
    }

    public function testGetConfigUnknownKeyDefault()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('five', $c->get('person.unknown', 'five'));
    }

    public function testGetConfigFromTwoFiles()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('Alex', $c->get('person.name'));
        $this->assertEquals('Poochie', $c->get('pet.name'));
    }

    public function testGetSubsection()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals(array(
            'colour' => 'orange',
        ), $c->get('person.bike'));
    }

    public function testGetUnknownSubkey()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('Doodad', $c->get('person.name.unknown', 'Doodad'));
    }

    public function testGetUnknownSubkeys()
    {
        $c = new FilesystemConfig();
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertNull($c->get('person.name.unknown.unknown'));
    }

    /*
     * ---------------- Testing Overrides ------------------
     */

    public function testOverrideSimple()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('Tuey', $c->get('person.name'));
    }

    public function testOverridePreservesProduction()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('orange', $c->get('person.bike.colour'));
    }

    public function testOverrideSubArray()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('Tuey', $c->get('person.name'));
        $this->assertEquals('International Space Station', $c->get('person.location.work'));
        $this->assertEquals('Roseville, CA', $c->get('person.location.home'));
    }

    public function testOverrideNotPresentInEnvironment()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('Tuey', $c->get('person.name'));
        $this->assertEquals('Poochie', $c->get('pet.name'));
    }

    public function testOverrideNewValues()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals('Pilot', $c->get('person.job'));
        $this->assertEquals('Honda', $c->get('person.car.make'));
        $this->assertEquals('Civic', $c->get('person.car.model'));
    }

    public function testOverridesChangeType()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $value = $c->get('mixedtypes.key');
        $this->assertEquals(['value1', 'value2'], $value);
    }

    /*
     * ------------------ Testing Required Files -------------------------
     */

    public function testRequiredFilesBothPresent()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $files = $c->getRequiredFiles('person');
        $this->assertCount(2, $files);
        $this->assertEquals(realpath(__DIR__.'/testconfig/person.php'), $files[0]['path']);
        $this->assertEquals(realpath(__DIR__.'/testconfig/development/person.php'), $files[1]['path']);
    }

    public function testRequiredFilesOnlyProduction()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $files = $c->getRequiredFiles('pet');
        $this->assertCount(1, $files);
        $this->assertEquals(realpath(__DIR__.'/testconfig/pet.php'), $files[0]['path']);
    }

    public function testRequiredFilesOnlyEnvironment()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $files = $c->getRequiredFiles('debugging');
        $this->assertCount(1, $files);
        $this->assertEquals(realpath(__DIR__.'/testconfig/development/debugging.php'), $files[0]['path']);
    }

    public function testRequiredFilesNeitherPresent()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $files = $c->getRequiredFiles('building');
        $this->assertCount(0, $files);
    }

    /*
     * -------------- Testing Multiple Basepaths ------------------
     */

    public function testRequiredFilesMultipleBasepaths()
    {
        $c = (new FilesystemConfig())
            ->setEnvironment('development')
            ->addConfigPaths([
                __DIR__.'/testconfig',
                __DIR__.'/testconfig2',
            ]);

        $this->assertEquals([
            ['path' => realpath(__DIR__.'/testconfig/person.php'), 'environment' => null],
            ['path' => realpath(__DIR__.'/testconfig2/person.php'), 'environment' => null],
            ['path' => realpath(__DIR__.'/testconfig/development/person.php'), 'environment' => 'development'],
        ], $c->getRequiredFiles('person'));
    }

    public function testMultipleBasepathsConstructor()
    {
        $c = (new FilesystemConfig())
            ->setEnvironment('development')
            ->addConfigPaths([
                __DIR__.'/testconfig',
                __DIR__.'/testconfig2',
            ]);

        $this->assertEquals([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ], $c->getConfigPaths());

        // Make sure we can load files from both locations:
        $this->assertEquals('Hovercar', $c->get('car.make'));
        $this->assertEquals(true, $c->get('debugging.enabled'));
    }

    public function testMultipleBasepathsAddingLater()
    {
        $c = new FilesystemConfig();
        $c->setEnvironment('development');
        $c->addConfigPath(__DIR__.'/testconfig');
        $this->assertEquals($c, $c->addConfigPath(__DIR__.'/testconfig2'));

        $this->assertEquals([
            __DIR__.'/testconfig',
            __DIR__.'/testconfig2',
        ], $c->getConfigPaths());

        // Make sure we can load files from both locations:
        $this->assertEquals('Hovercar', $c->get('car.make'));
        $this->assertEquals(true, $c->get('debugging.enabled'));
    }

    public function testOverwritingOrder()
    {
        // Basepaths are evaluated as 'last set, first read'. So we can overwrite from a later base path.
        $c = (new FilesystemConfig())
            ->addConfigPaths([
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

        $c = (new FilesystemConfig())
            ->setEnvironment('development')
            ->addConfigPaths([
                __DIR__.'/testconfig',
                __DIR__.'/testconfig2',
            ]);

        // Name is overridden by everything
        $this->assertEquals('Tuey', $c->get('person.name'));
        // Job is only set in the development person config:
        $this->assertEquals('Pilot', $c->get('person.job'));
        // And the bike is only present in the first basepath person config:
        $this->assertEquals('orange', $c->get('person.bike.colour'));
    }
}
