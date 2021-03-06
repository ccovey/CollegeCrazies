<?php

namespace SofaChamps\Bundle\CoreBundle\Tests;

use Faker\Factory;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class SofaChampsWebTest extends WebTestCase
{
    private $faker;

    protected function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    protected function getFaker()
    {
        return $this->faker;
    }
}
