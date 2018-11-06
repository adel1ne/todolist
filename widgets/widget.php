<?php

namespace widgets;

use system\View;

class Widget {
	protected $path;
	protected $data;
	protected $view;

	public function __construct($childClass, $view_name, $data) {
		$childClassExp = explode('\\', $childClass);
		$this->path = ROOT.DS.$childClassExp[0].DS.$childClassExp[1].DS.'view'.DS.$view_name.'.html';
		$this->data = $data;

		$this->view = new View($this->data, $this->path);
	}

	public function getView() {
		return $this->view->render();
	}
}