<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FieldStructureEvent extends AbstractEvent
{
    protected $fieldStructure = null;

    public function setFieldStructure($fieldStructure)
    {
        $this->fieldStructure = $fieldStructure;
        return $this;
    }

    public function getFieldStructure()
    {
        return $this->fieldStructure;
    }
}
