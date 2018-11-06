<?php

namespace widgets\lists;

use widgets\Widget;

class ListsWidget extends Widget {
	public function __construct($view_name = 'index', $data = []) {
		parent::__construct( strtolower(ListsWidget::class), $view_name, $data);
	}
}