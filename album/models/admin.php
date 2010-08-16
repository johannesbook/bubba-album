<?php
require_once "HTTP/Request.php";
class Admin extends Model {
	const TTL=10; // in seconds
	private $session_id = null;
	function __construct() {
		if( isset($_COOKIE['PHPSESSID']) ) {
			$this->session_id = $_COOKIE['PHPSESSID'];
		} elseif( isset($this->session) ) {
			$this->session_id = $this->session->userdata('phpsessid');
		}
		parent::Model();
	}

	public function login($username, $password) {
		$this->load->helper('cookie');
		$req = new HTTP_Request("http://localhost/admin/ajax_session/login");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->addPostData('username', $username);
		$req->addPostData('password', $password);
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			$decoded = json_decode($req->getResponseBody(), true);
			$success = isset($decoded['success']) && $decoded['success'];
			if( $success ) {
				$cookies = $req->getResponseCookies();
				foreach( $cookies as $cookie ) {
					if( $cookie['name'] == 'PHPSESSID' ) {
						$session_id = $cookie['value'];
						$success = $session_id;
						if( isset( $_COOKIE['PHPSESSID'] ) && $_COOKIE['PHPSESSID'] == $cookie['value'] ) {
							// do not overwrite the cookie, even as the cookies seems to be the same
							xcache_unset("session:{$session_id}:admin-userinfo");
							break;
						}
						setcookie('PHPSESSID', $cookie['value'],$cookie['expires'],$cookie['path']);
						$this->session->set_userdata('phpsessid', $cookie['value']);
						$this->session_id = $cookie['value'];
						xcache_unset("session:{$session_id}:admin-userinfo");
						break;
					}
				}
			}
			if(isset($session_id))	{
				xcache_unset("session:{$session_id}:album-access");
			}
			return $success;
		}

	}

	public function logout() {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/logout");
		$req->setMethod(HTTP_REQUEST_METHOD_GET);
		$req->addCookie("PHPSESSID", $this->session_id);
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			xcache_unset("session:{$this->session_id}:admin-userinfo");
			xcache_unset("session:{$this->session_id}:album-access");
			$this->session->sess_destroy();
			return true;
		}		
	}

	private function _get_userinfo($session_id) {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/get_userinfo");
		$req->setMethod(HTTP_REQUEST_METHOD_GET);
		$req->addCookie("PHPSESSID", $session_id);
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return null;
		} else {
			return json_decode($req->getResponseBody(), true);
		}
		return null;
	}

	public function get_userinfo( $force = false ) {
		if( $force ) {
			$session_id = $force;
		} else {
			$session_id = $this->session_id;
		}
		if( $session_id && $this->has_album_access() ) {
			if(!xcache_isset("session:{$session_id}:admin-userinfo")) {
				$list = $this->_get_userinfo($session_id);
				xcache_set("session:{$session_id}:admin-userinfo", $list, self::TTL);
			}
			return xcache_get("session:{$session_id}:admin-userinfo");
		} else {
			$list = $this->_get_userinfo($session_id);
			return array( 'groups' => array(), 'realname' => '', 'username' => $list['username'], 'logged_in' => false );
		}
	}

	public function is_logged_in() {
		$userinfo = $this->get_userinfo();
		return $userinfo['logged_in'];
	}
	public function modify_user( $username, $realname, $password ) {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/modify_user");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->addCookie("PHPSESSID", $this->session_id);
		$req->addPostData('username', $username);
		$req->addPostData('realname', $realname);
		$req->addPostData('password', $password);
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			$decoded = json_decode($req->getResponseBody(), true);
			$success = isset($decoded['success']) && $decoded['success'];
			return $success;
		}

	}

	public function add_user( $username, $realname, $password ) {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/add_user");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->addCookie("PHPSESSID", $this->session_id);
		$req->addPostData('username', $username);
		$req->addPostData('realname', $realname);
		$req->addPostData('password', $password);
		$req->addPostData('group', 'album');
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			$decoded = json_decode($req->getResponseBody(), true);
			$success = isset($decoded['success']) && $decoded['success'];
			return $success;
		}

	}
	

	public function del_user( $username ) {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/del_user");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->addCookie("PHPSESSID", $this->session_id);
		$req->addPostData('username', $username);
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			$decoded = json_decode($req->getResponseBody(), true);
			$success = isset($decoded['success']) && $decoded['success'];
			return $success;
		}
	}	

	public function user_exists( $username ) {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/user_exists");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->addCookie("PHPSESSID", $this->session_id);
		$req->addPostData('username', $username);
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			$decoded = json_decode($req->getResponseBody(), true);
			$success = isset($decoded['success']) && $decoded['success'];
			return $success;
		}
	}	

	public function get_album_users() {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/list_users");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->addCookie("PHPSESSID", $this->session_id);
		$req->addPostData('group', 'album');
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			$decoded = json_decode($req->getResponseBody(), true);
			$users= isset($decoded['users']) ? $decoded['users'] : array();
			return $users;
		}

	}
	private function _has_album_access() {
		$req = new HTTP_Request("http://localhost/admin/ajax_session/policy");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->addCookie("PHPSESSID", $this->session_id);
		$req->addPostData('policy', 'album');
		$req->addPostData('method', 'access');
		$response = $req->sendRequest();
		if (PEAR::isError($response)) {
			return false;
		} else {
			$decoded = json_decode($req->getResponseBody(), true);
			if( isset($decoded['success']) && !$decoded['success'] ) {
				return true;
			}
			if( isset($decoded['valid']) && $decoded['valid'] ) {
				return true;
			}
			return false;
		}
	}

	public function has_album_access() {
		if(! $this->session_id) return 1;
		if(!xcache_isset("session:{$this->session_id}:album-access")) {
			$access = $this->_has_album_access();
			xcache_set("session:{$this->session_id}:album-access", $access, self::TTL);
		}
		return xcache_get("session:{$this->session_id}:album-access");
	}

	public function has_manager_access() {
		$userinfo = $this->get_userinfo();
		return $this->has_album_access() && isset( $userinfo['groups']['bubba'] ) && $userinfo['groups']['bubba'];

	}
}

