<?php
class Album extends Controller {
	function __construct() {
		parent::Controller();
		$this->load->library('Auth'); 
	}
	function index() {
		$this->load->model("Album_model");
		$albums = $this->Album_model->get_albums(null, $this->session->userdata('user_id'));
		$data['albums'] = $albums;
		$this->layout->setLayout('album_layout', array('title' => "BUBBA|TWO PHOTO GALLERY"));
		$this->layout->view('album_index_view', $data);
	}
	function login() {
		$this->load->helper(array('form', 'url'));
		$this->load->library('validation');
		$data["album"] = $this->uri->segment(3);
		if(!$data["album"]) {
			// if not set in uri, is this called from a faild login?
			$data["album"] = $this->input->post('album');
		}
		$rules['username']	= "callback__check_login";
		$rules['password']	= "required";
		$this->validation->set_rules($rules);
		if ($this->validation->run()) {
			if($data["album"]) {
				redirect('album/section/'.$data["album"]);
			} else {
				redirect('album/index/');
			}
		} else {
			$this->layout->setLayout('album_layout', array('title' => "Album login", 'hide_header_right' => true));
			$this->layout->view('album_login',$data);
		}
	}
	function logout() {
		$this->auth->logout();
		redirect('album/index');
	}
	function _check_login($username) {
		$password = $this->input->post('password');
		if ($this->auth->login($username, $password)) {
			return TRUE;
		} else {
			$this->validation->set_message('_check_login', 'Incorrect login info.');
			return FALSE;
		}
	} 
	function no_access() {
			$this->layout->setLayout('album_layout', array('title' => "Access error"));
			$this->layout->view('album_no_access');
	}
	function section( $album ) {
		$this->load->model("Album_model");
		if( ! $this->Album_model->album_exists( $album ) ) {
				redirect('album/index');
		}
		if( ! $this->Album_model->album_is_public( $album ) ) {
			if( ! $this->auth->has_session() ) {
				redirect('album/login/'.$album);
			} elseif ( !$this->Album_model->user_has_access_to($this->session->userdata('user_id'), $album) ) {
				redirect('album/no_access');
			}
		}

		$data['this_album'] = $album;
		$data['name'] = $this->Album_model->album_name( $album );
		$data['caption'] = $this->Album_model->album_caption( $album );
		$parent = $this->Album_model->album_parent( $album );
		$data['albums'] = $this->Album_model->get_albums( $album , $this->session->userdata('user_id'));
		$data['images'] = $this->Album_model->get_album( $album );

		$this->layout->setLayout('album_layout', array('title' => "Photo Gallery: '" . $data['name'] ."'", 'parent' => $parent));
		$this->layout->view('album_section_view', $data);
	}
}
?>
