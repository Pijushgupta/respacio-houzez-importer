<?php

namespace RespacioHouzezImport;

use WP_Error;

class post {
	/**
	 * This to register image attachment as post type of attachment
	 *
	 * @param $postId
	 * @param $uploaded_url
	 * @param $file_name
	 * @param $flag
	 * @param $extention
	 *
	 * @return int|WP_Error
	 */
	public static function respacio_insert_post_data($postId,$uploaded_url,$file_name,$flag,$extention){

		global $wpdb;
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

		$post_attachment_id = wp_insert_post($post_array);

		$table_name = $wpdb->prefix . 'postmeta';
		$post_img = $wpdb->get_results("SELECT meta_id,meta_value FROM $table_name WHERE (post_id = ".$postId." AND meta_key = '_thumbnail_id')");

		if(!empty($post_attachment_id)){
			if(empty($post_img)){

				if(!empty($flag)){
					$insert_thumb = [
						'post_id'    =>	$postId,
						'meta_key'   => '_thumbnail_id',
						'meta_value' =>	$post_attachment_id
					];

					$wpdb->insert($table_name,$insert_thumb);

					$insert_thumb = [
						'post_id'    =>	$postId,
						'meta_key'   => 'fave_property_images',
						'meta_value' =>	$post_attachment_id
					];

					$wpdb->insert($table_name,$insert_thumb);
				}
			}
			else if(!empty($post_img) && empty($post_img[0]->meta_value)){
				$table_name = $wpdb->prefix . 'postmeta';
				$wpdb->update($table_name, [ 'meta_value' =>	$post_attachment_id ], [ 'meta_id' =>$post_img[0]->meta_id ] );
			}
			else
			{
				if(!empty($flag)){
					$insert_thumb = [
						'post_id'    =>	$postId,
						'meta_key'   => 'fave_property_images',
						'meta_value' =>	$post_attachment_id
					];

					$wpdb->insert($table_name,$insert_thumb);
				}
			}
		}
		return $post_attachment_id;
	}

	public static function respacio_add_post_metadata($attachment_id,$subdir,$file_name,$serialize_array,$extention){
		global $wpdb;
		$post_meta = [
			'post_id'    =>	$attachment_id,
			'meta_key'   =>	'_wp_attached_file',
			'meta_value' =>	$subdir.'/'.$file_name.'.'.$extention,
		];

		$table_name = $wpdb->prefix . 'postmeta';
		$wpdb->insert($table_name,$post_meta);

		$post_meta = [
			'post_id'    =>	$attachment_id,
			'meta_key'   =>	'_wp_attachment_metadata',
			'meta_value' =>	serialize($serialize_array),
		];

		$table_name = $wpdb->prefix . 'postmeta';
		$wpdb->insert($table_name,$post_meta);
	}

	public static function respacio_add_postmetadata($postId,$url,$image_sizes,$id){

		global $wpdb;

		if(!function_exists('wp_get_current_user')) {
			include( ABSPATH . 'wp-includes/pluggable.php' );
		}

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

				$attachment_id = post::respacio_insert_post_data($postId,$uploaded_url,$file_name,$id,$extention);
				$serialize_array = [
					'width'  =>	110,
					'height' =>	200,
					'file'   => $subdir . '/' . $file_name . '.' . $extention
				];
				foreach($image_sizes as $ims){

					$width = $ims['width'];
					$height = $ims['height'];
					$new_file_name = $file_name.'-'.$width.'x'.$height.'.'.$extention;
					$upload_dir = $res['path'] . '/' . $new_file_name;
					$img_url = $uploaded_url.'/'.$new_file_name;
					file_put_contents($upload_dir,$file_content);

					$image = wp_get_image_editor($upload_dir, [] );
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

				post::respacio_add_post_metadata($attachment_id,$subdir,$file_name,$serialize_array,$extention);
				if(!empty($id)){
					$table_name = $wpdb->prefix . 'property_images';
					$wpdb->update($table_name, [ 'is_download' =>1, 'image_id' =>$attachment_id ], [ 'id' =>$id ] );
				}
			}

			return $attachment_id;
		}
	}

	public static function respacio_update_property_link(){
		global $wpdb;

		$table_name = $wpdb->prefix . 'posts as p';
		$join = $wpdb->prefix . 'postmeta as pm';
		$post_img = $wpdb->get_results("SELECT p.ID,p.guid,pm.meta_value,p.post_name FROM $table_name left join $join on pm.post_id = p.ID WHERE p.post_type = 'property' and pm.meta_key = 'fave_property_id'");

		$api_key = get_option( 'property_verification_api');
		$data = [ 'property_friendly_url' =>	json_encode($post_img) ];
		$propData = wp_remote_post(RHIMO_PROPERTY_WEB_URL, [
			'method'      => 'POST',
			'timeout'     => 60,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'body'    => $data,
			'headers'     => [
				'authorization' => 'Basic YWRtaW46MTIzNA==',
				'x-api-key'     =>$api_key,
				'Content-Type'  => 'application/x-www-form-urlencoded'
			],
			'cookies' => []
		] );
	}

	public static function respacio_update_property_postmeta($postId,$meta_key,$meta_value){
		global $wpdb;

		if(!empty($meta_value)){
			$table_name = $wpdb->prefix . 'postmeta';
			$post_img = $wpdb->get_results("SELECT meta_id FROM $table_name WHERE (post_id = ".$postId." AND meta_key = '".$meta_key."')");


			if(!empty($post_img)){

				if( $meta_key != 'fave_video_image' ){
					$table_name = $wpdb->prefix . 'postmeta';
					$wpdb->update($table_name, [ 'meta_value' =>$meta_value ], [ 'meta_id' =>$post_img[0]->meta_id ] );
				}
			}
			else{

				if( $meta_key == 'fave_video_image' ){
					$url = $meta_value;
					$image_sizes = [
						[ 'width' =>	150, 'height' =>	150, 'type' => 'thumbnail' ],
						[ 'width' =>	300, 'height' =>	227, 'type' => 'medium' ],
						[ 'width' =>	150, 'height' =>	114, 'type' => 'post-thumbnail' ],
						[ 'width' =>	385, 'height' =>	258, 'type' => 'houzez-property-thumb-image' ],
						[ 'width' =>	380, 'height' =>	280, 'type' => 'houzez-property-thumb-image-v2' ],
						[ 'width' =>	570, 'height' =>	340, 'type' => 'houzez-image570_340' ],
						[ 'width' =>	810, 'height' =>	430, 'type' => 'houzez-property-detail-gallery' ],
						[ 'width' =>	350, 'height' =>	350, 'type' => 'houzez-image350_350' ],
						[ 'width' =>	150, 'height' =>	110, 'type' => 'thumbnail' ],
						[ 'width' =>	350, 'height' =>	9999, 'type' => 'houzez-widget-prop' ],
						[ 'width' =>	0, 'height' =>	480, 'type' => 'houzez-image_masonry' ],
					];


					$meta_value = post::respacio_add_postmetadata($postId,$url,$image_sizes,0);

				}

				$meta_add = [
					'post_id'    =>	$postId,
					'meta_key'   =>	$meta_key,
					'meta_value' =>	$meta_value
				];

				$wpdb->insert($table_name,$meta_add);
			}
		}
	}

	public static function recordSyncedProperty($postId = false){
		if($postId == false) return;
	
		// Check if a post with property_id meta value already exists
		$existing_post_id = get_posts(array(
			'post_type'      => 'property_log',
			'meta_key'       => 'property_id',
			'meta_value'     => $postId,
			'fields'         => 'ids',
			'posts_per_page' => 1, // We only need one post, if any
		));
	
		// If an existing post is found, update it
		if (!empty($existing_post_id)) {
			$existing_post_id = $existing_post_id[0]; // Extract the ID from the array
			$post_title = get_the_title($existing_post_id); // Get the title of existing post
			wp_update_post(array(
				'ID'          => $existing_post_id,
				'post_title'  => $post_title ? $post_title : '',
				'post_status' => 'publish',
			));
		} else {
			// If no existing post is found, insert a new one
			$post_title = get_the_title($postId);
			wp_insert_post(array(
				'post_title'  => $post_title ? $post_title : '',
				'post_status' => 'publish',
				'post_type'   => 'property_log',
				'meta_input'  => array(
					'property_id' => $postId,
				),
			));
		}
	}
	

	public static function getPropertyLog($offset,$numposts){
		$args = array(
			'post_type' => 'property_log', 
			'posts_per_page' => $numposts, // Number of posts per page
			'offset' => $offset, // Number of posts to skip
		);
		
		$query = new \WP_Query( $args );
		$data = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$data[] = array(
					'id'      => get_the_ID(),
					'title'   => get_the_title(),
					'content' => get_the_content(),
					'time'	  => get_the_time('F j, Y g:i a'),
					'property_id' => get_post_meta(get_the_ID(),'property_id',true)
					// Add more post data as needed
				);
			}
			wp_reset_postdata();
		}
		return $data;
		
	}

	public static function getTotalNumberOfLog(){
		$args = array(
			'post_type' => 'property_log',
			'post_status' => 'publish',
			'posts_per_page' => -1, // Set to -1 to retrieve all posts
		  );
		  
		  $query = new \WP_Query($args);
		  $total_posts = $query->found_posts;
		  return $total_posts;
	}
}