<?php

namespace Railken\LaraOre\Support\Testing;

trait ManagerTestableTrait
{
    public function commonTest($manager, $parameters)
    {
        $result = $manager->create($parameters);
        $this->assertEquals(true, $result->ok());

        $resource = $result->getResource();

        $result = $manager->update($resource, $parameters);
        $this->assertEquals(true, $result->ok());

        $resource = $result->getResource();

        $this->assertEquals($resource->id, $manager->getRepository()->findOneById($resource->id)->id);

        $result = $manager->remove($resource);
        $this->assertEquals(true, $result->ok());
    }
}
