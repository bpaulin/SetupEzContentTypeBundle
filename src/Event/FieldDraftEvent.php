<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

class FieldDraftEvent extends AbstractEvent
{
    protected $fieldName = null;
    protected $field = null;

    /**
     * @return null
     */
    public function getFieldDraft()
    {
        return $this->field;
    }

    /**
     * @param null $field
     */
    public function setFieldDraft($field)
    {
        $this->field = $field;
        return $this;
    }

    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }
}
