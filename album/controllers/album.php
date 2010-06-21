<?php
class Album extends Controller {
	function __construct() {
		parent::Controller();
		$this->load->helper('i18n');
		load_lang('en');
	}
	function json() {
		$this->load->model("Album_model");
		$this->load->model('admin');
		header("Content-type: application/json");
		$has_access = $this->admin->has_album_access();
		if(!$has_access) {
			echo json_encode(array("error" => "access_denied"));
			return;
		}
		$userinfo = $this->admin->get_userinfo();

		$album = json_decode($this->input->post('path'));
		$manager_mode = json_decode($this->input->post('manager_mode'));
		$username = false;
		if( !isset( $userinfo['groups']['bubba'] ) ) {
			$manager_mode = false;
			if( isset( $userinfo['groups']['album'] ) ) {
				$username = $userinfo['username'];
			}
		}

		$albums = $this->Album_model->get_albums($album, $username, $manager_mode);
		if( !is_null( $album ) ) {
			$parents = $this->Album_model->get_album_parents($album);

			$parent_album_numbers = $this->Album_model->get_album_parents_and_self($album);
			$parent_album_data = $this->Album_model->get_album_names( $parent_album_numbers );
			$parent_albums = array();
			foreach( $parent_album_numbers as $idx ) {
				foreach( $parent_album_data as $data ) {
					if( $data['id'] == $idx ) {
						array_unshift( $parent_albums, $data );
						continue 2;
					}
				}
			}
		} else {
			$parents =null;
			$parent_albums = array();
		}

		if( $albums && is_array( $albums ) && count( $albums ) > 0 ) {
			$count_subalbums = $this->Album_model->get_count_subalbums();
			foreach( $albums as &$cur_album ) {
				if( isset( $count_subalbums[$cur_album['id']] ) ) {
					$cur_album['subalbum_count'] = $count_subalbums[$cur_album['id']];
				}
			}		
			unset( $cur_album );
		}
		$images = $this->Album_model->get_album($album);

		$meta = $this->Album_model->get_meta($album);
		$data['albums'] = is_null($albums) ? array() : $albums;
		$data['images'] = is_null($images) ? array() : $images;
		$data['parents'] = is_null($parents) ? array() : $parents;
		$data['parent_albums'] = is_null($parent_albums) ? array() : $parent_albums;
		$data['meta'] = $meta;
		$data['root'] = $album;

		echo json_encode( $data );
		return;

	}
	function index() {
		$this->load->model("Album_model");
		$this->load->model('admin');

		//$this->admin->login('test','test');
		$has_access = $this->admin->has_album_access();
		$userinfo = $this->admin->get_userinfo();
		$data['manager_access'] = $this->admin->has_manager_access();
		$albums = $this->Album_model->get_albums(null, $userinfo['username'], $this->session->userdata('manager_mode') && $data['manager_access']);
		$data['albums'] = $albums;

		$this->layout->setLayout('album_layout', array(
			'title' => "BUBBA PHOTO GALLERY", 
			'userinfo' => $userinfo,
			'has_access' => $has_access,
			'head' => $this->load->view('album_index_head_view',$data,true) 
		));
		$this->layout->view('album_index_view', $data);
	}
}
