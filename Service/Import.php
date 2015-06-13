<?php

namespace Bpaulin\SetupEzContentTypeBundle\Service;

use eZ\Publish\Core\REST\Client\ContentTypeService;
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
        $contentTypeGroup = false;
        try
        {
            $contentTypeGroup = $this->getContentTypeService()->loadContentTypeGroupByIdentifier( $groupName );
        }
        catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e)
        {
            if ( $this->getForce() )
            {
                $contentTypeGroup = $this->getContentTypeService()->createContentTypeGroup(
                    $this->getContentTypeService()->newContentTypeGroupCreateStruct( $groupName )
                );
            }
        }
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

