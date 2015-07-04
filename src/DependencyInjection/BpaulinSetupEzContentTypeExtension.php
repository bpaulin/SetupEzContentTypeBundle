<?php

namespace Bpaulin\SetupEzContentTypeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Bpaulin\SetupEzContentTypeBundle\Exception\CircularExtendsException;
use Bpaulin\SetupEzContentTypeBundle\Exception\FieldTypeNotImplementedException;
use Bpaulin\SetupEzContentTypeBundle\Exception\NoFieldsException;
use Bpaulin\SetupEzContentTypeBundle\Exception\NoNameForMainLanguageException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BpaulinSetupEzContentTypeExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );
        $container->setParameter( 'bpaulin_setup_ez_content_type.groups', $this->processGroup( $config['groups'] ) );

        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'services.yml' );
    }

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
                        throw new CircularExtendsException( $typeKey );
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
}
