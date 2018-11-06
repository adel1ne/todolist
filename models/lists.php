<?php

namespace models;

use system\Model;

class Lists extends Model {
	protected $table_name = 'lists';

	public function createList($user_id) {
		$sql = "INSERT INTO ".$this->table_name." (list_name, create_date, owner_id) VALUES ('default', NOW(), ".$user_id.")";
		$this->db->query($sql);

		return $this->db->last_insert_id();
	}

	public function getAllLists($user_id) {
		$sql = "SELECT * FROM ".$this->table_name." WHERE owner_id=".$user_id." ORDER BY id DESC";

		return $this->db->query($sql);
	}

	public function getList($list_id) {
		$sql = "SELECT * FROM ".$this->table_name." WHERE id=".$list_id;

		return $this->db->query($sql);
	}

    public function getListByNameAndUserId($user_id, $list_name) {
        $sql = "SELECT * FROM ".$this->table_name." WHERE owner_id=".$user_id." AND list_name='".$this->db->escape($list_name)."'";

        return $this->db->query($sql);
    }

	public function updateListName($list_id, $list_name) {
		$sql = "UPDATE ".$this->table_name." SET list_name='".$this->db->escape($list_name)."' WHERE id=".$list_id;
		$this->db->query($sql);

		return $this->db->affected_rows();
	}
}