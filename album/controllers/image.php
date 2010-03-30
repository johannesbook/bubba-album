<?php
class Image extends Controller {
	function __construct() {
		parent::Controller();
		$this->load->library('Auth');
	}
	function index() {
	}

	function _image_access( $id ) {
		$this->load->model("Album_model");		
		$album = $this->Album_model->get_album_from_image( $id );
		return $this->_album_access( $album );
	}

	function _album_access( $album ) {
		$this->load->model("Album_model");		

		if( $this->Album_model->album_is_public( $album ) ) {
			return true;
		}

		if( ! $this->auth->has_session() ) {
			return false;
		}

		if( ! $this->Album_model->user_has_access_to($this->session->userdata('user_id'), $album) ) {
			return false;
		}

		return true;

	}

	function blank( $aid = null ) {
		if( !is_null($aid) && $this->_album_access( $aid ) ) {
			$this->load->helper('album');
			$this->output->set_header('Content-Type: image/png');
			$this->output->set_header('Content-Disposition: inline; filename*=utf-8\'\''.rawurlencode('blank.png'));
			$path = 'views/_img/blank.png';
			if( cache_control( $path ) ) {
				$this->output->set_output(file_get_contents($path));
			}
		} else {
			redirect("image/locked");
		}
	}
	function locked() {
		$this->load->helper('album');
		$this->output->set_header('Content-Type: image/png');
		$this->output->set_header('Content-Disposition: inline; filename*=utf-8\'\''.rawurlencode('locked.png'));
		$path = 'views/_img/locked.png';
		if( cache_control( $path ) ) {
			$this->output->set_output(file_get_contents($path));
		}
	}

	function thumb( $id ) {
		if( $this->_image_access( $id ) ) {
			$this->load->helper('album');
			$this->load->model("Album_model");
			list( $name, $path ) = $this->Album_model->get_thumbnail( $id );
			$this->output->set_header('Content-Type: '. mime_content_type( $path ));
			$this->output->set_header('Content-Disposition: inline; filename*=utf-8\'\''.rawurlencode($name));
			if( cache_control( $path ) ) {
				$this->output->set_output(file_get_contents($path));
			}
		} else {
			redirect("image/locked");
		}
	}
	function medium( $id ) {
		if( $this->_image_access( $id ) ) {
			$this->load->helper('album');
			$this->load->model("Album_model");
			list( $name, $path ) = $this->Album_model->get_medium_image( $id );
			$this->output->set_header('Content-Type: '. mime_content_type( $path ));
			$this->output->set_header('Content-Disposition: inline; filename*=utf-8\'\''.rawurlencode($name));
			if( cache_control( $path ) ) {
				$this->output->set_output(file_get_contents($path));
			}
		} else {
			redirect("image/locked");
		}
	}
	function view( $id ) {
		$this->_full( $id, true );
	}
	function download( $id ) {
		$this->_full( $id, false );
	}
	function _full( $id, $inline = false ) {
		if( $this->_image_access( $id ) ) {
			$this->load->helper('album');
			$this->load->model("Album_model");
			list( $name, $path ) = $this->Album_model->get_full_image( $id );
			$this->output->set_header('Content-Type: '. mime_content_type( $path ));
			# XXX Question regarding original image should be inline or attachment
			$this->output->set_header('Content-Disposition: '.($inline?'inline':'attachment').'; filename*=utf-8\'\''.rawurlencode($name));
			if( cache_control( $path ) ) {
				$this->output->set_output(file_get_contents($path));
			}
		} else {
			redirect("image/locked");
		}
	}
}
