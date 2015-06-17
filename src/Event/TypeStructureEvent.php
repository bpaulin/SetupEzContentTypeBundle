<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class TypeStructureEvent extends AbstractEvent
{
    protected $typeStructure = null;

    public function setTypeStructure($typeStructure)
    {
        $this->typeStructure = $typeStructure;
        return $this;
    }

    public function getTypeStructure()
    {
        return $this->typeStructure;
    }
}
