<?php

namespace Solution10\Config\Tests;

use PHPUnit\Framework\TestCase;
use Solution10\Config\ArrayConfig;

class ArrayConfigTest extends TestCase
{
    public function testSimpleGet()
    {
        $c = new ArrayConfig([
            'person' => [
                'name' => 'Alex',
                'job' => 'Spaceman',
                'location' => [
                    'home' => 'London',
                    'work' => 'International Space Station'
                ]
            ]
        ]);

        $this->assertEquals([
            'name' => 'Alex',
            'job' => 'Spaceman',
            'location' => [
                'home' => 'London',
                'work' => 'International Space Station'
            ]
        ], $c->get('person'));

        $this->assertEquals('Alex', $c->get('person.name'));
        $this->assertEquals('London', $c->get('person.location.home'));
    }

    public function testGetDefault()
    {
        $c = new ArrayConfig();
        $this->assertNull($c->get('person.name'));
        $this->assertEquals('Geoff', $c->get('person.name', 'Geoff'));

        $c = new ArrayConfig([
            'person' => [
                'name' => 'Geoff'
            ]
        ]);
        $this->assertNull($c->get('person.location'));
        $this->assertEquals('Mars', $c->get('person.unknown.surname', 'Mars'));
    }

    public function testAddingConfig()
    {
        $c = new ArrayConfig();
        $c->addConfig([
            'person' => [
                'name' => 'Alex',
                'job' => 'Spaceman',
                'location' => [
                    'home' => 'London',
                    'work' => 'International Space Station'
                ]
            ]
        ]);

        $this->assertEquals([
            'name' => 'Alex',
            'job' => 'Spaceman',
            'location' => [
                'home' => 'London',
                'work' => 'International Space Station'
            ]
        ], $c->get('person'));

        $this->assertEquals('Alex', $c->get('person.name'));
        $this->assertEquals('London', $c->get('person.location.home'));
    }

    public function testAddingConfigEnvironment()
    {
        $c = new ArrayConfig([
            'person' => [
                'name' => 'Alex'
            ]
        ]);
        $c->setEnvironment('development');
        $this->assertEquals('Alex', $c->get('person.name'));

        $c->addConfig([
            'person' => [
                'name' => 'Becky'
            ]
        ], 'development');
        $this->assertEquals('Becky', $c->get('person.name'));
    }

    public function testConfigNotDestroyedWhenChangingEnvironment()
    {
        $c = new ArrayConfig([
            'person' => [
                'name' => 'Alex'
            ]
        ]);
        $c->setEnvironment('development');
        $c->addConfig([
            'person' => [
                'name' => 'Becky'
            ]
        ], 'development');
        $this->assertEquals('Becky', $c->get('person.name'));

        $c->setEnvironment('test');
        $this->assertEquals('Alex', $c->get('person.name'));
    }
}
