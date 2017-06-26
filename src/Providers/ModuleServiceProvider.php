<?php
/**
 * Contains the ModuleServiceProvider class.
 *
 * @copyright   Copyright (c) 2016 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2016-11-30
 *
 */


namespace Konekt\AppShell\Providers;


use Illuminate\Support\Facades\Route;
use Konekt\AppShell\Breadcrumbs\HasBreadcrumbs;
use Konekt\AppShell\Console\Commands\ScaffoldCommand;
use Konekt\AppShell\Http\Middleware\AclMiddleware;
use Konekt\AppShell\Http\Requests\CreateUser;
use Konekt\AppShell\Http\Requests\UpdateUser;
use Konekt\AppShell\Models\User;
use Konekt\Concord\BaseBoxServiceProvider;
use Konekt\User\Contracts\User as UserContract;
use Konekt\User\Models\UserProxy;
use Menu;

class ModuleServiceProvider extends BaseBoxServiceProvider
{
    use HasBreadcrumbs;

    protected $requests = [
        CreateUser::class,
        UpdateUser::class
    ];

    public function register()
    {
        parent::register();

        $this->app->register(AuthServiceProvider::class);
        $this->registerThirdPartyProviders();
        $this->registerCommands();
    }

    public function boot()
    {
        parent::boot();
        $this->loadBreadcrumbs();

        // Use the User model that's extended with Acl
        $this->concord->registerModel(UserContract::class, User::class);

        Route::aliasMiddleware('acl', AclMiddleware::class);
        Route::model('user', UserProxy::modelClass());

        $this->initializeMenus();
    }

    /**
     * Registers 3rd party providers, AppShell is built on top of
     *
     * They are:
     *  - Lavary Menu,
     *  - Laravel Collective Forms
     *  - Laracasts Flash
     *  - Yajra Breadcrumbs
     */
    protected function registerThirdPartyProviders()
    {
        $this->registerMenuComponent();
        $this->registerFormComponent();
        $this->registerFlashComponent();
        $this->registerBreadcrumbsComponent();
    }


    /**
     * Register appshell's commands
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScaffoldCommand::class
            ]);
        }
    }

    /**
     * Initializes menus set in the configuration
     */
    protected function initializeMenus()
    {
        foreach ($this->config('menu') as $name => $config) {
            Menu::create($name, $config);
        }

        // Add default menu items to sidbar
        if ($appshellMenu = Menu::get('appshell')) {
            $appshellMenu->addItem('users', __('Users'), ['route' => 'appshell.user.index'])->data('icon', 'accounts');
        }
    }


    /**
     * Register Laravel Collective Form Component
     */
    private function registerFormComponent()
    {
        $this->app->register(\Collective\Html\HtmlServiceProvider::class);
        $this->concord->registerAlias('Form', \Collective\Html\FormFacade::class);
        $this->concord->registerAlias('Html', \Collective\Html\HtmlFacade::class);
    }

    /**
     * Registers Konekt Menu Component
     */
    private function registerMenuComponent()
    {
        $this->app->register(\Konekt\Menu\MenuServiceProvider::class);
        $this->concord->registerAlias('Menu', \Konekt\Menu\Facades\Menu::class);
    }

    /**
     * Register the Laracasts Flash Component
     */
    private function registerFlashComponent()
    {
        $this->app->register(\Laracasts\Flash\FlashServiceProvider::class);
    }

    /**
     * Register the breadcrumbs component, also merge the config from within the box config
     */
    private function registerBreadcrumbsComponent()
    {
        // Register The Breadcrumbs Component
        $this->app->register(\Yajra\Breadcrumbs\ServiceProvider::class);
        $this->concord->registerAlias('Breadcrumbs', \Yajra\Breadcrumbs\Facade::class);

        // Merge component config from the box config
        // Note that this can still be overwritten
        // by the app in config/breadcrumbs.php
        $this->app['config']->set('breadcrumbs',
            array_merge(
                $this->config('components.breadcrumbs') ?: [],  // key within box config
                $this->app['config']['breadcrumbs'] ?: [] // current
            )
        );
    }


}