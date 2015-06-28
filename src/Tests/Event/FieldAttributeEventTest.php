<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Event;

use Bpaulin\SetupEzContentTypeBundle\Event\FieldAttributeEvent;

class FieldAttributeEventTest extends \PHPUnit_Framework_TestCase
{
    public function testOldValue()
    {
        $event = new FieldAttributeEvent();
        $this->assertSame(
            $event,
            $event->setOldValue( 'foo' )
        );
        $this->assertSame(
            'foo',
            $event->getOldValue()
        );
    }

    public function testNewValue()
    {
        $event = new FieldAttributeEvent();
        $this->assertSame(
            $event,
            $event->setNewValue( 'foo' )
        );
        $this->assertSame(
            'foo',
            $event->getNewValue()
        );
    }
}
