<?php

class Album_model extends Model {
	function __construct() {
		parent::Model();
	}

	function album_exists( $album_id ) {
		return $this->db->select( 'id' )->from( 'album' )->where( array( 'id' => $album_id ) )->get()->num_rows() > 0;
	}
	function album_name( $album_id ) {
		return $this->db->select( 'name' )->from( 'album' )->where( array( 'id' => $album_id ) )->get()->row()->name;
	}
	function album_caption( $album_id ) {
		return $this->db->select( 'caption' )->from( 'album' )->where( array( 'id' => $album_id ) )->get()->row()->caption;
	}

	function album_parent( $album_id ) {
		return $this->db->select( 'parent' )->from( 'album' )->where( array( 'id' => $album_id ) )->get()->row()->parent;
	}
	function get_albums( $parent = null, $uid = false ) {
		$query = $this->db->query('SELECT DISTINCT 
			`album`.`id` AS id, 
			`album`.`name` AS name, 
			`album`.`caption` AS caption, 
			`album`.`path` AS path, 
			`image`.`id` AS image_id 
			FROM (`album`) 
			LEFT JOIN `image` ON `image`.`album` = `album`.`id` 
			LEFT JOIN `access` ON `access`.`album` = `album`.`id`
			WHERE `parent` = ? AND (`access`.`user` = ? OR `album`.`public` = 1) 
			GROUP BY `album`.`id` 
			ORDER BY `album`.`name` desc
			', array( $parent, $uid ) );

		if( $query->num_rows() > 0 ) {
			$arr = $query->result_array();
			foreach( $arr as &$album ) {
				$aid = $album['id'];
				if(is_null($album['image_id'])) {
					$album['image_id'] = $this->get_first_subimage( $aid );
				}
			}

			return $arr;
		} else {
			return null;
		}
	}

	function get_first_subimage( $album ) {
		$this->db->select('album.id AS id, image.id AS image_id');
		$this->db->from( 'album' );
		$this->db->where( 'parent', $album );
		$this->db->join( 'image', 'image.album = album.id', 'left');
		$this->db->group_by('album.id');
		$this->db->distinct();
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$arr = $query->result_array();
			foreach( $arr as $row ) {
				$iid = $row['image_id'];
				if(!is_null($iid)) {
					return $iid;
				}
			}
			foreach( $arr as $row ) {
				$iid = $this->get_first_subimage( $row['id'] );
				if(!is_null($iid)) {
					return $iid;
				}
			}
		} else {
			return null;
		}
	}

	function get_album( $album ) {

		$this->db->select( 'image.id AS id, image.name AS name, image.caption AS caption' );
		$this->db->from( 'image' );
		$this->db->where( 'image.album', $album );

		$query = $this->db->get();

		return $query->num_rows() > 0 ? $query->result_array() : null; 

	}

	function get_nbr_images( $album ) {

		$this->db->select( 'count(id) as nbr' );
		$this->db->from( 'image' );
		$this->db->where( 'image.album', $album );

		$query = $this->db->get();

		return $query->num_rows() > 0 ? $query->row()->nbr : 0; 

	}	
	function get_thumbnail( $image ) {
		$this->load->helper('album');

		$this->db->select('image.path AS path, image.name AS name');
		$this->db->from(array('image'));
		$this->db->where('image.id', $image);

		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$path = $query->row()->path;
			$name = $query->row()->name;
			$id = $image;

			$thumb_path = get_thumb_path( $id );
			if( ! file_exists( $thumb_path ) ) {
				create_thumb( get_image_path( $path ), $thumb_path );
			}
			return array( $name, $thumb_path );
		}
	}
	function get_medium_image( $image ) {
		$this->load->helper('album');

		$this->db->select('image.path AS path, image.name AS name');
		$this->db->from(array('image'));
		$this->db->where('image.id', $image);

		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$path = $query->row()->path;
			$name = $query->row()->name;
			$id = $image;

			$thumb_path = get_rescaled_path( $id );
			if( ! file_exists( $thumb_path ) ) {
				create_rescaled( get_image_path( $path ), $thumb_path );
			}
			return array( $name, $thumb_path );
		}
	}
	function get_full_image( $image ) {
		$this->load->helper('album');

		$this->db->select('image.path AS path, image.name AS name');
		$this->db->from(array('image'));
		$this->db->where('image.id', $image);

		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$path = $query->row()->path;
			$name = $query->row()->name;
			return array( $name, get_image_path( $path ) );
		}
	}

	function get_image_data( $image ) {
		$this->db->select('name, value')->from('exif')->where('image', $image);

		$query = $this->db->get();
		$data['exif'] = $query->result_array();

		$this->db->select('name, caption, album, width, height')->from('image')->where('id', $image)->limit(1);

		$query = $this->db->get();
		$row = $query->row();
		$data['name'] = $row->name;
		$data['caption'] = $row->caption;
		$data['album'] = $row->album;
		$data['width'] = $row->width;
		$data['height'] = $row->height;

		return $data;
	}

	function album_is_public( $album ) {
		$parent = $album;
		$self_public = false;
		do {
			if( is_null($parent) ) {
				return true;
			}
			$query = $this->db->select('parent, public')->from('album')->where( array( 'id' =>  $parent ) );
			$result = $query->get()->row();
			if( $result->public == 0 ) {
				return false;
			} 

			if( $result->public == 1 && $parent == $album ) {
				$self_public = true;
			}
			$parent = $result->parent;
		} while ( !is_null($parent) );
		return true;
	}

	function get_album_parents( $album ) {
		$parent = $album;
		$parents = array();
		do {
			$parent = $this->db->select('parent')->from('album')->where( array( 'id' =>  $parent ) )->get()->row()->parent;
			if( !is_null($parent) ) {
				$parents[] = $parent;
			}
		} while ( !is_null($parent) );
		return $parents;
	}

	function get_album_parents_and_self( $album ) {
		$parents = $this->get_album_parents( $album );
		array_unshift( $parents, $album );
		return $parents;
	}

	function user_has_access_to( $uid, $album ) {
		foreach( $this->get_album_parents_and_self( $album ) as $album ) {
			if( !$this->_user_has_access_to( $uid, $album ) ) {
				return false;
			}
		}
		return true;
	}

	function _user_has_access_to( $uid, $album ) {
		if( $this->album_is_public( $album ) ) {
			return true;
		}
		$query = $this->db->select('album')->from('access')->where(array( 'user' => $uid, 'album' => $album ))->get();
		return $query->num_rows() >  0;
	}

	function get_album_from_image( $image ) {
		$query = $this->db->select('album')->from('image')->where(array( 'id' => $image ))->get();
		if( $query->num_rows() == 0 ) {
			return false;
		}
		return $query->row()->album;
	}

	function user_has_access_to_image( $uid, $image ) {
		$album = $this->get_album_from_image( $image );
		return $this->user_has_access_to( $uid, $album );
	}
}
