<?php

namespace Bpaulin\SetupEzContentTypeBundle\Service;

use Bpaulin\SetupEzContentTypeBundle\Event\FieldAttributeEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\FieldDraftEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\FieldStructureEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\GroupLoadingEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\TypeDraftEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\TypeStructureEvent;
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

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    protected function getContentTypeService()
    {
        if ( !$this->contentTypeService )
        {
            $this->contentTypeService = $this->container->get( 'ezpublish.api.repository' )->getContentTypeService();
        }
        return $this->contentTypeService;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher|\Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected function getEventDispatcher()
    {
        if ( !$this->eventDispatcher )
        {
            $this->eventDispatcher = $this->container->get( 'event_dispatcher' );
        }
        return $this->eventDispatcher;
    }

    /**
     * @param $force
     * @return $this
     */
    public function setForce( $force )
    {
        $this->force = $force;
        return $this;
    }

    /**
     * @return bool
     */
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

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_GROUP_LOADING, $event
        );
        return $contentTypeGroup;
    }

    public function getTypeDraft ($typeName )
    {
        $event = new TypeDraftEvent();
        $event->setTypeName( $typeName )
            ->setStatus( Events::STATUS_MISSING );
        try
        {
            $contentType = $this->getContentTypeService()->loadContentTypeByIdentifier( $typeName );
            try
            {
                $contentType = $this->getContentTypeService()->createContentTypeDraft( $contentType );
                $event->setStatus( Events::STATUS_CREATED );
            }
            catch (\eZ\Publish\Core\Base\Exceptions\BadStateException $e)
            {
                $contentType = $this->getContentTypeService()->loadContentTypeDraft( $contentType->id );
                $event->setStatus( Events::STATUS_LOADED );
            }
        }
        catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e)
        {
            $contentType = false;
        }
        $event->setTypeDraft( $contentType );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_TYPE_DRAFT_LOADING, $event
        );
        return $contentType;
    }

    public function getTypeStructure( $typeDraft, $typeName )
    {
        $event = new TypeStructureEvent();
        if ( $typeDraft )
        {
            $structure = $this->getContentTypeService()->newContentTypeUpdateStruct();
            $event->setStatus( Events::STATUS_UPDATE_STRUCTURE );
        }
        else
        {
            $structure = $this->getContentTypeService()->newContentTypeCreateStruct( $typeName );
            $event->setStatus( Events::STATUS_CREATE_STRUCTURE );
        }
        $event->setTypeStructure( $structure );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_TYPE_STRUCTURE_LOADING, $event
        );
        return $structure;
    }

    public function hydrateType($typeStructure, $typeData)
    {
        // type data
        $typeStructure->mainLanguageCode = str_replace( '_', '-', $typeData['mainLanguageCode'] );
        $names = array();
        foreach ( $typeData['names'] as $key => $value )
        {
            $names[str_replace( '_', '-', $key )] = $value;
        }
        $typeStructure->names = $names;

        if ( isset( $typeData['nameSchema'] ) )
        {
            $typeStructure->nameSchema = $typeData['nameSchema'];
        }
        if ( isset( $typeData['descriptions'] ) )
        {
            $descriptions = array();
            foreach ( $typeData['descriptions'] as $key => $value )
            {
                $descriptions[str_replace( '_', '-', $key )] = $value;
            }
            $typeStructure->descriptions = $descriptions;
        }
    }

    /**
     * @param $fieldName
     * @param $fieldType
     * @param $typeDraft \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @return mixed
     */
    public function getFieldDraft($fieldName, $typeDraft)
    {
        $event = new FieldDraftEvent();
        $event->setFieldName( $fieldName );

        $fieldDraft = null;
        $event->setStatus( Events::STATUS_MISSING );
        if ( $typeDraft )
        {
            $fieldDraft = $typeDraft->getFieldDefinition( $fieldName );
            $event->setStatus( Events::STATUS_LOADED );
        }

        $event->setFieldDraft( $fieldDraft );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_FIELD_DRAFT_LOADING, $event
        );

        return $fieldDraft;
    }

    public function getFieldStructure( $fieldDraft, $fieldName, $fieldType )
    {
        $event = new FieldStructureEvent();
        if ( $fieldDraft )
        {
            $structure = $this->getContentTypeService()->newFieldDefinitionUpdateStruct();
            $event->setStatus( Events::STATUS_UPDATE_STRUCTURE );
        }
        else
        {
            $structure = $this->getContentTypeService()->newFieldDefinitionCreateStruct( $fieldName, $fieldType );
            $event->setFieldStructure( Events::STATUS_CREATE_STRUCTURE );
        }
        $event->setFieldStructure( $structure );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_FIELD_STRUCTURE_LOADING, $event
        );

        return $structure;
    }

    public function hydrateField($fieldStructure, $fieldData)
    {
        $fields = array(
            'position',
            'isTranslatable',
            'isRequired',
            'isSearchable',
            'fieldGroup'
        );
        foreach ( $fields as $field )
        {
            $event = new FieldAttributeEvent();
            $event->setOldValue( $fieldStructure->$field );
            $event->setAttributeName( $field );
            if ( isset( $fieldData[$field] ) )
            {
                $fieldStructure->$field = $fieldData[$field];
                $event->setNewValue( $fieldStructure->$field );
            }
            $this->getEventDispatcher()->dispatch(
                Events::AFTER_FIELD_ATTRIBUTE_LOADING, $event
            );
        }
        $fieldsArray = array(
            'names',
            'description'
        );
        foreach ( $fieldsArray as $field )
        {
            if ( isset( $fieldData[$field] ) )
            {
                $array = array();
                foreach ( $fieldData[$field] as $key => $value )
                {
                    $array[str_replace( '_', '-', $key )] = $value;
                }
                $fieldStructure->$field = $array;
            }
        }
    }

    public function addFieldToType($fieldDraft, $fieldStructure, $typeDraft, $typeStructure)
    {
        if ( $typeDraft )
        {
            $this->getContentTypeService()->updateFieldDefinition(
                $typeDraft,
                $typeDraft->getFieldDefinition( $fieldDraft->identifier ),
                $fieldStructure
            );
        }
        else
        {
            $typeStructure->addFieldDefinition( $fieldStructure );
        }
    }

    public function addTypeToGroup( $typeDraft, $typeStructure, $groupDraft )
    {
        if ( $typeDraft )
        {
            $this->getContentTypeService()->updateContentTypeDraft( $typeDraft, $typeStructure );
            $this->getContentTypeService()->publishContentTypeDraft( $typeDraft );
        }
        else
        {
            $typeDraft = $this->getContentTypeService()->createContentType(
                $typeStructure,
                array( $groupDraft )
            );
            $this->getContentTypeService()->publishContentTypeDraft( $typeDraft );
        }
    }

}
