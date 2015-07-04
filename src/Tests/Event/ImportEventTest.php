<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Event;

use Bpaulin\SetupEzContentTypeBundle\Event\ImportEvent;

class ImportEventTest extends \PHPUnit_Framework_TestCase
{
    public function testObjectName()
    {
        $event = new ImportEvent();
        $this->assertSame(
            $event,
            $event->setObjectName( 'foo' )
        );
        $this->assertSame(
            'foo',
            $event->getObjectName()
        );
    }

    public function testStatus()
    {
        $event = new ImportEvent();
        $this->assertSame(
            $event,
            $event->setStatus( 'foo' )
        );
        $this->assertSame(
            'foo',
            $event->getStatus()
        );
    }

    public function testObject()
    {
        $event = new ImportEvent();
        $this->assertSame(
            $event,
            $event->setObject( 'foo' )
        );
        $this->assertSame(
            'foo',
            $event->getObject()
        );
    }
}
