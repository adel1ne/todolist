<?php

namespace models;

use system\Model;
use system\Config;

class Auth extends Model {
	protected $table_name = 'users';

	// 0 - ошибка БД
	// 1 - все ок, высылаем email (мб повторно)
	// 2 - пользователь уже зарегистрирован
	public function createUser($email, $passwd, $currentDate) {
		// Проверяем, есть ли такой пользователь в БД
		$sql = "SELECT * FROM ".$this->table_name." WHERE email='".$email."'";
		$result = $this->db->query($sql);
        $passwd_md5 = md5(Config::get('passwd_salt').$passwd);
		if (count($result)) {
			if (!(int) $result[0]['confirm']) {
                $sql = "UPDATE ".$this->table_name." SET passwd='".$passwd_md5."', create_date=NOW(),"
                    ." confirm_hash='".md5(Config::get('confirm_salt').$email.$currentDate)."'"
                    ." WHERE email='".$email."'";
                if ($this->db->query($sql)) {
                    return 1;
                } else {
                    return 0;
                }
			} else {
				return 2;
			}
		} else {
			$sql = "INSERT INTO ".$this->table_name
			       ." (email,passwd,create_date,confirm_hash) VALUES ('"
			       .$email."','".$passwd_md5."', NOW(),'".md5(Config::get('confirm_salt').$email.$currentDate)."')";
			if ($this->db->query($sql)) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	// 1 - все ок, активируем пользователя
	// 2 - ошибка БД
	// 3 - попытка активировать после 3-х дней
	// 4 - уже активирована
	public function confirmUser($hash) {
		$sql = "SELECT * FROM ".$this->table_name." WHERE confirm_hash='".$hash."'";
		$result = $this->db->query($sql);
		if (count($result)) {
			if (!(int) $result[0]['confirm']) {
				$expireDate = (new \DateTime($result[0]['create_date']))->modify('+3 day');
				if ($expireDate >= new \DateTime()) {
					$sql = "UPDATE ".$this->table_name." SET confirm=1 WHERE id=".$result[0]['id'];
					if ($this->db->query($sql)) {
						return 1;
					} else {
						return 2;
					}
				} else {
					return 3;
				}
			} else {
				return 4;
			}
		} else { // не палим, что такого хэша нет, просто выдаем ошибку БД
			return 2;
		}
	}

	public function getUser($email, $passwd) {
		$passwd_md5 = md5(Config::get('passwd_salt').$passwd);
		$sql = "SELECT * FROM ".$this->table_name." WHERE email='".$email."' AND passwd='".$passwd_md5."'";
		return $this->db->query($sql);
	}

	public function setActiveListToUser($list_id, $user_id) {
		$sql = "UPDATE ".$this->table_name." SET active_list_id=".$list_id." WHERE id=".$user_id;
		$this->db->query($sql);
		return $this->db->affected_rows();
	}

	public function getActiveList($user_id) {
		$sql = "SELECT active_list_id FROM ".$this->table_name." WHERE id=".$user_id;
		return $this->db->query($sql);
	}

    public function getConfirmShown($hash) {
        $sql = "SELECT id, confirm_shown FROM ".$this->table_name." WHERE confirm_hash='".$hash."'";
        return $this->db->query($sql);
    }

    public function confirmShown($user_id, $value) {
        $sql = "UPDATE ".$this->table_name." SET confirm_shown=".$value." WHERE id=".$user_id;
        $this->db->query($sql);
        return $this->db->affected_rows();
    }
}