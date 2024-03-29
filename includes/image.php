<?php

namespace RespacioHouzezImport;

/**
 * Resposible for having Image related methods
 */
class image {
	/**
	 * this to have hook
	 * @return void
	 */
	public static function activate() {


		add_action('admin_init', [ 'RespacioHouzezImport\image','copyThumbToGallery' ],9999);
	}

	public static function copyThumbToGallery(){
		$url = $_SERVER['REQUEST_URI'];
		if(!str_contains($url,'page=respacio_houzez_import')) return;
		if(!str_contains($url,'flag=1')) return;

		global $wpdb;
		/**
		 * posts table name with prefix
		 */
		$table_name = $wpdb->prefix . 'posts';
		/**
		 * selecting posts where registered post type is 'property'
		 */
		$sql = 'SELECT ID FROM ' . $table_name . " WHERE post_type = 'property'";
		/**
		 * getting the result
		 */
		$all_properties = $wpdb->get_results($sql);

		/**
		 * post meta table name with prefix
		 */
		$postmeta = $wpdb->prefix . 'postmeta';

		/**
		 * if no properties exists
		 * as post in the database
		 */
		if(empty($all_properties)) return;

		/**
		 * iterating the posts of post type property
		 */
		foreach($all_properties as $p){
			/**
			 * getting the ID of a particular post
			 */
			$post_id = $p->ID;
			/**
			 * getting meta_id,post_id,meta_key,meta_value
			 * from post meta table where post id is $post_id and meta_key is '_thumbnail_id'
			 */
			$sql = 'SELECT meta_id,post_id,meta_key,meta_value FROM ' . $postmeta . ' WHERE post_id = ' . $post_id . " and meta_key = '_thumbnail_id'";

			/**
			 * getting the result
			 */
			$get_thumb = $wpdb->get_results($sql);

			/**
			 * if empty jump on next iteration of this loop
			 */
			if(empty($get_thumb)) continue;

			/**
			 * getting fav property image meta data of post $post_id
			 * from post meta table where post id is $post_id meta key is `fave_property_images` and meta value is
			 * $get_thumb[0]->meta_value
			 */
			$sql = 'SELECT meta_id,post_id,meta_key,meta_value FROM ' . $postmeta . ' WHERE post_id = ' . $post_id . " and meta_key = 'fave_property_images' and meta_value = " . $get_thumb[0]->meta_value;

			/**
			 * getting the data
			 */
			$check = $wpdb->get_results($sql);

			/**
			 * checking if the result is empty or not
			 * if empty then only execute rest of the code, otherwise jump on next iteration
			 */
			if(!empty($check)) continue;


			//$sql = "SELECT meta_id,post_id,meta_key,meta_value FROM ".$postmeta." WHERE post_id = ".$post_id." and meta_key = 'fave_property_images'";
			//$check = $wpdb->get_results($sql);

			$post_images = $wpdb->prefix . 'property_images';
			$sql = 'SELECT image_id FROM ' . $post_images . ' WHERE post_id = ' . $post_id . ' and type = 1';
			$property_images = $wpdb->get_results($sql);

			$sql = 'delete FROM ' . $postmeta . ' WHERE post_id = ' . $post_id . " and meta_key = 'fave_property_images'";
			$wpdb->get_results($sql);

			if(!empty($property_images)){
				foreach($property_images as $pi){

					$add_same_image = [
						'post_id'    =>	$post_id,
						'meta_key'   => 'fave_property_images',
						'meta_value' =>	$pi->image_id
					];

					$wpdb->insert($postmeta,$add_same_image);
				}
			}

		}
	}

	/**
	 * returns various image sizes
	 * @return array
	 */
	public static function respacio_get_image_sizes() {
		return [
			[ 'width' =>	300, 'height' =>	200, 'type' => 'medium' ],
			[ 'width' =>	1024, 'height' =>	683, 'type' => 'large' ],
			[ 'width' =>	150, 'height' =>	150, 'type' => 'thumbnail' ],
			[ 'width' =>	768, 'height' =>	512, 'type' => 'medium_large' ],
			[ 'width' =>	1536, 'height' =>	1024, 'type' => '1536x1536' ],
			[ 'width' =>	2048, 'height' =>	1366, 'type' => '2048x2048' ],
			[ 'width' =>	1170, 'height' =>	785, 'type' => 'houzez-gallery' ],
			[ 'width' =>	592, 'height' =>	444, 'type' => 'houzez-item-image-1' ],
			[ 'width' =>	758, 'height' =>	564, 'type' => 'houzez-item-image-4' ],
			[ 'width' =>	584, 'height' =>	438, 'type' => 'houzez-item-image-6' ],
			[ 'width' =>	900, 'height' =>	600, 'type' => 'houzez-variable-gallery' ],
			[ 'width' =>	120, 'height' =>	90, 'type' => 'houzez-map-info' ],
			[ 'width' =>	496, 'height' =>	331, 'type' => 'houzez-image_masonry' ],
		];
	}

	public static function respacio_download_video_image() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'property_images';
		$post_img = $wpdb->get_results("SELECT * FROM $table_name WHERE image_url != '' AND is_download = 0 AND type = 3 order by id asc limit 300");

		$image_sizes = self::respacio_get_image_sizes();

		if(!empty($post_img)){
			foreach($post_img as $key => $val){

				$url = $val->image_url;
				$postId = $val->post_id;
				$id = $val->id;
				self::respacio_add_imagepostmetadata($postId,$url,$image_sizes,$id);
			}
		}
	}

	public static function respacio_add_imagepostmetadata($postId,$url,$image_sizes,$id){

		//echo "<br> post id : ".$postId;
		global $wpdb;
		if(!empty($url)){
			$headers = get_headers($url);

			$attachment_id = '';
			if(!empty($headers) && $headers[0] == 'HTTP/1.1 200 OK' ){
				$request = wp_remote_get($url, [ 'timeout' => 7200000, 'httpversion' => '1.1' ] );
				$file_content = wp_remote_retrieve_body( $request );

				$res = wp_upload_dir();

				$file_obj = explode( '/',$url);
				$full_file_name = $file_obj[count($file_obj)-1];
				list($file_name,$extention) = explode( '.',$full_file_name);
				$upload_dir = $res['path'] . '/' . $file_name . '.' . $extention;
				$uploaded_url = $res['url'];
				$subdir = $res['subdir'];
				file_put_contents($upload_dir,$file_content);

				//INSERT INTO POST TABLE START //

				$post_array = [
					'post_author'    =>	1,
					'post_date'      =>	date( 'Y-m-d H:i:s' ),
					'post_date_gmt'  =>	date( 'Y-m-d H:i:s' ),
					'post_status'    =>	'inherit',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_name'      =>	$file_name,
					'post_parent'    =>	$postId,
					'guid'           => $uploaded_url . '/' . $file_name . '.' . $extention,
					'post_type'      => 'attachment',
					'post_mime_type' => 'image/jpg',
				];

				$attachment_id = wp_insert_post($post_array);
				// INSERT INTO POST TABLE END


				add_post_meta($postId,'fave_video_image',$attachment_id, true );

				foreach($image_sizes as $ims){

					$width = $ims['width'];
					$height = $ims['height'];
					$new_file_name = $file_name.'-'.$width.'x'.$height.'.'.$extention;
					$upload_dir = $res['path'] . '/' . $new_file_name;
					file_put_contents($upload_dir,$file_content);

					$image = wp_get_image_editor($upload_dir);
					if ( ! is_wp_error( $image ) ) {
						$image->resize( $width, $height, true );
						$image->save($upload_dir);
					}

					$serialize_array['sizes'][$ims['type']] = [
						'file'   =>	$new_file_name,
						'width'  =>	$width,
						'height' =>	$height,
					];
				}

				if( !empty($attachment_id) ){

					/** @noinspection PhpUndefinedVariableInspection */
					post::respacio_add_post_metadata($attachment_id,$subdir,$file_name,$serialize_array,$extention);

				}

				if(!empty($id)){
					$table_name = $wpdb->prefix . 'property_images';
					$wpdb->update($table_name, [ 'is_download' =>1, 'image_id' =>$attachment_id ], [ 'id' =>$id ] );
				}

			}

			return $attachment_id;
		}
	}

	public static function respacio_add_property_docdata($docId,$docPostId,$docUrl){

		global $wpdb;

		if(!empty($docUrl)){
			$docHeaders = get_headers($docUrl);

			$docAttachmentId = '';
			if(!empty($docHeaders) && $docHeaders[0] == 'HTTP/1.1 200 OK' ){

				$request = wp_remote_get($docUrl, [ 'timeout' => 7200000, 'httpversion' => '1.1' ] );
				$doc_content = wp_remote_retrieve_body( $request );

				$res = wp_upload_dir();
				$file_obj = explode( '/',$docUrl);
				$full_file_name = $file_obj[count($file_obj)-1];
				list($file_name,$extention) = explode( '.',$full_file_name);
				$upload_dir = $res['path'] . '/' . $file_name . '.' . $extention;
				$uploaded_url = $res['url'];
				$subdir = $res['subdir'];
				file_put_contents($upload_dir,$doc_content);

				// GET MIME TYPE OF FILE
				//$mimeType = mime_content_type($upload_dir);
				$mimeType = 'application/pdf';
				//INSERT INTO POST TABLE START
				$post_array = [
					'post_author'    =>	1,
					'post_date'      =>	date( 'Y-m-d H:i:s' ),
					'post_date_gmt'  =>	date( 'Y-m-d H:i:s' ),
					'post_title'     =>  $file_name,
					'post_status'    =>	'inherit',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_name'      =>	$file_name,
					'post_parent'    =>	$docPostId,
					'guid'           => $uploaded_url . '/' . $file_name . '.' . $extention,
					'post_type'      => 'attachment',
					'post_mime_type' =>	$mimeType
				];

				$post_attachment_id = wp_insert_post($post_array);
				//INSERT INTO POST TABLE END

				//INSERT INTO POST META TABLE START

				$post_meta = [
					'post_id'    =>	$post_attachment_id,
					'meta_key'   =>	'_wp_attached_file',
					'meta_value' =>	$subdir.'/'.$file_name.'.'.$extention
				];

				$table_name = $wpdb->prefix . 'postmeta';
				$wpdb->insert($table_name,$post_meta);

				$post_meta = [
					'post_id'    =>	$docPostId,
					'meta_key'   =>	'fave_attachments',
					'meta_value' =>	$post_attachment_id
				];

				$table_name = $wpdb->prefix . 'postmeta';
				$wpdb->insert($table_name,$post_meta);

				// INSERT INTO POST META TABLE END

				return $post_attachment_id ;
			}
		}
	}

	public static function respacio_add_property_documents(){

		global $wpdb;

		//GET DOCUMENTS FROM TABLES
		$table_name = $wpdb->prefix . 'property_images';
		$post_docs = $wpdb->get_results("SELECT * FROM $table_name WHERE is_download = 0 AND type = 2 order by id asc limit 300");

		$attachment_id = '' ;
		if(!empty($post_docs)){
			foreach($post_docs as $dKey => $dVal){
				$docUrl = $dVal->image_url;
				$docPostId = $dVal->post_id;
				$docId = $dVal->id;

				$attachment_id = image::respacio_add_property_docdata($docId,$docPostId,$docUrl);

				if(!empty($attachment_id)){
					$table_name = $wpdb->prefix . 'property_images';
					$wpdb->update($table_name, [ 'is_download' =>1, 'image_id' =>$attachment_id ], [ 'id' =>$docId ] );
				}
			}
		}
	}
	public static function respacio_houzez_delete_image($post_id,$image_id) {
		global $wpdb;
		
		$upload_dir = wp_upload_dir();
		$tbl = $wpdb->prefix . 'postmeta';
		$get_data = $wpdb->get_results("select meta_value from $tbl where post_id = ".$image_id." and meta_key = '_wp_attachment_metadata'");
		
		$basepath = $upload_dir['basedir'];
		if(!empty($get_data)){
			foreach($get_data as $g){
				$serialize_data = $g->meta_value;
				
				$unserialize = unserialize($serialize_data);
				$attachment = wp_get_original_image_path($image_id);
				$attach_arr = explode( '/',$attachment);
				unset($attach_arr[count($attach_arr)-1]);
				$file_path = implode( '/',$attach_arr);
				
				$main_file_path = $basepath.$unserialize['file'];
				
				if(file_exists($main_file_path)){
					unlink($main_file_path);
				}
				
				$other_attachment = $unserialize['sizes'];
				if(!empty($other_attachment)){
					foreach($other_attachment as $oa){
						
						$other_file_path = $basepath.$file_path.'/'.$oa['file'];
						
						if(file_exists($other_file_path)){
							unlink($other_file_path);
						}
					}
				}
			}
		}
		
		$table_name = $wpdb->prefix . 'property_images';
    	$wpdb->delete($table_name, [ 'post_id' =>$post_id, 'image_id' =>$image_id ] );
		
		$table_name = $wpdb->prefix . 'postmeta';
		$wpdb->delete($table_name, [ 'post_id' =>$post_id, 'meta_value' =>$image_id ] );
		
		$table_name = $wpdb->prefix . 'posts';
		$wpdb->delete($table_name, [ 'ID' =>$image_id ] );
	}

	public static function respacio_houzez_update_image_order($post_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'property_images';
		$check_image = $wpdb->get_results("select id from $table_name where is_download = 0 and post_id = ".$post_id);

		if(empty($check_image)){

			$get_all_images = $wpdb->get_results("select id,post_id,image_url,image_id,sequence from $table_name where type = 1 and is_download = 1 and post_id = ".$post_id . ' order by sequence asc' );

			if(!empty($get_all_images)){
				foreach($get_all_images as $key => $g){

					$image_id = $g->image_id;

					$table_name = $wpdb->prefix . 'postmeta';
					$sql = 'delete FROM ' . $table_name . ' WHERE post_id = ' . $post_id . ' and meta_value = ' . $image_id;
					$wpdb->get_results($sql);

					if($key == 0){
						//$meta_key = "_thumbnail_id";
						$add_same_image = [
							'post_id'    =>	$post_id,
							'meta_key'   => '_thumbnail_id',
							'meta_value' =>	$image_id
						];
						$table_name = $wpdb->prefix . 'postmeta';
						$wpdb->insert($table_name,$add_same_image);
					}
					$add_same_image = [
						'post_id'    =>	$post_id,
						'meta_key'   => 'fave_property_images',
						'meta_value' =>	$image_id
					];
					$table_name = $wpdb->prefix . 'postmeta';
					$wpdb->insert($table_name,$add_same_image);

				}
			}

		}

	}
}