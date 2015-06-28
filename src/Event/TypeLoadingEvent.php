<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class TypeLoadingEvent extends AbstractEvent
{
    protected $typeName = null;
    protected $type = null;

    /**
     * @return null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param null $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setTypeName($typeName)
    {
        $this->typeName = $typeName;
        return $this;
    }

    public function getTypeName()
    {
        return $this->typeName;
    }
}
