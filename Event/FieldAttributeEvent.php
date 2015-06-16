<?php
/**
 * Created by PhpStorm.
 * User: bpaulin
 * Date: 16/06/15
 * Time: 21:24
 */

namespace Bpaulin\SetupEzContentTypeBundle\Event;

class FieldAttributeEvent extends AbstractEvent
{
    protected $oldValue;
    protected $newValue;
    protected $attributeName;

    /**
     * @return mixed
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * @param mixed $attributeName
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->valueToString( $this->oldValue );
    }

    /**
     * @param mixed $oldValue
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->valueToString( $this->newValue );
    }

    /**
     * @param mixed $newValue
     */
    public function setNewValue($newValue)
    {
        $this->newValue = $newValue;
    }

    protected function valueToString( $value )
    {
        if ( $value === true )
        {
            return 'true';
        }
        if ( $value === false )
        {
            return 'false';
        }
        return $value;
    }
}
