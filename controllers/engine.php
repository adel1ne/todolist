<?php

namespace controllers;

use libs\Session;
use Predis\Connection\Parameters;
use system\Controller;
use widgets\auth\AuthWidget;
use widgets\lists\ListsWidget;
use widgets\messages\MessagesWidget;

include_once(ROOT.DS.'helpers'.DS.'regexp.php');

class Engine extends Controller {
	public function __construct( array $data = [] ) {
		parent::__construct( $data );
		$this->auth_model = new\models\Auth();
		$this->lists_model = new \models\Lists();
		$this->tasks_model = new \models\Tasks();
	}

	public function index() {
		//TODO если подтверждать регистрацию при авторизованном пользователе, то сообщения не будет, нужно сперва разлогинится?
		$data = [];
		$this->data['show_msg'] = 0;

        $response = isset($_GET['response'])?(int) $_GET['response']:0;
        $hash = isset($_GET['hash'])?$_GET['hash']:'';

        if (!empty($response) && !empty($hash) && regexp_hash($hash)) {
            // Если все ок, то показываем сообщение только один раз
            $getShownInfo = $this->auth_model->getConfirmShown($hash);
            if (count($getShownInfo)) {
                if (!(int) $getShownInfo[0]['confirm_shown']) {
                    $this->auth_model->confirmShown($getShownInfo[0]['id'], 1);
                } else {
                    $this->auth_model->confirmShown($getShownInfo[0]['id'], 0);
                    header('Location: /');
                }
            } else {
                header('Location: /');
            }

			$view_name = 'activate_conf_message';
			$data = [
				'conf_response' => $response
			];

			$messagesWidget = new MessagesWidget($view_name, $data);
			$this->data['info'] = $messagesWidget->getView();
			$this->data['show_msg'] = 1;
		}

		if (Session::get('id')) {
			$view_name = 'authorized';
			$data = [
				'email' => Session::get('email')
			];

			$activeQuery = $this->auth_model->getActiveList((int) Session::get('id'));
			$activeListId = $activeQuery[0]['active_list_id'];
			if ($activeListId) {
				$listInfo = $this->getListAndTasks($activeListId);
				$this->data['current_list'] = $listInfo['current_list'];
				$this->data['tasks'] = $listInfo['tasks'];
				$this->data['active_tasks_count'] = $listInfo['active_tasks_count'];
				$this->data['passive_tasks_count'] = $listInfo['passive_tasks_count'];
			}
		} else {
			$view_name = 'index';
		}

		$authWidget = new AuthWidget($view_name, $data);
		$this->data['auth'] = $authWidget->getView();
	}

	public function changeList() {
		header('Content-type:application/json');
		$list_id = isset($_POST['list_id'])?(int) $_POST['list_id']:0;
		$user_id = (int) Session::get('id');

		if (!$list_id) {
		    $getList = $this->auth_model->getActiveList($user_id);
            $list_id = $getList[0]['active_list_id'];
            if (!$list_id) {
                $result['error'] = 1;
            } else {
                $result = $this->getListAndTasks($list_id);
                $result['error'] = 0;
            }
        } else {
            $result = $this->getListAndTasks($list_id);
            $result['error'] = 0;
            if (!$this->auth_model->setActiveListToUser($list_id, $user_id)) {
                $result['error'] = 1;
            }
        }

		echo json_encode($result);
	}

	private function getListAndTasks($list_id) {
		$data = [];
		$listInfo = $this->lists_model->getList($list_id);
		$listsWidget = new ListsWidget('show_list', [
			'list_id' => $list_id,
			'list_name' => $listInfo[0]['list_name']
		]);
		$data['current_list'] = $listsWidget->getView();

		$tasks = $this->tasks_model->getAllTasks($list_id);
		$listsWidget = new ListsWidget('show_tasks', [
			'tasks' => $tasks
		]);
		$data['tasks'] = $listsWidget->getView();

		$active_count = 0;
		$passive_count = 0;
		foreach ($tasks as $task) {
			if (!$task['status']) {
				$active_count++;
			} else {
				$passive_count++;
			}
		}

		$data['active_tasks_count'] = $active_count;
		$data['passive_tasks_count'] = $passive_count;

		return $data;
	}

	public function showMessage() {
		$view_name = $_GET['view_name'];
		if (regexp_view_name($view_name)) {
			$messagesWidget = new MessagesWidget($view_name, [
				'msg' => htmlspecialchars($_GET['msg'])
			]);
			echo $messagesWidget->getView();
		}
	}
}