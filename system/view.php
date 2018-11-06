<?php
namespace system;

class View {
    protected $data;
    protected $path;
    public $do_render = true;

    public function __construct($data = [], $path = null) {
        if ( !$path ) {
            $path = self::getDefaultViewPath();
        }

        // Если файла нет, значит вызывается метод, не требующий рендера
        if ( !file_exists($path) ) {
        	$this->do_render = false;
        }

        $this->path = $path;
        $this->data = $data;
    }

    protected static function getDefaultViewPath() {
        $router = App::getRouter();
        if ( !$router) {
            return false;
        }

        $controller_dir = $router->getController();
        $template_name = $router->getMethodPrefix().$router->getAction().'.html';

        return VIEWS_PATH.DS.$controller_dir.DS.$template_name;
    }

    public function render() {
        $data = $this->data;

        ob_start();
        include($this->path);
        $content = ob_get_clean();

        return $content;
    }
}