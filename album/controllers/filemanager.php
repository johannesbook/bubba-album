<?php
class Filemanager extends Controller {
	function __construct() {
		parent::Controller();
		$this->load->helper('i18n');
		load_lang('en');
	}
	function json() {
			$this->load->model('admin');
			$this->load->model('Album_model');
			$subpath = $this->input->post('path');
			$modified_subpath = preg_replace("#(^|\/)\.\.?(\/|$)#", '/', $subpath);
			$modified_subpath = preg_replace("#^\/?pictures\/?#", '', $modified_subpath);
			$path = "/home/storage/pictures/$modified_subpath";
			header("Content-type: application/json");
			if( ! $this->admin->has_manager_access() ) {
				print json_encode(array(
					'meta' => array( 'permission_denied' => true ),
					'aaData' => array(),
					'root' => $subpath
					));
				return;
			}
			function formatBytes($bytes, $precision = 2) { 
				$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

				$bytes = max($bytes, 0); 
				$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
				$pow = min($pow, count($units) - 1); 

				$bytes /= (1 << (10 * $pow)); 

				return round($bytes, $precision) . ' ' . $units[$pow]; 
			}
			$data['meta']=array();
			$data['aaData'] = array();
			$data['root'] = preg_replace("#\/$#", '', "/pictures/$modified_subpath");

			if (file_exists($path) && is_dir($path) && is_readable($path)) {
				if ($dh = opendir($path)) {
					while (($file = readdir($dh)) !== false) {
						if( $file == '.'  || $file == '..' ) {
							continue;
						}
						$filename = $path . '/' . $file;
						$data['aaData'][] = array(
							filetype($filename),
							$file,
							date ("o-m-d H:i:s", filemtime($filename)),
							formatBytes(filesize($filename))
						);
					}
					closedir($dh);
				}
			} else {
				$data["meta"]["permission_denied"]=true;
			}
			
			print json_encode($data);			
	}
}
