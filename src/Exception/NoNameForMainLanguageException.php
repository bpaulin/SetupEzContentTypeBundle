<?php

namespace Bpaulin\SetupEzContentTypeBundle\Exception;

class NoNameForMainLanguageException extends \Exception
{

    function __construct( $typeName )
    {
        parent::__construct( "No name for main language provided for $typeName" );
    }
}
