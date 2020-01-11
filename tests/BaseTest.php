<?php

namespace Amethyst\Core\Tests;

use Amethyst\Core\Support\Router;
use Amethyst\Core\Tests\App;
use Amethyst\Core\Tests\App\Controllers\FooController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

abstract class BaseTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('foo');

        Schema::create('foo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('bar_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::dropIfExists('bar');

        Schema::create('bar', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Router::group('admin', ['as' => 'foo.', 'prefix' => 'foo'], function ($router) {
            $controller = FooController::class;

            $router->get('/', ['as' => 'index', 'uses' => $controller.'@index']);
            $router->post('/', ['as' => 'create', 'uses' => $controller.'@create']);
            $router->put('/{id}', ['as' => 'update', 'uses' => $controller.'@update']);
            $router->delete('/{id}', ['as' => 'remove', 'uses' => $controller.'@remove']);
            $router->get('/{id}', ['as' => 'show', 'uses' => $controller.'@show']);
        });

        Route::fallback(function () {
            return response()->json(['message' => 'Not Found!'], 404);
        });

        Config::set('amethyst.foo.data.foo', [
            'table'      => 'amethyst_foos',
            'comment'    => 'Foo',
            'model'      => App\Models\Foo::class,
            'schema'     => App\Schemas\FooSchema::class,
            'repository' => App\Repositories\FooRepository::class,
            'serializer' => App\Serializers\FooSerializer::class,
            'validator'  => App\Validators\FooValidator::class,
            'authorizer' => App\Authorizers\FooAuthorizer::class,
            'faker'      => App\Fakers\FooFaker::class,
            'manager'    => App\Managers\FooManager::class,
        ]);

        Config::set('amethyst.bar.data.bar', [
            'table'      => 'amethyst_Bars',
            'comment'    => 'Bar',
            'model'      => App\Models\Bar::class,
            'schema'     => App\Schemas\BarSchema::class,
            'repository' => App\Repositories\BarRepository::class,
            'serializer' => App\Serializers\BarSerializer::class,
            'validator'  => App\Validators\BarValidator::class,
            'authorizer' => App\Authorizers\BarAuthorizer::class,
            'faker'      => App\Fakers\BarFaker::class,
            'manager'    => App\Managers\BarManager::class,
        ]);
        
        app('amethyst')->ini();
        $this->artisan('mapper:generate');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Amethyst\Core\Providers\AmethystServiceProvider::class,
        ];
    }
}
