<?php

namespace Bpaulin\SetupEzContentTypeBundle\Exception;

class FieldTypeNotImplementedException extends \Exception
{

    function __construct( $fieldName )
    {
        parent::__construct( "The type is not (yet?) implemented for field $fieldName" );
    }
}
