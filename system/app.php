<?php
namespace system;

use libs\Db;

class App {
    protected static $router;
    public static $db;

    /**
     * @return mixed
     */
    public static function getRouter() {
        return self::$router;
    }

    public static function run($uri) {
        self::$router = new Router($uri);
        self::$db = new Db(Config::get('db')['default']);

        $controller_class = '\\controllers\\'.ucfirst(self::$router->getController());
        $controller_method = strtolower(self::$router->getMethodPrefix().self::$router->getAction());

        $controller_object = new $controller_class();

        if ( method_exists($controller_object, $controller_method) ) {
            $view_path = $controller_object->$controller_method();
            $view_object = new View($controller_object->getData(), $view_path);
            if ($view_object->do_render) {
	            $content = $view_object->render();
            }
        } else {
            throw new \Exception('Method does not exists');
        }

        if (!empty($content)) {
	        $layout = self::$router->getRoute();
	        $layout_path = VIEWS_PATH.DS.$layout.'.html';
	        $layout_view_object = new View(compact('content'), $layout_path);

	        echo $layout_view_object->render();
        }
    }
}