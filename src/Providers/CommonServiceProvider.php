<?php

namespace Amethyst\Core\Providers;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Array of config files.
     *
     * @var array
     */
    protected $configFiles = [];

    /**
     * Directory.
     *
     * @param string
     */
    protected $directory;

    /**
     * Create a new instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        $reflector = new \ReflectionClass($this);

        $this->directory = dirname((string) $reflector->getFileName());

        parent::__construct($app);
    }

    /**
     * Get current directory.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->loadRoutes();
        $this->loadTranslations();

        if ($this->app->runningInConsole()) {
            $this->publishableTranslations();
            $this->loadMigrationsFrom($this->getDirectory().'/../../database/migrations');
            $this->publishableAssets();
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->loadConfigs();
        $this->app->register(AmethystServiceProvider::class);
    }

    /**
     * Publishable translations.
     */
    public function publishableTranslations()
    {
        $directory = $this->getDirectory().'/../../resources/lang';

        $this->publishes([
            $directory => resource_path('lang/vendor/amethyst'),
        ], 'resources');
    }

    /**
     * Load translations.
     */
    public function loadTranslations()
    {
        $directory = $this->getDirectory().'/../../resources/lang';

        $this->loadTranslationsFrom($directory, 'amethyst-'.$this->getPackageName());
    }

    /**
     * Load assets.
     */
    public function publishableAssets()
    {
        $directory = $this->getDirectory().'/../../resources/assets';

        $this->publishes([
            $directory => storage_path('assets/amethyst'),
        ], 'assets');
    }

    /**
     * Load configs-.
     */
    public function loadConfigs()
    {
        $directory = $this->getDirectory().'/../../config/*';

        foreach (glob($directory) as $file) {
            $this->publishes([$file => config_path(basename($file))], 'config');
            $this->mergeConfigFrom($file, basename($file, '.php'));
        }
    }

    /**
     * Return package name.
     */
    public function getPackageName()
    {
        $reflection = new \ReflectionClass($this);
        $inflector = new Inflector();

        return str_replace('_', '-', $inflector->tableize(str_replace('ServiceProvider', '', $reflection->getShortName())));
    }

    /**
     * Load routes based on configs.
     */
    public function loadRoutes()
    {
        // Moved.
    }
}
