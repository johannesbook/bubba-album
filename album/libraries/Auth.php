<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Based on Erkanaauth
 */
class Auth {
	var $CI;

	function Auth() {
		$this->CI = &get_instance();
		log_message('debug', 'Authorization class initialized.');
		$this->CI->load->library('session');
	}
 
	function login($username = null, $password = null) {
		$this->CI->load->helper('security');
		$query = $this->CI->db->select('id')->getwhere('users', array( 'username' => $username, 'password' => dohash($password, 'md5') ), 1, 0);
		if ($query->num_rows != 1) {
			return FALSE;
		} else {
			$row = $query->row();
			$this->CI->session->set_userdata(array('user_id'=>$row->id));
			return TRUE;
		}
	}

	function has_session() {
		if ($this->CI->session->userdata('user_id')) {
			$query = $this->CI->db->query('SELECT COUNT(*) AS total FROM users WHERE id = ' . $this->CI->session->userdata('user_id'));
			$row = $query->row();
			if ($row->total != 1) {
				// Bad session - kill it
				$this->logout();
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}

	function logout() {
		$this->CI->session->set_userdata(array('user_id'=>FALSE));
	}

	function getField($field = '') {
		$this->CI->db->select($field);
		$query = $this->CI->db->getwhere('users', array('id'=>$this->CI->session->userdata('user_id')), 1, 0);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			return $row->$field;
		}
	}

	function getRole() {
		$this->CI->db->select('roles.name');
		$this->CI->db->join('roles', 'users.role_id = roles.id');
		$query = $this->CI->db->getwhere('users', array('users.id'=>$this->CI->session->userdata('user_id')), 1, 0);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			return $row->name;
		}
	}

}
