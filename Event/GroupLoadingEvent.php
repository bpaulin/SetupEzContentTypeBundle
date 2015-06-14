<?php
namespace Bpaulin\SetupEzContentTypeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class GroupLoadingEvent extends Event
{
    protected $groupName = null;
    protected $group = null;
    protected $status = null;

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
