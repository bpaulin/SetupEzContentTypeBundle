<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Command;

use eZ\Publish\Core\Repository\UserService;
use Symfony\Component\Console\Application;
use Bpaulin\SetupEzContentTypeBundle\Command\SetupEzContentTypeCommand;
use Symfony\Component\Console\Tester\CommandTester;
use eZ\Publish\Core\Repository\Values\User\User;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SetupEzContentTypeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var SetupEzContentTypeCommand
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $tester;

    protected function setUp()
    {
        $this->application = new Application();
        $this->application->add( new SetupEzContentTypeCommand() );

        /** @var SetupEzContentTypeCommand $command */
        $this->command = $this->application->find( 'bpaulin:setupez:contenttype' );

        $this->tester = new CommandTester( $this->command );
    }

    public function testCommandWithoutDryRunOrForce()
    {
        $this->tester->execute(
            array(
                'command' => $this->command->getName()
            )
        );

        $this->assertContains(
            'You need to specify either --dry-run or --force',
            $this->tester->getDisplay()
        );
        $this->assertNotEquals(
            0,
            $this->tester->getStatusCode()
        );
    }

    public function testCommandWithBothDryRunAndForce()
    {
        $this->tester->execute(
            array(
                'command' => $this->command->getName(),
                '--dry-run' => true,
                '--force' => true
            )
        );

        $this->assertContains(
            'You can\'t specify both --dry-run and --force',
            $this->tester->getDisplay()
        );
        $this->assertNotEquals(
            0,
            $this->tester->getStatusCode()
        );
    }

    public function commandProvider()
    {
        return array(
            array(
                '--dry-run',
                false,
                'Only displaying what would be done...'
            ),
            array(
                '--force',
                true,
                'Altering content types...'
            )
        );
    }

    /**
     * @dataProvider commandProvider
     */
    public function testCommandWithDryRunOrForce( $param, $called, $display)
    {
        $tree = array(
            "group1" => array(
                "type1" => array(
                    "fields" => array()
                )
            )
        );

        $import = $this->getMockBuilder( 'Bpaulin\SetupEzContentTypeBundle\Service\Import' )
            ->getMock();
        $import->expects( $this->once() )
            ->method( 'setForce' )
            ->with(
                $called
            );

        $userAdmin = new User();
        $userService = $this->getMockBuilder( 'eZ\Publish\API\Repository\UserService' )
            ->getMock();
        $userService->expects( $this->once() )
            ->method( 'loadUserByLogin' )
            ->with( 'admin' )
            ->will(
                $this->returnValue(
                    $userAdmin
                )
            );
        $repository = $this->getMockBuilder( 'eZ\Publish\API\Repository\Repository' )
            ->getMock();
        $repository->expects( $this->once() )
            ->method( 'getUserService' )
            ->will(
                $this->returnValue(
                    $userService
                )
            );
        $repository->expects( $this->once() )
            ->method( 'setCurrentUser' )
            ->with( $userAdmin );

        $dispatcher = $this->getMockBuilder( '\Symfony\Component\EventDispatcher\EventDispatcher' )
            ->getMock();
        $dispatcher->expects( $this->once() )
            ->method( 'addSubscriber' )
            ->with( $this->command );

        $container = $this->getMockBuilder( 'Symfony\Component\DependencyInjection\Container' )
            ->getMock();
        $container->expects( $this->exactly( 3 ) )
            ->method( 'get' )
            ->withConsecutive(
                array( 'ezpublish.api.repository' ),
                array( 'event_dispatcher' ),
                array( 'bpaulin.setupezcontenttype.import' )
            )
            ->will(
                $this->onConsecutiveCalls(
                    $repository,
                    $dispatcher,
                    $import
                )
            );
        $container->expects( $this->any() )
            ->method( 'getParameter' )
            ->with( 'bpaulin_setup_ez_content_type.groups' )
            ->will( $this->returnValue( $tree ) );
        $this->command->setContainer( $container );
        $this->tester->execute(
            array(
                'command' => $this->command->getName(),
                $param => true
            )
        );

        $this->assertContains(
            $display,
            $this->tester->getDisplay()
        );
    }
}
