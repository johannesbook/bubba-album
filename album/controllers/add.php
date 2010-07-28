<?php
class Add extends Controller {
        function __construct() {
                parent::Controller();
                $this->load->helper('i18n');
                load_lang('en');
		}
		private function _fixpath($path) {
			$path = preg_replace("#(^|\/)\.\.?(\/|$)#", '/', $path);
			$path = preg_replace("#^\/?pictures\/?#", '', $path);
			return "/home/storage/pictures/$path";
		}
		public function json() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$files = $this->input->post('files');
			$album = json_decode($this->input->post('album'));
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}

			if( $files ) {
				$files = array_map( array( $this, '_fixpath' ), $files );
				$this->Album_model->batch_add( $files, $album );
			}

			$data = array();
			print json_encode($data);
		}
}

