<?php

namespace Amethyst\Core\Tests\App\Models;

use Amethyst\Core\ConfigurableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Railken\Lem\Contracts\EntityContract;

class Foo extends Model implements EntityContract
{
    use SoftDeletes;
    use ConfigurableModel;
    use Notifiable;

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        $this->ini('amethyst.foo.data.foo');
        parent::__construct($attributes);
    }

    public function bar(): BelongsTo
    {
        return $this->belongsTo(config('amethyst.bar.data.bar.model'));
    }
}
