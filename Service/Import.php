<?php

namespace Bpaulin\SetupEzContentTypeBundle\Service;

use Bpaulin\SetupEzContentTypeBundle\Event\GroupLoadingEvent;
use Bpaulin\SetupEzContentTypeBundle\Events;
use eZ\Publish\Core\REST\Client\ContentTypeService;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class Import
 *
 * Import content types, groups and fields
 *
 * @package Bpaulin\SetupEzContentTypeBundle\Service
 * @author bpaulin<brunopaulin@bpaulin.net>
 */
class Import extends ContainerAware
{
    /**
     * @var boolean
     */
    protected $force;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    protected function getContentTypeService()
    {
        if ( !$this->contentTypeService )
        {
            $this->contentTypeService = $this->container->get( 'ezpublish.api.repository' )->getContentTypeService();
        }
        return $this->contentTypeService;
    }

    public function setForce( $force )
    {
        $this->force = $force;
    }

    public function getForce()
    {
        return $this->force;
    }

    public function getGroupDraft( $groupName )
    {
        $event = new GroupLoadingEvent();
        $event->setGroupName( $groupName )
            ->setStatus( Events::STATUS_MISSING );

        $contentTypeGroup = false;
        try
        {
            $contentTypeGroup = $this->getContentTypeService()->loadContentTypeGroupByIdentifier( $groupName );
            $event->setStatus( Events::STATUS_LOADED );
        }
        catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e)
        {
            if ( $this->getForce() )
            {
                $contentTypeGroup = $this->getContentTypeService()->createContentTypeGroup(
                    $this->getContentTypeService()->newContentTypeGroupCreateStruct( $groupName )
                );
                $event->setStatus( Events::STATUS_CREATED );
            }
        }
        $event->setGroup( $contentTypeGroup );

        $this->container->get( "event_dispatcher" )->dispatch(
            Events::AFTER_GROUP_LOADING, $event
        );
        return $contentTypeGroup;
    }

    public function getTypeDraft($typeName)
    {
        return false;
    }

    public function hydrateType($typeDraft, $typeData)
    {
        return false;
    }

    public function getFieldDraft($fieldName)
    {
        return false;
    }

    public function hydrateField($fieldDraft, $fieldData)
    {
        return false;
    }

    public function addFieldToType($fieldDraft, $typeDraft)
    {
        return false;
    }

    public function addTypeToGroup($typeDraft, $groupDraft)
    {
        return false;
    }
}

