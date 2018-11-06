<?php

if (!function_exists('regexp_email')) {
	function regexp_email($email) {
		if(preg_match('/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/', $email)) {
			return true;
		}
		return false;
	}
}

if (!function_exists('regexp_passwd')) {
	function regexp_passwd($passwd) {
		if(preg_match('/^[A-Za-z0-9_-]{4,20}$/', $passwd)) {
			return true;
		}
		return false;
	}
}

if (!function_exists('regexp_hash')) {
	function regexp_hash($hash) {
		if(preg_match('/^[a-f0-9]{32}$/i', $hash)) {
			return true;
		}
		return false;
	}
}

if (!function_exists('regexp_view_name')) {
	function regexp_view_name($str) {
		if(preg_match('/^[a-z_]+$/i', $str)) {
			return true;
		}
		return false;
	}
}