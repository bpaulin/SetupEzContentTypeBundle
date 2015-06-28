<?php

namespace Bpaulin\SetupEzContentTypeBundle\Exception;

class CircularExtendsException extends \Exception
{

    function __construct( $typeName )
    {
        parent::__construct( "circular extends for $typeName" );
    }
}
