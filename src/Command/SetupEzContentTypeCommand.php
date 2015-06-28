<?php
namespace Bpaulin\SetupEzContentTypeBundle\Command;

use Bpaulin\SetupEzContentTypeBundle\Event\FieldAttributeEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\FieldDraftEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\FieldStructureEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\GroupLoadingEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\TypeDraftEvent;
use Bpaulin\SetupEzContentTypeBundle\Event\TypeStructureEvent;
use Bpaulin\SetupEzContentTypeBundle\Events;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SetupEzContentTypeCommand
 *
 * launch the content type import
 *
 * @package Bpaulin\SetupEzContentTypeBundle\Command
 * @author bpaulin<brunopaulin@bpaulin.net>
 */
class SetupEzContentTypeCommand extends ContainerAwareCommand
    implements EventSubscriberInterface
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

        /*
         * call services
         */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        /*
         * set user admin
         */
        $userService = $repository->getUserService();
        $repository->setCurrentUser( $userService->loadUserByLogin( 'admin' ) );

        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get( "event_dispatcher" );
        $dispatcher->addSubscriber( $this );

        return $this->runImport();
    }

    protected function runImport()
    {
        $tree = $this->getContainer()->get( 'bpaulin.setupezcontenttype.treeprocessor' )->getTree();
        $importService = $this->getContainer()->get( 'bpaulin.setupezcontenttype.import' );
        /*
         * and finally, at least, do something
         */
        $importService->setForce( $this->input->getOption( 'force' ) );
        foreach ( $tree as $groupName => $groupData )
        {
            $groupDraft = $importService->getGroupDraft( $groupName );
            foreach ( $groupData as $typeName => $typeData )
            {
                $typeDraft = $importService->getTypeDraft( $typeName );
                $typeStructure = $importService->getTypeStructure( $typeDraft, $typeName );
                $importService->hydrateType( $typeStructure, $typeData );

                foreach ( $typeData['fields'] as $fieldName => $fieldData )
                {
                    $fieldDraft = $importService->getFieldDraft(
                        $fieldName,
                        $typeDraft
                    );
                    $fieldStructure = $importService->getFieldStructure( $fieldDraft, $fieldName, $fieldData['type'] );
                    $importService->hydrateField( $fieldDraft, $fieldStructure, $fieldData );
                    $importService->addFieldToType(
                        $fieldDraft,
                        $fieldStructure,
                        $typeDraft,
                        $typeStructure
                    );
                }
                $importService->addTypeToGroup( $typeDraft, $typeStructure, $groupDraft );
            }
        }
    }

    /**
     * return events this command is listening to
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::AFTER_GROUP_LOADING => 'afterGroupLoading',
            Events::AFTER_TYPE_DRAFT_LOADING => 'afterTypeDraftLoading',
            Events::AFTER_FIELD_DRAFT_LOADING => 'afterFieldDraftLoading',
            Events::AFTER_TYPE_STRUCTURE_LOADING => 'afterTypeStructureLoading',
            Events::AFTER_FIELD_STRUCTURE_LOADING => 'afterFieldStructureLoading',
            Events::AFTER_FIELD_ATTRIBUTE_LOADING => 'afterFieldAttributeLoading',
        );
    }

    /**
     * This is executed after a group is loaded
     *
     * @param GroupLoadingEvent $event
     */
    public function afterGroupLoading(GroupLoadingEvent $event)
    {
        $this->output->writeln( 'group '.$event->getGroupName().' '.$event->getStatus() );
    }

    public function afterTypeDraftLoading(TypeDraftEvent $event)
    {
        $this->output->writeln( '  type draft '.$event->getTypeName().' '.$event->getStatus() );
    }

    public function afterFieldDraftLoading(FieldDraftEvent $event)
    {
        $this->output->writeln( '    field draft '.$event->getFieldName().' '.$event->getStatus() );
    }

    public function afterTypeStructureLoading(TypeStructureEvent $event)
    {
        $this->output->writeln( '  type structure: '.$event->getStatus() );
    }

    public function afterFieldStructureLoading(FieldStructureEvent $event)
    {
        $this->output->writeln( '    field structure: '.$event->getStatus() );
    }

    public function afterFieldAttributeLoading(FieldAttributeEvent $event)
    {
        $this->output->writeln( '      field attribute: '.$event->getAttributeName().' '.$event->getOldValue().' -> '.$event->getNewValue() );
    }
}
