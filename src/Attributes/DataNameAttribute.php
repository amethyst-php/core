<?php

namespace Amethyst\Core\Attributes;

use Railken\Lem\Attributes\EnumAttribute;

class DataNameAttribute extends EnumAttribute
{
    /**
     * Create a new instance.
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct(string $name = null, array $options = [])
    {
        if (empty($options)) {
            $options = app('amethyst')->getDataNames();
        }

        parent::__construct($name, $options);
    }

    public function getType(): string
    {
        return 'DataName';
    }
}
