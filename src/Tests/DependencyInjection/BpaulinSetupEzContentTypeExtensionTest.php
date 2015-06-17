<?php
/**
 * Created by PhpStorm.
 * User: bpaulin
 * Date: 12/06/15
 * Time: 21:56
 */

namespace Bpaulin\SetupEzContentTypeBundle\Tests\DependencyInjection;

use Bpaulin\SetupEzContentTypeBundle\DependencyInjection\BpaulinSetupEzContentTypeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class BpaulinSetupEzContentTypeExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BpaulinSetupEzContentTypeExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->extension = new BpaulinSetupEzContentTypeExtension();

        $this->container = new ContainerBuilder();
        $this->container->registerExtension( $this->extension );
    }

    public function testWithoutConfiguration()
    {
        $this->setExpectedException( 'Exception' );

        $this->container->loadFromExtension( $this->extension->getAlias() );
        $this->container->compile();
    }

    protected function loadConfig( $file )
    {
        $loader = new YamlFileLoader( $this->container, new FileLocator( __DIR__.'/Fixtures/Yaml/' ) );
        $loader->load( $file );
        $this->container->compile();
    }

    public function testWithConfiguration()
    {
        $this->loadConfig( 'sample.yml' );

        $this->assertArrayHasKey(
            'group1',
            $this->container->getParameter( 'bpaulin_setup_ez_content_type.groups' )
        );
    }

    public function testTreeProcessorServiceIsAvailable()
    {
        $this->loadConfig( 'sample.yml' );

        $this->assertTrue(
            $this->container->has( 'bpaulin.setupezcontenttype.treeprocessor' ),
            "The treeprocessor service isn't available"
        );
        $this->assertEquals(
            get_class( $this->container->get( 'bpaulin.setupezcontenttype.treeprocessor' ) ),
            'Bpaulin\SetupEzContentTypeBundle\Service\TreeProcessor'
        );
    }

    public function testImportServiceIsAvailable()
    {
        $this->loadConfig( 'sample.yml' );

        $this->assertTrue(
            $this->container->has( 'bpaulin.setupezcontenttype.import' ),
            "The import service isn't available"
        );
        $this->assertEquals(
            get_class( $this->container->get( 'bpaulin.setupezcontenttype.import' ) ),
            'Bpaulin\SetupEzContentTypeBundle\Service\Import'
        );
    }
}