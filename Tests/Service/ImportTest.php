<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Service;

use Bpaulin\SetupEzContentTypeBundle\Service\Import;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Import
     */
    protected $import;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    public function setUp()
    {
        $this->contentTypeService = $this->getMockBuilder( 'eZ\Publish\API\Repository\ContentTypeService' )
            ->getMock();

        $this->repository = $this->getMockBuilder( 'eZ\Publish\API\Repository\Repository' )
            ->getMock();
        $this->repository->expects( $this->once() )
            ->method( 'getContentTypeService' )
            ->will(
                $this->returnValue(
                    $this->contentTypeService
                )
            );

        $this->dispatcher = $this->getMockBuilder( '\Symfony\Component\EventDispatcher\EventDispatcher' )
            ->getMock();

        $this->container = $this->getMockBuilder( 'Symfony\Component\DependencyInjection\Container' )
            ->getMock();
        $this->container->expects( $this->exactly( 2 ) )
            ->method( 'get' )
            ->withConsecutive(
                array( 'ezpublish.api.repository' ),
                array( 'event_dispatcher' )
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->repository,
                    $this->dispatcher
                )
            );

        $this->import = new Import();
        $this->import->setContainer( $this->container );
    }

    public function testGetNewGroup()
    {
        $this->contentTypeService->expects( $this->once() )
            ->method( 'loadContentTypeGroupByIdentifier' )
            ->will(
                $this->throwException(
                    new \eZ\Publish\Core\Base\Exceptions\NotFoundException( '', '' )
                )
            );

        $this->assertEquals(
            false,
            $this->import->getGroupDraft( 'sdf' )
        );
    }

    public function testForceGetNewGroup()
    {
        $this->contentTypeService->expects( $this->once() )
            ->method( 'loadContentTypeGroupByIdentifier' )
            ->will(
                $this->throwException(
                    new \eZ\Publish\Core\Base\Exceptions\NotFoundException( '', '' )
                )
            );

        $sdfGroupStruct = new ContentTypeGroupCreateStruct( array( 'identifier' => 'sdf' ) );
        $this->contentTypeService->expects( $this->once() )
            ->method( 'newContentTypeGroupCreateStruct' )
            ->with( 'sdf' )
            ->will(
                $this->returnValue( $sdfGroupStruct )
            );

        $sdfGroup = new ContentTypeGroup();
        $this->contentTypeService->expects( $this->once() )
            ->method( 'createContentTypeGroup' )
            ->with( $sdfGroupStruct )
            ->will(
                $this->returnValue( $sdfGroup )
            );

        $this->import->setForce( true );
        $this->assertEquals(
            $sdfGroup,
            $this->import->getGroupDraft( 'sdf' )
        );
    }

    public function testGetOldGroup()
    {
        $this->contentTypeService->expects( $this->once() )
            ->method( 'loadContentTypeGroupByIdentifier' )
            ->with( 'sdf' )
            ->will(
                $this->returnValue( 'sdfGroup' )
            );

        $this->assertEquals(
            'sdfGroup',
            $this->import->getGroupDraft( 'sdf' )
        );
    }
}
