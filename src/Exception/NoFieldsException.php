<?php

namespace Bpaulin\SetupEzContentTypeBundle\Exception;

class NoFieldsException extends \Exception
{

    function __construct( $typeName )
    {
        parent::__construct( "No fields provided for $typeName" );
    }
}
