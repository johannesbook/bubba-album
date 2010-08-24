<?php

class Album_model extends Model {
	function __construct() {
		parent::Model();
		$this->load->helper('bubba_socket');
	}
	function album_create_album( $name, $caption, $parent, $public ) {
		$this->db->insert('album', array( 'name' => $name, 'caption' => $caption, 'public' => $public, 'parent' => $parent ) );
		return $this->db->insert_id();
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
	function get_meta( $album_id ) {
		$query = $this->db->get_where( 'album' ,array( 'id' => $album_id ) );
		if( $query->num_rows() > 0 ) {
			return $query->row_array();
		} else {
			return null;
		}
	}

	function get_count_subalbums() {
		$query = $this->db->query("select parent as id, count(id) as nbr from album group by parent");
		if( $query->num_rows > 0 ) {
			$result= $query->result_array();

			$res = array();
			foreach( $result as $row ) {
				if(isset( $row['id']) && !is_null($row['id'])) {
					$res[$row['id']] = $row['nbr'];
				}
			}
			return $res;

		}
	}
	function get_albums( $parent = null, $username = false, $manager = false ) {
		$parent_sql = is_null($parent) ? '`parent` IS NULL' : '`parent` = '.mysql_escape_string( $parent );
		if( $manager ) {
			$username_sql = 'TRUE';
		} elseif( !$username ) {
			$username_sql = 'FALSE';
		} else {
			$username_sql = '`access`.`username` = "'.mysql_escape_string( $username ).'"';
		}
		$query = $this->db->query('SELECT DISTINCT 
			`album`.`id` AS id, 
			`album`.`name` AS name, 
			`album`.`caption` AS caption, 
			`album`.`path` AS path, 
			`album`.`created` AS created, 
			`album`.`modified` AS modified, 
			`album`.`public` AS public,
			min(`image`.`id`) AS image_id,
			count(`image`.`id`) AS image_count
			FROM (`album`) 
			LEFT JOIN `image` ON `image`.`album` = `album`.`id` 
			LEFT JOIN `access` ON `access`.`album` = `album`.`id`
			WHERE 
			'.$parent_sql.'
			AND
			( '.$username_sql.' OR `album`.`public` = 1 ) 
			GROUP BY `album`.`id` 
			ORDER BY `album`.`name` desc
			', array( $parent, $username ) );

		if( $query->num_rows() > 0 ) {
			$arr = $query->result_array();
			foreach( $arr as &$album ) {
				$album['public'] = (boolean)$album['public'];
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

	function album_is_public( $album, $recursive = true ) {
		$parent = $album;
		$self_public = false;
		do {
			if( is_null($parent) ) {
				return true;
			}
			$query = $this->db->select('parent, public as is_public')->from('album')->where( array( 'id' =>  $parent ) );
			$result = $query->get()->row();
			if( $result->is_public == 0 ) {
				return false;
			} 

			if( $result->is_public == 1 && $parent == $album ) {
				$self_public = true;
			}
			$parent = $result->parent;
		} while ( !is_null($parent) && $recursive );
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
		array_unshift( $parents, "$album" );
		return $parents;
	}

	function get_album_names( $albums ) {
		if( is_null( $albums ) ) {
			return null;
		}
		if( !is_array( $albums ) ) {
			$albums = array( $albums );
		}
		$this->db->select( 'id, name' )->from('album')->where_in( 'id', $albums );
		$query = $this->db->get();

		return $query->num_rows() > 0 ? $query->result_array() : null; 
		
	}

	function user_has_access_to( $username, $album ) {
		foreach( $this->get_album_parents_and_self( $album ) as $album ) {
			if( !$this->_user_has_access_to( $username, $album ) ) {
				return false;
			}
		}
		return true;
	}

	function _user_has_access_to( $username, $album ) {
		if( $this->album_is_public( $album ) ) {
			return true;
		}
		$query = $this->db->select('album')->from('access')->where(array( 'username' => $username, 'album' => $album ))->get();
		return $query->num_rows() >  0;
	}

	function get_album_from_image( $image ) {
		$query = $this->db->select('album')->from('image')->where(array( 'id' => $image ))->get();
		if( $query->num_rows() == 0 ) {
			return false;
		}
		return $query->row()->album;
	}

	function user_has_access_to_image( $username, $image ) {
		$this->load->model('admin');
		if( $this->admin->has_manager_access() ) {
			return true;
		}
		$album = $this->get_album_from_image( $image );
		return $this->user_has_access_to( $username, $album );
	}

	function move_albums( $ids, $target ) {
		if($target == 'null') {
			return $this->db->query("UPDATE album SET parent = NULL WHERE id in {$this->_in($ids)}", array( $target ));
		} else {
			return $this->db->query("UPDATE album SET parent = ? WHERE id in {$this->_in($ids)}", array( $target ));
		}
	}
	function move_images( $ids, $target ) {
		if($target == 'null') {
			return $this->db->query("UPDATE image SET album = NULL WHERE id in {$this->_in($ids)}");
		} else {
			return $this->db->query("UPDATE image SET album = ? WHERE id in {$this->_in($ids)}", array( $target ));
		}
	}
	function delete_albums( $ids ) {
		return $this->db->query("DELETE FROM album WHERE id in {$this->_in($ids)}");
	}
	function delete_images( $ids ) {
		return $this->db->query("DELETE FROM image WHERE id in {$this->_in($ids)}");
	}
	function update_image_metadata( $id, $name, $caption ) {
		$this->db->update('image', array( 'name' => $name, 'caption' => $caption ), array( 'id' => $id ) );
		return $this->db->last_query();
	}
	function update_album_metadata( $id, $name, $caption ) {
		$this->db->update('album', array( 'name' => $name, 'caption' => $caption ), array( 'id' => $id ) );
		return $this->db->last_query();
	}	
	private function _in( $what ) {
		if( !is_array( $what ) ) {
			$what = array( $what );
		}
		$what = array_map( create_function('$a', 'return mysql_escape_string($a);'), $what );
		return '('.implode(',',$what).')';
	}
	function _batch_add_socket( $process ) {
		syslog( LOG_INFO, 'here' );
		$sock = new BubbaAlbumSocket();
		$ret = "";
		foreach( $process as $id => $file ) {
			$sock->say( json_encode( array( 'action' => 'add', 'id' => $id, 'file' => $file ) ) );
			$ret = $sock->getline();
		}
		$sock->close();
		return $ret;
	}
	function batch_add( $files, $album ) {
		$origdir = getcwd();
		$added = $this->_batch_add( $files, $album );
		chdir($origdir);
		return $added;
	}

	function _batch_add( $files, $parent = null ) {
		$added = array();
		$to_process = array();
		foreach( $files as $file ) {
			if( $file == '.' || $file == '..' ) {
				continue;
			}
			$file = rawurldecode( $file );
			if( is_dir( $file )) {
				$subalbum = basename( $file );
				$this->db->insert( 'album', array( 'name' => $subalbum, 'parent' => $parent, 'public' => false ) );
				$subalbum_id = $this->db->insert_id();
				chdir( $file );
				$added = array_merge( $added, $this->_batch_add( scandir( "." ), $subalbum_id ) );
				chdir ( '..' );
			} else {
				if( filesize( $file ) == 0 || exif_imagetype( $file ) != IMAGETYPE_JPEG ) {
					$added[realpath($file)] = false;
					continue;
				}
				$added[realpath($file)] = true;
				list( $width, $height ) = getimagesize( $file );
				$this->db->insert( 
					'image', 
					array( 
						'album' => $parent,  
						'name' => $file,
						'path' => realpath($file), 
						'width' => $width, 
						'height' => $height
					)
				);
				$image_id = $this->db->insert_id();
				$to_process[$image_id] = realpath($file);
			}
		}
		$this->_batch_add_socket( $to_process );
		return $added;
	}	

	function album_access_list( $album ) {
		$query = $this->db
			->select( 'username' )
			->from( 'access' )
			->where( 'album', $album)
			->get();

		$access_list = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$access_list[] = $row->username;
			}
		} 		
		return $access_list;
	}
	function modify_album_access( $album, $users, $recursive = false ) {

		if( !is_array( $users ) ) {
			return false;
		}
		
		// remove all user access from the album
		$retval = @$this->db->delete( 'access', array( 'album'=> $album) );
		foreach( $users as $user ) {
			$this->db->insert( 'access', array( 'album' => $album, 'username' => $user ) );
		}

		if( $recursive ) {
			$children = $this->db->select('id')->from('album')->where( array( 'parent' => $album ) )->get()->result();
			foreach( $children as $child ) {
				$retval &= $this->modify_album_access( $child->id, $users, $recursive );
			}
		}			
		return $retval;
	}	
	function album_set_public( $id, $public, $recursive = false ) {
		$retval = @$this->db->update('album', array( 'public' => $public ), array( 'id' => $id ) );
		if( $recursive ) {
			$children = $this->db->select('id')->from('album')->where( array( 'parent' => $id ) )->get()->result();
			foreach( $children as $child ) {
				$retval &= $this->album_set_public( $child->id, $public, $recursive );
			}
		}	
		return $retval;
	}
}
