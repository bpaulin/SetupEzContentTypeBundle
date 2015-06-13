<?php
namespace Bpaulin\SetupEzContentTypeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetupEzContentTypeCommand
 *
 * launch the content type import
 *
 * @package Bpaulin\SetupEzContentTypeBundle\Command
 * @author bpaulin<brunopaulin@bpaulin.net>
 */
class SetupEzContentTypeCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $output;

    /**
     * @var \Symfony\Component\Console\Input\ArgvInput
     */
    protected $input;

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName( 'bpaulin:setupez:contenttype' )
            ->setDescription( 'Setup Ez Content Type' )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'If set, display without doing nothing'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'If set, process configuration and alter database'
            );
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
         * store properties
         */
        $this->input = $input;
        $this->output = $output;

        /*
         * check command input
         */
        if ( !$input->getOption( 'dry-run' ) && !$input->getOption( 'force' ) )
        {
            $output->writeln( '<error>You need to specify either --dry-run or --force</error>' );
            return 1;
        }
        else if ( $input->getOption( 'dry-run' ) && $input->getOption( 'force' ) )
        {
            $output->writeln( '<error>You can\'t specify both --dry-run and --force</error>' );
            return 1;
        }
        else if ( $input->getOption( 'dry-run' ) )
        {
            $output->writeln( '<info>Only displaying what would be done...</info>' );
        }
        else if ( $input->getOption( 'force' ) )
        {
            $output->writeln( '<info>Altering content types...</info>' );
        }

        return 0;
    }
}
