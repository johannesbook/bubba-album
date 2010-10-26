<?php
class Create extends Controller {
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
			$users = $this->input->post('users');
			if($this->input->post('public')) {
				$public = true;
			} else {
				$public = false;
			}
			$album = json_decode($this->input->post('album'));
			$name = $this->input->post("name");
			$caption = $this->input->post("caption");

			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array());
				return;
			}

			if( $files ) {
				$files = array_map( array( $this, '_fixpath' ), $files );			
			}
			if( count( $files ) == 1 && file_exists( $files[0] ) && is_dir( $files[0] ) ) {
				// special wen we only mark an dir, we add it's content and not the dir itself
				$path = $files[0];
				$files = array();
				if ($dh = opendir($path)) {
					while (($file = readdir($dh)) !== false) {
						if( $file == '.'  || $file == '..' ) {
							continue;
						}
						$filename = $path . '/' . $file;
						$files[] = $filename;
					}
					closedir($dh);
				}
			}

			$error = true;
			if( $name ) {
				$new_album_id = $this->Album_model->album_create_album($name, $caption, $album, $public );
				if( $files && count($files) > 0 ) {
					$this->Album_model->batch_add( $files, $new_album_id, $public );
				}
				$error = false;
				if( $users && count( $users ) > 0 ) {
					$error |= !$this->Album_model->modify_album_access( $new_album_id, $users , true);
				}
			}
			$data = array();
			if( $error ) {
				$data['error'] = true;
			}
			print json_encode($data);
		}
}

