<?php

namespace Amethyst\Core\Tests;

use Amethyst\Core\Support\Testing\TestableBaseTrait;
use Amethyst\Core\Tests\App\Fakers\FooFaker;

class ApiTest extends BaseTest
{
    use TestableBaseTrait;

    /**
     * Faker class.
     *
     * @var string
     */
    protected $faker = FooFaker::class;

    /**
     * Router group resource.
     *
     * @var string
     */
    protected $group = 'admin';

    /**
     * Route name.
     *
     * @var string
     */
    protected $route = 'admin.foo';
}
