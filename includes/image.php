<?php

namespace RespacioHouzezImport;

class image {
	/**
	 * this to have hook
	 * @return void
	 */
	public static function activate(){


		add_action('admin_init',array('RespacioHouzezImport\image','copyThumbToGallery'),9999);
	}

	public static function copyThumbToGallery(){
		$url = $_SERVER['REQUEST_URI'];
		if(!str_contains($url,'page=respacio_houzez_import')) return;
		if(!str_contains($url,'flag=1')) return;

		global $wpdb;
		/**
		 * posts table name with prefix
		 */
		$table_name = $wpdb->prefix."posts";
		/**
		 * selecting posts where registered post type is 'property'
		 */
		$sql = "SELECT ID FROM ".$table_name." WHERE post_type = 'property'";
		/**
		 * getting the result
		 */
		$all_properties = $wpdb->get_results($sql);

		/**
		 * post meta table name with prefix
		 */
		$postmeta = $wpdb->prefix."postmeta";

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
			$sql = "SELECT meta_id,post_id,meta_key,meta_value FROM ".$postmeta." WHERE post_id = ".$post_id." and meta_key = '_thumbnail_id'";

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
			$sql = "SELECT meta_id,post_id,meta_key,meta_value FROM ".$postmeta." WHERE post_id = ".$post_id." and meta_key = 'fave_property_images' and meta_value = ".$get_thumb[0]->meta_value;

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

			$post_images = $wpdb->prefix."property_images";
			$sql = "SELECT image_id FROM ".$post_images." WHERE post_id = ".$post_id." and type = 1";
			$property_images = $wpdb->get_results($sql);

			$sql = "delete FROM ".$postmeta." WHERE post_id = ".$post_id." and meta_key = 'fave_property_images'";
			$wpdb->get_results($sql);

			if(!empty($property_images)){
				foreach($property_images as $pi){

					$add_same_image = array(
						"post_id"	=>	$post_id,
						"meta_key"	=>	"fave_property_images",
						"meta_value"	=>	$pi->image_id
					);

					$wpdb->insert($postmeta,$add_same_image);
				}
			}

		}
	}

	public static function respacio_get_image_sizes(){
		$image_sizes = array(
			array("width"	=>	300,"height"	=>	200,"type"	=>	"medium"),
			array("width"	=>	1024,"height"	=>	683,"type"	=>	"large"),
			array("width"	=>	150,"height"	=>	150,"type"	=>	"thumbnail"),
			array("width"	=>	768,"height"	=>	512,"type"	=>	"medium_large"),
			array("width"	=>	1536,"height"	=>	1024,"type"	=>	"1536x1536"),
			array("width"	=>	2048,"height"	=>	1366,"type"	=>	"2048x2048"),
			array("width"	=>	1170,"height"	=>	785,"type"	=>	"houzez-gallery"),
			array("width"	=>	592,"height"	=>	444,"type"	=>	"houzez-item-image-1"),
			array("width"	=>	758,"height"	=>	564,"type"	=>	"houzez-item-image-4"),
			array("width"	=>	584,"height"	=>	438,"type"	=>	"houzez-item-image-6"),
			array("width"	=>	900,"height"	=>	600,"type"	=>	"houzez-variable-gallery"),
			array("width"	=>	120,"height"	=>	90,"type"	=>	"houzez-map-info"),
			array("width"	=>	496,"height"	=>	331,"type"	=>	"houzez-image_masonry"),
		);

		return $image_sizes;
	}
}