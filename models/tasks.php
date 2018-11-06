<?php

namespace models;

use system\Model;

class Tasks extends Model {
	protected $table_name = 'tasks';

	public function createTask($list_id, $task) {
		$sql = "INSERT INTO ".$this->table_name." (task_name, list_id, create_date) VALUES ('".$this->db->escape($task)."', ".$list_id.", NOW())";
		$this->db->query($sql);

		return $this->db->last_insert_id();
	}

	public function getAllTasks($list_id) {
		$sql = "SELECT * FROM ".$this->table_name." WHERE list_id=".$list_id;

		return $this->db->query($sql);
	}

	public function changeTaskStatus($task_id, $status) {
		$sql = "UPDATE ".$this->table_name." SET status=".$status." WHERE id=".$task_id;
		$this->db->query($sql);

		return $this->db->affected_rows();
	}

	public function changeAllTasksStatus($list_id, $status) {
		$sql = "UPDATE ".$this->table_name." SET status=".$status." WHERE list_id=".$list_id;
		$this->db->query($sql);

		return $this->db->affected_rows();
	}

	public function removeTask($task_id) {
		$sql = "DELETE FROM ".$this->table_name."  WHERE id=".$task_id;
		$this->db->query($sql);

		return $this->db->affected_rows();
	}

	public function removeTasks($task_id) {
		$sql = "DELETE FROM ".$this->table_name."  WHERE id in (".$this->db->escape($task_id).")";
		$this->db->query($sql);

		return $this->db->affected_rows();
	}
}