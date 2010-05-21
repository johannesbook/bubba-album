<?php
class Modify extends Controller {
        function __construct() {
                parent::Controller();
                $this->load->helper('i18n');
                load_lang('en');
		}
		public function json() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$id = json_decode($this->input->post('id'));
			$name = $this->input->post('name');
			$caption = $this->input->post('caption');
			$type = $this->input->post('type');
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}
			$error = false;
			if( $type == 'album' ) {
				$this->Album_model->update_album_metadata( $id, $name, $caption );
			} elseif( $type == 'image' ) {
				$this->Album_model->update_image_metadata( $id, $name, $caption );
			} else {
				$error = true;
			}
			$data['error'] = $error;

			print json_encode($data);
		}
}

