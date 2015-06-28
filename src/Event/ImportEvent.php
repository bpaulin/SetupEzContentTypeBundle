<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ImportEvent extends Event
{
    protected $status = null;
    protected $name = null;
    protected $object = null;

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return null
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param null $object
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param null $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}
