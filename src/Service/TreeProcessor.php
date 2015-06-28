<?php

namespace Bpaulin\SetupEzContentTypeBundle\Service;

use Bpaulin\SetupEzContentTypeBundle\Exception\CircularException;
use Bpaulin\SetupEzContentTypeBundle\Exception\FieldTypeNotImplementedException;
use Bpaulin\SetupEzContentTypeBundle\Exception\NoFieldsException;
use Bpaulin\SetupEzContentTypeBundle\Exception\NoNameForMainLanguageException;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class TreeProcessor
 *
 * Process raw config data and convert it to a clean array, ready to import
 *
 * @package Bpaulin\SetupEzContentTypeBundle\Service
 * @author bpaulin<brunopaulin@bpaulin.net>
 */
class TreeProcessor extends ContainerAware
{
    /**
     * Processed tree
     *
     * @var array
     */
    protected $tree;

    /**
     * extend a type with another
     *
     * @param   &$type    array       child type
     * @param   $extends  array       parent type
     * @return  array     extended    type
     */
    protected function mergeType( &$type, $extends )
    {
        $extendedType = $type + $extends;
        foreach ( $extendedType as $key => $value )
        {
            if ( is_array( $value ) && isset( $extends[ $key ] ) && is_array( $extends[ $key ] ) )
            {
                $this->mergeType( $value, $extends[$key] );
                $extendedType[$key] = $value + $extends[$key];
            }
        }
        $type = $extendedType;

        return $type;
    }

    /**
     * flatten groups in 1-dim array types
     *
     * @param  $groups array raw config data
     * @return array
     */
    protected function storeTypes( $groups )
    {
        $types = array();
        foreach ( $groups as $groupName => $group )
        {
            foreach ( $group as $typeName => $type )
            {
                $typeKey = $groupName.'.'.$typeName;
                $types[$typeKey] = $type;
            }
        }
        return $types;
    }

    /**
     * build a array for every fields with hierarchy
     *
     * @param  $types    array   types
     * @return array types hierarchy
     * @throws CircularException if a circular hierarchy is detected
     */
    protected function buildExtendsTree( $types )
    {
        $extends = array();
        foreach ( $types as $typeKey => $type )
        {
            $extends[$typeKey] = array();
            $typeExtends = $type;
            while ( true )
            {
                if ( isset( $typeExtends['extends'] ) && $typeExtends['extends'] )
                {
                    if ( in_array( $typeExtends['extends'], $extends[$typeKey] ) )
                    {
                        throw new CircularException();
                    }
                    $extends[$typeKey][] = $typeExtends['extends'];
                    $typeExtends = $types[$typeExtends['extends']];
                    continue;
                }
                break;
            }
        }
        return $extends;
    }

    /**
     * extends every types with his ancestor
     *
     * @param $types    array   types
     * @param $extends  array   types hierarchy
     * @return array types extended
     */
    protected function extendTypes( $types, $extends)
    {
        $extendedTypes = array();
        foreach ( array_keys( $types ) as $typeKey )
        {
            array_unshift( $extends[$typeKey], $typeKey );
            $extendedType = array();
            foreach ( $extends[$typeKey] as $extend )
            {
                $this->mergeType( $extendedType, $types[$extend] );
            }
            $extendedType['virtual'] = ( isset( $types[$typeKey]['virtual'] ) )? $types[$typeKey]['virtual']: false;
            $extendedTypes[$typeKey] = $extendedType;
        }
        return $extendedTypes;
    }

    /**
     * Split types by group
     *
     * @param $types array types
     * @return array    groups
     */
    protected function splitByGroup( $types )
    {
        $tree = array();
        foreach ( $types as $typeKey => $type )
        {
            if ( !$type['virtual'] )
            {
                unset( $type['virtual'] );
                unset( $type['extends'] );
                list( $group, $name ) = explode( '.', $typeKey );
                if ( !isset( $tree[$group] ) )
                {
                    $tree[$group] = array();
                }
                $tree[$group][$name] = $type;
            }
        }
        return $tree;
    }

    protected function checkTree( $groups )
    {
        foreach ( $groups as $group )
        {
            foreach ( $group as $typeName => $type )
            {
                if ( !array_key_exists( $type['mainLanguageCode'], $type['names'] ) )
                {
                    throw new NoNameForMainLanguageException( $typeName );
                }
                if ( empty( $type['fields'] ) || count( $type['fields'] ) == 0 )
                {
                    throw new NoFieldsException( $typeName );
                }
                foreach ( $type['fields'] as $fieldName => $field )
                {
                    if ( empty( $field['type'] ) || !in_array( $field['type'], array( 'ezstring' ) ) )
                    {
                        throw new FieldTypeNotImplementedException( $fieldName );
                    }
                }
            }
        }
    }

    /**
     * process groups to extend and clean types
     *
     * @param $groups   array   raw config data
     * @return array    array   processed tree
     * @throws \Exception if circular extends is detected
     */
    protected function processGroup( $groups )
    {
        $types = $this->storeTypes( $groups );
        $extends = $this->buildExtendsTree( $types );
        $types = $this->extendTypes( $types, $extends );
        $groups = $this->splitByGroup( $types );
        $this->checkTree( $groups );
        return $groups;
    }

    /**
     * process raw config data only once and return it
     *
     * @return array    processed tree
     * @throws \Exception if circular extends is detected
     */
    public function getTree( )
    {
        if ( !$this->tree )
        {
            $groups = $this->container->getParameter( 'bpaulin_setup_ez_content_type.groups' );
            $this->tree = $this->processGroup( $groups );
        }

        return $this->tree;
    }
}
