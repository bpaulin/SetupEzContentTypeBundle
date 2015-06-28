<?php
/**
 * Created by PhpStorm.
 * User: bpaulin
 * Date: 16/06/15
 * Time: 21:24
 */

namespace Bpaulin\SetupEzContentTypeBundle\Event;

class FieldAttributeEvent extends ImportEvent
{
    protected $oldValue;
    protected $newValue;

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @param mixed $oldValue
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @param mixed $newValue
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;
        return $this;
    }
}
