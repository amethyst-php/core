<?php

namespace Amethyst\Core\Contracts;

interface TransformerContract
{
    public function setSelectedAttributes(array $selectedAttributes = []);

    public function getSelectedAttributes(): array;

    public function setAuthorizedAttributes(array $authorizedAttributes = []);

    public function getAuthorizedAttributes(): array;
}
