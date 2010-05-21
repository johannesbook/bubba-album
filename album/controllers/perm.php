<?php
class Perm extends Controller {
        function __construct() {
                parent::Controller();
                $this->load->helper('i18n');
                load_lang('en');
		}
		public function show_access() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$albums = $this->input->post('albums');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$error = true;
			if( is_array($albums) && count( $albums ) > 0 ) {
				$users = $this->admin->get_album_users();
				$current_access = $this->Album_model->album_access_list( $albums[0] );

				foreach( $users as &$user ) {
					$user['access'] = in_array( $user['username'], $current_access );
				}
				unset($user);

				$data['users'] = $users;
				$data['public'] = $this->Album_model->album_is_public( $albums[0], false );
				$error = false;
			}

			$data['error'] = $error;

			print json_encode($data);
		}

		public function update_access() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$albums = $this->input->post('albums');
			$users = $this->input->post('users');
			$public = json_decode($this->input->post('public'));
			$recursive = json_decode($this->input->post('recursive'));

			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$error = true;
			if( is_array($albums) && count( $albums ) > 0 ) {
				$error |= !$this->Album_model->modify_album_access( $albums[0], $users, $recursive );
				$error |= !$this->Album_model->album_set_public( $albums[0], $public, $recursive );
				$data['users'] = $users;
				$error = false;
			}
			$data['error'] = $error;

			print json_encode($data);			
		}
		
}

