<?php

namespace Amethyst\Core\Tests\App\Authorizers;

use Railken\Lem\Authorizer;
use Railken\Lem\Tokens;

class BarAuthorizer extends Authorizer
{
    /**
     * List of all permissions.
     *
     * @var array
     */
    protected $permissions = [
        Tokens::PERMISSION_CREATE => 'bar.create',
        Tokens::PERMISSION_UPDATE => 'bar.update',
        Tokens::PERMISSION_SHOW   => 'bar.show',
        Tokens::PERMISSION_REMOVE => 'bar.remove',
    ];
}
