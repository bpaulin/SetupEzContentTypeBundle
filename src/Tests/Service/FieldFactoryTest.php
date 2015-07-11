<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Service;

use Bpaulin\SetupEzContentTypeBundle\Service\FieldFactory;

class FieldFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $factory = new FieldFactory();
        $this->assertInstanceOf(
            'Bpaulin\SetupEzContentTypeBundle\Service\FieldFactory',
            $factory
        );
    }
}
