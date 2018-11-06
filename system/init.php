<?php
spl_autoload_register(function ($class) {
    $class = explode('\\',$class);
    $class_path = ROOT;
    for ($i=0; $i < count($class)-1; $i++) {
	    $class_path .= DS.$class[$i];
    }
    // костылек :( а потому что под Windows писал.
    if (preg_match('/(.*)widget$/',strtolower(end($class)))) {
        $class_path .= DS.preg_replace('/(.+)widget$/', '$1Widget',strtolower(end($class))).'.php';
    } else {
        $class_path .= DS.strtolower(end($class)).'.php';
    }

    if (file_exists($class_path)) {
        require_once($class_path);
    } else {
	    exit('No such file:'.$class_path);
    }
});

require_once(ROOT.DS.'config'.DS.'config.php');

