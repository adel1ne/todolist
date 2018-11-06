<?php

namespace controllers;

use system\App;
use libs\Session;
use system\Controller;
use widgets\lists\ListsWidget;
use widgets\messages\messagesWidget;

class Lists extends Controller {
	public function __construct($data = []) {
		parent::__construct($data);
		$this->auth_model = new\models\Auth();
		$this->lists_model = new \models\Lists();
		$this->tasks_model = new \models\Tasks();
	}

	public function addTask() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$user_id = (int) Session::get('id');
		$list_id = (int) $_POST['list_id'];
		$task = $_POST['task'];

		if (empty($user_id)) {
			$result['error'] = 1;
			$messagesWidget = new MessagesWidget('message', [
				'msg' => 'You need to be login to create a list!'
			]);
			$result['msg'] = $messagesWidget->getView();

		} else {
			if (!$list_id) { // it's a new list
				try {
					App::$db->begin_transaction();
					$result['list_id'] = $this->lists_model->createList($user_id);
					$result['task_id'] = $this->tasks_model->createTask($result['list_id'], $task);
					if (!$this->auth_model->setActiveListToUser($result['list_id'], $user_id)) {
						App::$db->rollback();
						$result['error'] = 2;
						$messagesWidget = new MessagesWidget('message', [
							'msg' => 'Can not find user to set active list!'
						]);
						$result['msg'] = $messagesWidget->getView();
					} else {
						App::$db->commit();
					}

					$listsWidget = new ListsWidget('show_list', [
						'list_id' => $result['list_id'],
						'list_name' => 'default'
					]);
					$result['list_view'] = $listsWidget->getView();

					$listsWidget = new ListsWidget('add_task', [
						'task_id' => $result['task_id'],
						'task' => $task
					]);
					$result['task_view'] = $listsWidget->getView();

					$listsWidget = new ListsWidget('add_list', [
						'list_id' => $result['list_id'],
						'list_name' => 'default'
					]);
					$result['all_lists_add'] = $listsWidget->getView();
				} catch (\Exception $e) {
					$result['error'] = 2;
					$messagesWidget = new MessagesWidget('message', [
						'msg' => 'Database error! Try later!'
					]);
					$result['msg'] = $messagesWidget->getView();
				}
			} else {
				$result['task_id'] = $this->tasks_model->createTask($list_id, $task);
				$listsWidget = new ListsWidget('add_task', [
					'task_id' => $result['task_id'],
					'task' => $task
				]);
				$result['task_view'] = $listsWidget->getView();
			}
		}

		echo json_encode($result);
	}

	public function showEditList() {
		$view_name = 'edit_list';
		$listWidget = new ListsWidget($view_name, [
			'list_id' => htmlspecialchars($_GET['list_id']),
			'list_name' => htmlspecialchars($_GET['list_name'])
		]);
		echo $listWidget->getView();
	}

	public function goBack() {
		$listWidget = new ListsWidget('show_list', [
			'list_id' => htmlspecialchars($_GET['list_id']),
			'list_name' => htmlspecialchars($_GET['list_name'])
		]);
		echo $listWidget->getView();
	}

	public function getAllLists() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$user_id = (int) Session::get('id');
		if (!empty($user_id)) {
			$lists = $this->lists_model->getAllLists($user_id);
			$activeQuery = $this->auth_model->getActiveList((int) Session::get('id'));
			$activeListId = $activeQuery[0]['active_list_id'];
			if (count($lists)) {
				$listWidget = new ListsWidget('show_all_lists', [
					'lists' => $lists,
					'active_list' => $activeListId
				]);
				$result['msg'] = $listWidget->getView();
			} else {
				$result['error'] = 1;
				$messagesWidget = new MessagesWidget('message', [
					'msg' => "You don't have list! Please, add task to create it!"
				]);
				$result['msg'] = $messagesWidget->getView();
			}
		}

		echo json_encode($result);
	}

	public function updateListName() {
		header('Content-type:application/json');
		$result['error'] = 0;

        $user_id = (int) Session::get('id');
		$list_id = (int) $_POST['list_id'];
		$list_name = $_POST['list_name'];

		$duplicateList = $this->lists_model->getListByNameAndUserId($user_id, $list_name);
		if (count($duplicateList)) {
            $result['error'] = 1;
            $messagesWidget = new MessagesWidget('message', [
                'msg' => 'You cannot have two lists with the same name!'
            ]);
            $result['msg'] = $messagesWidget->getView();
        } else {
            if (!$this->lists_model->updateListName($list_id, $list_name)) {
                $result['error'] = 1;
                $messagesWidget = new MessagesWidget('message', [
                    'msg' => 'Database error! Try later!'
                ]);
                $result['msg'] = $messagesWidget->getView();
            } else {
                $listsWidget = new ListsWidget('show_list', [
                    'list_id' => $list_id,
                    'list_name' => htmlspecialchars($list_name)
                ]);
                $result['msg'] = $listsWidget->getView();
            }
        }

		echo json_encode($result);
	}

	public function changeActiveList() {
		header('Content-type:application/json');
		$result['error'] = 0;

		$list_id = (int) $_POST['list_id'];
		$user_id = (int) Session::get('id');
		if (!$this->auth_model->setActiveListToUser($list_id, $user_id)) {
			$result['error'] = 1;
		}

		echo json_encode($result);
	}

	public function changeTaskStatus() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$task_id = (int) $_POST['task_id'];
		$status = (int) $_POST['status'];
		if (!$this->tasks_model->changeTaskStatus($task_id, $status)) {
			$result['error'] = 1;
		}

		echo json_encode($result);
	}

	public function changeAllTasksStatus() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$status = (int) $_POST['status'];
		$list_id = (int) $_POST['list_id'];
		if (!$this->tasks_model->changeAllTasksStatus($list_id, $status)) {
			$result['error'] = 1;
		}

		echo json_encode($result);
	}

	public function removeTask() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$task_id = (int) $_POST['task_id'];

		if (!$this->tasks_model->removeTask($task_id)) {
			$result['error'] = 1;
		}

		echo json_encode($result);
	}

	public function removeTasks() {
		header('Content-type:application/json');
		$result['error'] = 0;
		$task_ids = rtrim(ltrim($_POST['task_ids'],'['), ']');

		if (!$this->tasks_model->removeTasks($task_ids)) {
			$result['error'] = 1;
		}

		echo json_encode($result);
	}
}