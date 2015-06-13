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

    public function testCommandWithDryRun()
    {
        $this->tester->execute(
            array(
                'command' => $this->command->getName(),
                '--dry-run' => true
            )
        );

        $this->assertContains(
            'Only displaying what would be done...',
            $this->tester->getDisplay()
        );
    }

    public function testCommandWithForce()
    {
        $this->tester->execute(
            array(
                'command' => $this->command->getName(),
                '--force' => true
            )
        );

        $this->assertContains(
            'Altering content types...',
            $this->tester->getDisplay()
        );
    }
}
