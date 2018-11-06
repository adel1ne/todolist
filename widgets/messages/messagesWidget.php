<?php
/**
 * Created by PhpStorm.
 * User: adel1ne
 * Date: 05.11.2018
 * Time: 0:26
 */

namespace widgets\messages;

use widgets\Widget;

class MessagesWidget extends Widget{
	public function __construct($view_name = 'index', $data = []) {
		parent::__construct( strtolower(MessagesWidget::class), $view_name, $data);
	}
}