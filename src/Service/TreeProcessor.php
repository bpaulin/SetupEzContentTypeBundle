<?php

namespace Bpaulin\SetupEzContentTypeBundle\Service;

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
     * @param $type     array   child type
     * @param $extends  array   parent type
     * @return array    extended type
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
                        throw new \Exception( 'circular extends' );
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
        return $this->splitByGroup( $types );
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
