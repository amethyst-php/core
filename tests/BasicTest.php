<?php

namespace Amethyst\Core\Tests;

use Amethyst\Core\Tests\App\Fakers\FooFaker;
use Amethyst\Core\Tests\App\Managers\FooManager;
use Railken\Lem\Support\Testing\TestableBaseTrait;

class BasicTest extends Base
{
    use TestableBaseTrait;

    /**
     * Manager class.
     *
     * @var string
     */
    protected $manager = FooManager::class;

    /**
     * Faker class.
     *
     * @var string
     */
    protected $faker = FooFaker::class;
}
