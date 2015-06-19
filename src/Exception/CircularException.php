<?php

namespace Bpaulin\SetupEzContentTypeBundle\Exception;

class CircularException extends \Exception
{

    function __construct()
    {
        parent::__construct( "circular extends" );
    }
}
