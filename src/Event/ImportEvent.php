<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ImportEvent extends Event
{
    protected $status = null;
    protected $objectName = null;
    protected $object = null;

    /**
     * @return null
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * @param null $name
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;
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
