<?php

namespace Amethyst\Core\Http\Controllers\Traits;

trait RestCommonTrait
{
    use RestIndexTrait;
    use RestShowTrait;
    use RestCreateTrait;
    use RestUpdateTrait;
    use RestRemoveTrait;
    use RestStoreTrait;
    use RestEraseTrait;
}
