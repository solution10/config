<?php

namespace Solution10\Config\Tests\Common;

use PHPUnit\Framework\TestCase;
use Solution10\Config\Common\Environment;

class EnvironmentTest extends TestCase
{
    public function testGetSetEnvironment()
    {
        $e = new class {
            use Environment;
        };
        $this->assertEquals('production', $e->getEnvironment());
        $this->assertEquals($e, $e->setEnvironment('test'));
        $this->assertEquals('test', $e->getEnvironment());
    }
}
