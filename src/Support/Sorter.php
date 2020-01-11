<?php

namespace Amethyst\Core\Support;

class Sorter
{
    /**
     * List of sorting values.
     *
     * @var array
     */
    protected $values;

    /**
     * List of sorting keys.
     *
     * @var array
     */
    protected $keys;

    /**
     * Set keys.
     *
     * @param array $keys
     *
     * @return $this
     */
    public function setKeys($keys)
    {
        $this->keys = $keys;

        return $this;
    }

    /**
     * Perform the query and retrieve the information about pagination.
     *
     * @param string $name
     * @param string $direction
     *
     * @return $this
     */
    public function add($name, $direction)
    {
        if (!in_array($name, $this->keys, true)) {
            throw new Exceptions\InvalidSorterFieldException($name);
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new Exceptions\InvalidSorterDirectionException($direction);
        }

        $field = new SorterField();
        $field->setName($name);
        $field->setDirection($direction);
        $this->values[] = $field;
    }

    /**
     * Retrieve all sorting values.
     *
     * @return array
     */
    public function get()
    {
        return $this->values;
    }
}
