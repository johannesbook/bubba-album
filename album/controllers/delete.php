<?php
class Delete extends Controller {
        function __construct() {
                parent::Controller();
                $this->load->helper('i18n');
                load_lang('en');
        }
		public function json() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$images = $this->input->post('images');
			$albums = $this->input->post('albums');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}

			if( $albums ) {
				$this->Album_model->delete_albums( $albums );
			}
			if( $images ) {
				$this->Album_model->delete_images( $images );
			}

			$data = array();
			print json_encode($data);
			$userinfo = $this->admin->get_userinfo();
		}
}

