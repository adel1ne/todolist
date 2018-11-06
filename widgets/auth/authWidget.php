<?php

namespace widgets\auth;

use widgets\Widget;

class AuthWidget extends Widget {
	public function __construct($view_name = 'index', $data = []) {
		parent::__construct( strtolower(AuthWidget::class), $view_name, $data);
	}
}