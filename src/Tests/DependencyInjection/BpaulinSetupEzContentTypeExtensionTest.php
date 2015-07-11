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

    public function testFieldFactoryServiceIsAvailable()
    {
        $this->loadConfig( 'sample.yml' );

        $this->assertTrue(
            $this->container->has( 'bpaulin.setupezcontenttype.field_factory' ),
            "The field factory service isn't available"
        );
        $this->assertEquals(
            get_class( $this->container->get( 'bpaulin.setupezcontenttype.field_factory' ) ),
            'Bpaulin\SetupEzContentTypeBundle\Service\FieldFactory'
        );
    }

    public function mergeTypeProvider()
    {
        return array(
            array(
                array(
                    "mainLanguageCode" => "eng",
                    "extends" => "group2.type22",
                    "names" => array(
                        "eng-GB" => "type12"
                    ),
                    "fields" => array(
                        "field1" => array()
                    ),
                ),
                array(
                    "mainLanguageCode" => "fre",
                    "names" => array(
                        "eng-GB" => "type22"
                    ),
                    "fields" => array(
                        "field2" => array()
                    ),
                    "virtual" => true
                ),
                array(
                    "mainLanguageCode" => "eng",
                    "names" => array(
                        "eng-GB" => "type12"
                    ),
                    "fields" => array(
                        "field1" => array(),
                        "field2" => array()
                    ),
                    "extends" => "group2.type22",
                    "virtual" => true
                )
            )
        );
    }

    /**
     * @dataProvider mergeTypeProvider
     */
    public function testMergeType( $type, $extends, $expected )
    {
        $class = new \ReflectionClass( 'Bpaulin\\SetupEzContentTypeBundle\\DependencyInjection\\BpaulinSetupEzContentTypeExtension' );
        $method = $class->getMethod( 'mergeType' );
        $method->setAccessible( true );

        $processor = new BpaulinSetupEzContentTypeExtension();
        $typeParam = $type;
        $this->assertEquals( $expected, $method->invokeArgs( $processor, array( &$typeParam, $extends ) ) );
    }

    public function processGroupProvider()
    {
        return array(
            array(
                array(
                    "group1" => array(
                        "type11" => array(
                            "mainLanguageCode" => "eng-GB",
                            "names" => array(
                                "eng-GB" => "type11"
                            ),
                            "fields" => array(
                                "field1" => array(
                                    "type" => 'ezstring'
                                )
                            )
                        ),
                        "type12" => array(
                            "mainLanguageCode" => "eng-GB",
                            "extends" => "group2.type22",
                            "names" => array(
                                "eng-GB" => "type12"
                            ),
                            "fields" => array(
                                "field1" => array(
                                    "type" => 'ezstring'
                                )
                            ),
                        ),
                    ),
                    "group2" => array(
                        "type22" => array(
                            "mainLanguageCode" => "eng-GB",
                            "names" => array(
                                "eng-GB" => "type22"
                            ),
                            "fields" => array(
                                "field2" => array(
                                    "type" => 'ezstring'
                                )
                            ),
                            "virtual" => true
                        ),
                    )
                ),
                array(
                    "group1" => array(
                        "type11" => array(
                            "mainLanguageCode" => "eng-GB",
                            "names" => array(
                                "eng-GB" => "type11"
                            ),
                            "fields" => array(
                                "field1" => array(
                                    "type" => 'ezstring'
                                )
                            )
                        ),
                        "type12" => array(
                            "mainLanguageCode" => "eng-GB",
                            "names" => array(
                                "eng-GB" => "type12"
                            ),
                            "fields" => array(
                                "field1" => array(
                                    "type" => 'ezstring'
                                ),
                                "field2" => array(
                                    "type" => 'ezstring'
                                ),
                            ),
                        ),
                    )
                ),
            )
        );
    }

    /**
     * @dataProvider processGroupProvider
     */
    public function testProcessGroup( $config, $expected )
    {
        $class = new \ReflectionClass( 'Bpaulin\\SetupEzContentTypeBundle\\DependencyInjection\\BpaulinSetupEzContentTypeExtension' );
        $method = $class->getMethod( 'processGroup' );
        $method->setAccessible( true );

        $processor = new BpaulinSetupEzContentTypeExtension();
        $this->assertEquals( $expected, $method->invoke( $processor, $config ) );
    }

    public function getTreeProvider()
    {
        return array(
            array(
                array(
                    "group1" => array(
                        "type1" => array(
                            'mainLanguageCode' => 'eng-GB',
                            'names' => array(
                                'eng-GB' => 'name'
                            ),
                            'fields' => array(
                                'name' => array(
                                    'type' => 'ezstring'
                                ),
                            )
                        )
                    )
                ),
                false
            ),
            array(
                array(
                    "group1" => array(
                        "type1" => array(
                            'mainLanguageCode' => 'eng-GB',
                            'names' => array(
                                'eng-GB' => 'name'
                            )
                        )
                    )
                ),
                'NoFields'
            ),
            array(
                array(
                    "group1" => array(
                        "type1" => array(
                            'mainLanguageCode' => 'eng-GB',
                            'names' => array(
                                'fre-FR' => 'name'
                            ),
                            'fields' => array(
                                'name' => array(),
                                'type' => 'ezstring'
                            )
                        )
                    )
                ),
                'NoNameForMainLanguage'
            ),
            array(
                array(
                    "group1" => array(
                        "type1" => array(
                            'mainLanguageCode' => 'eng-GB',
                            'names' => array(
                                'eng-GB' => 'name'
                            ),
                            'fields' => array(
                                'name' => array(),
                                'type' => 'UNKNOW_TYPE'
                            )
                        )
                    )
                ),
                'FieldTypeNotImplemented'
            ),
            array(
                array(
                    "group1" => array(
                        "type11" => array(
                            "extends" => "group1.type12"
                        ),
                        "type12" => array(
                            "extends" => "group1.type11"
                        ),
                    )
                ),
                'CircularExtends'
            )
        );
    }

    /**
     * @dataProvider getTreeProvider
     */
    public function testCheckTree( $config, $exception )
    {
        $class = new \ReflectionClass( 'Bpaulin\\SetupEzContentTypeBundle\\DependencyInjection\\BpaulinSetupEzContentTypeExtension' );
        $method = $class->getMethod( 'processGroup' );
        $method->setAccessible( true );

        if ( $exception )
        {
            $this->setExpectedException( '\Bpaulin\SetupEzContentTypeBundle\Exception\\'.$exception.'Exception' );
        }
        $processor = new BpaulinSetupEzContentTypeExtension();
        $method->invoke( $processor, $config );
    }
}
