<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Service;

use Bpaulin\SetupEzContentTypeBundle\Service\TreeProcessor;

class BpaulinSetupEzContentTypeExtensionTest extends \PHPUnit_Framework_TestCase
{

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
        $class = new \ReflectionClass( 'Bpaulin\\SetupEzContentTypeBundle\\Service\\TreeProcessor' );
        $method = $class->getMethod( 'mergeType' );
        $method->setAccessible( true );

        $processor = new TreeProcessor();
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
                            "mainLanguageCode" => "eng",
                            "names" => array(
                                "eng-GB" => "type11"
                            )
                        ),
                        "type12" => array(
                            "mainLanguageCode" => "eng",
                            "extends" => "group2.type22",
                            "names" => array(
                                "eng-GB" => "type12"
                            ),
                            "fields" => array(
                                "field1" => array()
                            ),
                        ),
                    ),
                    "group2" => array(
                        "type22" => array(
                            "mainLanguageCode" => "fre",
                            "names" => array(
                                "eng-GB" => "type22"
                            ),
                            "fields" => array(
                                "field2" => array()
                            ),
                            "virtual" => true
                        ),
                    )
                ),
                array(
                    "group1" => array(
                        "type11" => array(
                            "mainLanguageCode" => "eng",
                            "names" => array(
                                "eng-GB" => "type11"
                            )
                        ),
                        "type12" => array(
                            "mainLanguageCode" => "eng",
                            "names" => array(
                                "eng-GB" => "type12"
                            ),
                            "fields" => array(
                                "field1" => array(),
                                "field2" => array(),
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
        $class = new \ReflectionClass( 'Bpaulin\\SetupEzContentTypeBundle\\Service\\TreeProcessor' );
        $method = $class->getMethod( 'processGroup' );
        $method->setAccessible( true );

        $processor = new TreeProcessor();
        $this->assertEquals( $expected, $method->invoke( $processor, $config ) );
    }

    public function testCircularExtends()
    {
        $input = array(
            "group1" => array(
                "type11" => array(
                    "extends" => "group1.type12"
                ),
                "type12" => array(
                    "extends" => "group1.type11"
                ),
            )
        );
        $class = new \ReflectionClass( 'Bpaulin\\SetupEzContentTypeBundle\\Service\\TreeProcessor' );
        $method = $class->getMethod( 'processGroup' );
        $method->setAccessible( true );

        $processor = new TreeProcessor();
        $this->setExpectedException( 'Exception', "circular extends" );
        $this->assertEquals( null, $method->invoke( $processor, $input ) );
    }

    public function testGetTree()
    {
        $container = $this->getMockBuilder( 'Symfony\Component\DependencyInjection\Container' )
            ->getMock();
        $container->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'bpaulin_setup_ez_content_type.groups' )
            ->will(
                $this->returnValue(
                    array(
                        "group1" => array(
                            "field1" => array()
                        )
                    )
                )
            );

        $processor = new TreeProcessor();
        $processor->setContainer( $container );
        $processor->getTree();
        $processor->getTree();
    }
}
