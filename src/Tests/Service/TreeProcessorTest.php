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
                            "mainLanguageCode" => "eng-GB",
                            "names" => array(
                                "eng-GB" => "type11"
                            )
                        ),
                        "type12" => array(
                            "mainLanguageCode" => "eng-GB",
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
                            "mainLanguageCode" => "eng-GB",
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
                            "mainLanguageCode" => "eng-GB",
                            "names" => array(
                                "eng-GB" => "type11"
                            )
                        ),
                        "type12" => array(
                            "mainLanguageCode" => "eng-GB",
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

    public function getTreeProvider()
    {
        return array(
            array(
                array(),
                false
            ),
            array(
                array(
                    "group1" => array(
                        "type1" => array(
                            'mainLanguageCode' => 'eng-GB',
                            'names' => array(
                                'fre-FR' => 'name'
                            )
                        )
                    )
                ),
                'NoNameForMainLanguage'
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
                'Circular'
            )
        );
    }

    /**
     * @dataProvider getTreeProvider
     */
    public function testGetTree( $config, $exception )
    {
        $container = $this->getMockBuilder( 'Symfony\Component\DependencyInjection\Container' )
            ->getMock();
        $container->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'bpaulin_setup_ez_content_type.groups' )
            ->will( $this->returnValue( $config ) );

        if ( $exception )
        {
            $this->setExpectedException( '\Bpaulin\SetupEzContentTypeBundle\Exception\\'.$exception.'Exception' );
        }

        $processor = new TreeProcessor();
        $processor->setContainer( $container );
        $processor->getTree();
    }
}
