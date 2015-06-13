<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Bpaulin\SetupEzContentTypeBundle\Command\SetupEzContentTypeCommand;
use Symfony\Component\Console\Tester\CommandTester;

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
                "field1" => array()
            )
        );
        $return = 255;

        $treeProcessor = $this->getMockBuilder( 'Bpaulin\SetupEzContentTypeBundle\Service\TreeProcessor' )
            ->getMock();
        $treeProcessor->expects( $this->once() )
            ->method( 'getTree' )
            ->will(
                $this->returnValue( $tree )
            );

        $import = $this->getMockBuilder( 'Bpaulin\SetupEzContentTypeBundle\Service\Import' )
            ->getMock();
        $import->expects( $this->once() )
            ->method( 'process' )
            ->with(
                $tree,
                $called
            )
            ->will(
                $this->returnValue(
                    $return
                )
            );

        $container = $this->getMockBuilder( 'Symfony\Component\DependencyInjection\Container' )
            ->getMock();
        $container->expects( $this->exactly( 2 ) )
            ->method( 'get' )
            ->withConsecutive(
                array( 'bpaulin.setupezcontenttype.treeprocessor' ),
                array( 'bpaulin.setupezcontenttype.import' )
            )
            ->will(
                $this->onConsecutiveCalls(
                    $treeProcessor,
                    $import
                )
            );
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
        $this->assertEquals(
            $return,
            $this->tester->getStatusCode()
        );
    }
}
