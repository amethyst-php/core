<?php

namespace Railken\LaraOre\Support\Testing;

trait ManagerTestableTrait
{
    public function commonTest($manager, $parameters)
    {
        $result = $manager->create($parameters);
        $this->assertResultOrPrint($result);

        $resource = $result->getResource();

        $result = $manager->update($resource, $parameters);
        $this->assertResultOrPrint($result);

        $resource = $result->getResource();

        $this->assertEquals($resource->id, $manager->getRepository()->findOneById($resource->id)->id);

        $result = $manager->remove($resource);
        $this->assertResultOrPrint($result);
    }

    public function assertResultOrPrint($result, $flag = true)
    {

        if ($result->ok() !== $flag) {
            print_r($result->getSimpleErrors());
        }

        $this->assertEquals($flag, $result->ok());
    }
}
