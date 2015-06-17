<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class GroupLoadingEvent extends AbstractEvent
{
    protected $groupName = null;
    protected $group = null;

    /**
     * @return null
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param null $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }
}
