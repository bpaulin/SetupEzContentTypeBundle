<?php

namespace Bpaulin\SetupEzContentTypeBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Bpaulin\SetupEzContentTypeBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testGroupIsRequired()
    {
        $this->assertConfigurationIsInvalid(
            array(
                array() // no values at all
            ),
            'groups'
        );
    }
}
