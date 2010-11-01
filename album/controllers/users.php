<?php
class Users extends Controller {
        function __construct() {
                parent::Controller();
                $this->load->helper('i18n');
                load_lang('en');
		}
		public function list_users() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$users = $this->admin->get_album_users();
			$data['users'] = $users;

			print json_encode($data);
		}
		public function set_manager_mode() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$manager_mode = json_decode($this->input->post('manager_mode'));
			$this->session->set_userdata('manager_mode', $manager_mode);

			print json_encode(array());
		}

		public function check_manager_mode() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			header("Content-type: application/json");
			$response['manager_mode'] = $this->admin->has_manager_access();
			print json_encode($response);
		}
		
		public function username_free() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$username=strtolower(trim($this->input->post('username')));
			$res = !$this->admin->user_exists( $username );
			if( !$res ) {
				$res = t('username-exists', $username);
			}
			print json_encode( $res );
		}
		public function edit() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$username = $this->input->post('username');
			$realname = $this->input->post('realname');
			$password1 = $this->input->post('password1');
			$password2 = $this->input->post('password2');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$error = false;
			if( $password2 == $password1 ) {
				$this->admin->modify_user( $username, $realname, $password1 );
			} else {
				$error = true;
			}
			$data['error'] = $error;

			print json_encode($data);
			
		}
		public function add() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$username = $this->input->post('username');
			$realname = $this->input->post('realname');
			$password1 = $this->input->post('password1');
			$password2 = $this->input->post('password2');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$error = false;
			if( $password2 == $password1 ) {
				$this->admin->add_user( $username, $realname, $password1 );
			} else {
				$error = true;
			}
			$data['error'] = $error;

			print json_encode($data);
			
		}		
		public function del() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$username = $this->input->post('username');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$error = false;
			$error = !$this->admin->del_user( $username );
			$data['error'] = $error;

			print json_encode($data);
			
		}				
}

