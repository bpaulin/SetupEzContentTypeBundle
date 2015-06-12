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

    public function testTreeProcessorServiceIsAvailable()
    {
        $this->container->loadFromExtension( $this->extension->getAlias() );
        $this->container->compile();

        $this->assertTrue( $this->container->has( 'bpaulin.setupezcontenttype.treeprocessor' ) );
        $this->assertEquals(
            get_class( $this->container->get( 'bpaulin.setupezcontenttype.treeprocessor' ) ),
            'Bpaulin\SetupEzContentTypeBundle\Service\TreeProcessor'
        );
    }

    public function testWithoutConfiguration()
    {
        $this->container->loadFromExtension( $this->extension->getAlias() );
        $this->container->compile();

        $this->assertEmpty( $this->container->getParameter( 'bpaulin_setup_ez_content_type.groups' ) );
    }

    public function testWithConfiguration()
    {
        $loader = new YamlFileLoader( $this->container, new FileLocator( __DIR__.'/Fixtures/Yaml/' ) );
        $loader->load( 'sample.yml' );
        $this->container->compile();

        $this->assertArrayHasKey(
            'group1',
            $this->container->getParameter( 'bpaulin_setup_ez_content_type.groups' )
        );
    }
}
