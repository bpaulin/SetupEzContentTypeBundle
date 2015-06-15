<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class TypeDraftEvent extends AbstractEvent
{
    protected $typeName = null;
    protected $type = null;

    /**
     * @return null
     */
    public function getTypeDraft()
    {
        return $this->type;
    }

    /**
     * @param null $type
     */
    public function setTypeDraft($type)
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
