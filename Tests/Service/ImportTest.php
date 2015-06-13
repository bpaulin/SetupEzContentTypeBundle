<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\Service;

use Bpaulin\SetupEzContentTypeBundle\Service\Import;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function testImportServiceIsAvailable()
    {
        $import = new Import( array( 'group' => array() ) );

        $this->assertEquals(
            get_class( $import ),
            'Bpaulin\SetupEzContentTypeBundle\Service\Import'
        );
    }
}
