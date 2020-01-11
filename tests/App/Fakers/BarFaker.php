<?php

namespace Amethyst\Core\Tests\App\Fakers;

use Faker\Factory;
use Railken\Bag;
use Railken\Lem\Faker;

class BarFaker extends Faker
{
    /**
     * @return \Railken\Bag
     */
    public function parameters()
    {
        $faker = Factory::create();

        $bag = new Bag();
        $bag->set('name', $faker->name);
        $bag->set('description', $faker->text);

        return $bag;
    }
}
