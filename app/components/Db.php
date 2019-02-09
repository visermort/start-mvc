<?php

namespace app\components;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use app\App;
use app\Component;
/**
 * Class Help
 * @package app\components
 */
class Db extends Component
{
    /**
     * Db init
     */
    public static function init()
    {
        $dbConfig = App::getConfig('database.connection');

        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => $dbConfig['driver'],
            'host'      => $dbConfig['host'],
            'database'  => $dbConfig['database'],
            'username'  => $dbConfig['user'],
            'password'  => $dbConfig['password'],
            'charset'   => $dbConfig['charset'],
            'collation' => $dbConfig['collation'],
            'prefix'    => $dbConfig['table_prefix'],
        ]);

        // Set the event dispatcher used by Eloquent models... (optional)
        $capsule->setEventDispatcher(new Dispatcher(new Container));

        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();

        return parent::init();
    }

}