<?php
/**
 * Import Service
 */
namespace Bpaulin\SetupEzContentTypeBundle\Service;

use Bpaulin\SetupEzContentTypeBundle\Event\FieldAttributeEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\ImportEvent;
use Bpaulin\SetupEzContentTypeBundle\Events;
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

    protected $tree;

    /**
     * @return mixed
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * @param mixed $tree
     */
    public function setTree($tree)
    {
        $this->tree = $tree;
        return $this;
    }

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Return EzPublish ContentService
     *
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
     * Return Symfony Event Dispatcher
     *
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
     * Set Force mode
     *
     * @param $force boolean
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
    public function isForce()
    {
        return $this->force;
    }

    /**
     * Return group identified by groupName
     *
     * Create the group if force is enabled
     *
     * @param $groupName string
     * @return bool|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function getGroup( $groupName )
    {
        $event = new ImportEvent();
        $event->setObjectName( $groupName )
            ->setStatus( Events::STATUS_MISSING );

        $contentTypeGroup = false;
        try
        {
            $contentTypeGroup = $this->getContentTypeService()->loadContentTypeGroupByIdentifier( $groupName );
            $event->setStatus( Events::STATUS_LOADED );
        }
        catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e)
        {
            if ( $this->isForce() )
            {
                $contentTypeGroup = $this->getContentTypeService()->createContentTypeGroup(
                    $this->getContentTypeService()->newContentTypeGroupCreateStruct( $groupName )
                );
                $event->setStatus( Events::STATUS_CREATED );
            }
        }
        $event->setObject( $contentTypeGroup );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_GROUP_LOADING, $event
        );
        return $contentTypeGroup;
    }

    /**
     * Return type identified by typeName
     *
     * Create the type if force is enabled
     *
     * @param $typeName string
     * @return bool|\eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function getType ( $typeName )
    {
        $event = new ImportEvent();
        $event->setObjectName( $typeName )
            ->setStatus( Events::STATUS_MISSING );
        try
        {
            $contentType = $this->getContentTypeService()->loadContentTypeByIdentifier( $typeName );
            $event->setStatus( Events::STATUS_LOADED );
        }
        catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e)
        {
            $contentType = false;
        }
        $event->setObject( $contentType );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_TYPE_LOADING, $event
        );
        return $contentType;
    }

    /**
     * Return type draft for contentType
     *
     * Create the group if force is enabled
     *
     * @param $typeName string
     * @param $contentType bool|\eZ\Publish\API\Repository\Values\ContentType\ContentType
     * @return bool|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function getTypeDraft ( $typeName, $contentType )
    {
        $event = new ImportEvent();
        $event->setObjectName( $typeName )
            ->setStatus( Events::STATUS_MISSING );
        $contentTypeDraft = false;
        if ( $contentType )
        {
            try
            {
                $contentTypeDraft = $this->getContentTypeService()->loadContentTypeDraft( $contentType->id );
                $event->setStatus( Events::STATUS_LOADED );
            }
            catch ( \eZ\Publish\Core\Base\Exceptions\NotFoundException $e)
            {
                if ( $this->isForce() )
                {
                    $contentTypeDraft = $this->getContentTypeService()->createContentTypeDraft( $contentType );
                    $event->setStatus( Events::STATUS_CREATED );
                }
            }
        }
        $event->setObject( $contentTypeDraft );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_TYPE_DRAFT_LOADING, $event
        );
        return $contentTypeDraft;
    }

    /**
     * @param $typeDraft bool|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @param $typeName
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function getTypeStructure( $typeDraft, $typeName )
    {
        $event = new ImportEvent();

        $structure = ( $typeDraft ) ?
            $this->getContentTypeService()->newContentTypeUpdateStruct():
            $this->getContentTypeService()->newContentTypeCreateStruct( $typeName );
        $event->setStatus( ( $typeDraft ) ?  Events::STATUS_UPDATE_STRUCTURE : Events::STATUS_CREATE_STRUCTURE );

        $event->setObject( $structure );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_TYPE_STRUCTURE_LOADING, $event
        );
        return $structure;
    }

    /**
     * @param $typeStructure \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     * @param $typeData
     */
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
     * @param $typeDraft \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @return bool|\eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getField($fieldName, $typeDraft)
    {
        $event = new ImportEvent();
        $event->setObjectName( $fieldName );

        $fieldDraft = false;
        $event->setStatus( Events::STATUS_MISSING );
        if ( $typeDraft )
        {
            $fieldDraft = $typeDraft->getFieldDefinition( $fieldName );
            $event->setStatus( Events::STATUS_LOADED );
        }

        $event->setObject( $fieldDraft );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_FIELD_DRAFT_LOADING, $event
        );

        return $fieldDraft;
    }

    /**
     * @param $fieldDraft bool|\eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     * @param $fieldName
     * @param $fieldType
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct|\eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function getFieldStructure( $fieldDraft, $fieldName, $fieldType )
    {
        $event = new ImportEvent();

        $structure = ( $fieldDraft ) ?
            $this->getContentTypeService()->newFieldDefinitionUpdateStruct():
            $this->getContentTypeService()->newFieldDefinitionCreateStruct( $fieldName, $fieldType );
        $event->setStatus( ( $fieldDraft ) ?  Events::STATUS_UPDATE_STRUCTURE : Events::STATUS_CREATE_STRUCTURE );

        $event->setObject( $structure );

        $this->getEventDispatcher()->dispatch(
            Events::AFTER_FIELD_STRUCTURE_LOADING, $event
        );

        return $structure;
    }

    /**
     * @param $fieldDraft bool|\eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     * @param $fieldStructure \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct|\eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     * @param $fieldData
     */
    public function hydrateField($fieldDraft, $fieldStructure, $fieldData)
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
            $event->setOldValue( ( $fieldDraft )? $fieldDraft->$field: null );
            $event->setObjectName( $field );
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

    /**
     * @param $fieldDraft bool|\eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     * @param $fieldStructure \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct|\eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     * @param $typeDraft bool|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @param $typeStructure \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     * @return bool
     */
    public function addFieldToType($fieldDraft, $fieldStructure, $typeDraft, $typeStructure)
    {
        if ( !$this->isForce() )
        {
            return false;
        }

        if ( $typeDraft )
        {
            return $this->getContentTypeService()->updateFieldDefinition(
                $typeDraft,
                $typeDraft->getFieldDefinition( $fieldDraft->identifier ),
                $fieldStructure
            );
        }

        return $typeStructure->addFieldDefinition( $fieldStructure );
    }

    /**
     * @param $typeDraft bool|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @param $typeStructure \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     * @param $groupDraft bool|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     * @return bool
     */
    public function addTypeToGroup( $typeDraft, $typeStructure, $groupDraft )
    {
        if ( !$this->isForce() )
        {
            return false;
        }

        if ( $typeDraft )
        {
            $this->getContentTypeService()->updateContentTypeDraft( $typeDraft, $typeStructure );
            return $this->getContentTypeService()->publishContentTypeDraft( $typeDraft );
        }

        $typeDraft = $this->getContentTypeService()->createContentType(
            $typeStructure,
            array( $groupDraft )
        );
        return $this->getContentTypeService()->publishContentTypeDraft( $typeDraft );
    }

    public function countTypes()
    {
        $groups = $this->tree;
        $count = 0;
        foreach ( $groups as $group )
        {
            $count += count( $group );
        }
        return $count;
    }
}

