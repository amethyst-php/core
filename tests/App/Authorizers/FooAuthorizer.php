<?php

namespace Amethyst\Core\Tests\App\Authorizers;

use Railken\Lem\Authorizer;
use Railken\Lem\Tokens;

class FooAuthorizer extends Authorizer
{
    /**
     * List of all permissions.
     *
     * @var array
     */
    protected $permissions = [
        Tokens::PERMISSION_CREATE => 'foo.create',
        Tokens::PERMISSION_UPDATE => 'foo.update',
        Tokens::PERMISSION_SHOW   => 'foo.show',
        Tokens::PERMISSION_REMOVE => 'foo.remove',
    ];
}
