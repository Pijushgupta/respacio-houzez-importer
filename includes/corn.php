<?php /** @noinspection PhpEnforceDocCommentInspection */
/** @noinspection PhpMissingStrictTypesDeclarationInspection */

/** @noinspection PhpArraySearchInBooleanContextInspection */

namespace RespacioHouzezImport;

use Exception;
use WP_Query;

class corn
{
	public static function activate(){
		add_filter('cron_schedules', [ '\RespacioHouzezImport\corn', 'addCronEvents' ] );

		if (!wp_next_scheduled('add_daily_properties')) {
			wp_schedule_event(time(), 'every_eleven_minutes', 'add_daily_properties');
		}
		add_action('add_daily_properties', [ '\RespacioHouzezImport\corn', 'respacio_sync_properties' ] );

		if (!wp_next_scheduled('import_images_trigger')) {
			wp_schedule_event(time(), 'every_five_minutes', 'import_images_trigger');
		}
		add_action('import_images_trigger',  [ '\RespacioHouzezImport\corn', 'respacio_add_property_image_hourly' ] );

		if (!wp_next_scheduled('property_attachment_download')) {
			wp_schedule_event(time(), 'every_sixty_minutes', 'property_attachment_download');
		}
		add_action('property_attachment_download', [ '\RespacioHouzezImport\image', 'respacio_add_property_documents' ] );

		if (!wp_next_scheduled('add_hourly_properties_url')) {
			wp_schedule_event(time(), 'every_sixty_minutes', 'add_hourly_properties_url');
		}
		add_action('add_hourly_properties_url', [ '\RespacioHouzezImport\post', 'respacio_update_property_link' ] );

		if (!wp_next_scheduled('add_video_images')) {
			wp_schedule_event(time(), 'every_ninteen_minutes', 'add_video_images');
		}
		add_action('add_video_images', [ '\RespacioHouzezImport\image', 'respacio_download_video_image' ] );
	}

	/**
	 * declear all cron events
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public static function addCronEvents($schedules)
	{

		$schedules['every_three_minutes'] = [
			'interval'  => 180,
			'display'   => __('Every 3 Minutes', 'respacio-admin')
		];
		$schedules['every_five_minutes'] = [
			'interval'  => 300,
			'display'   => __('Every 5 Minutes', 'respacio-admin')
		];
		$schedules['every_seven_minutes'] = [
			'interval'  => 420,
			'display'   => __('Every 7 Minutes', 'respacio-admin')
		];
		$schedules['every_eleven_minutes'] = [
			'interval'  => 660,
			'display'   => __('Every 11 Minutes', 'respacio-admin')
		];
		$schedules['every_thirteen_minutes'] = [
			'interval'  => 780,
			'display'   => __('Every 13 Minutes', 'respacio-admin')
		];
		$schedules['every_fifteen_minutes'] = [
			'interval'  => 900,
			'display'   => __('Every 15 Minutes', 'respacio-admin')
		];
		$schedules['every_seventeen_minutes'] = [
			'interval'  => 1020,
			'display'   => __('Every 17 Minutes', 'respacio-admin')
		];
		$schedules['every_ninteen_minutes'] = [
			'interval'  => 1140,
			'display'   => __('Every 19 Minutes', 'respacio-admin')
		];
		$schedules['every_thirty_minutes'] = [
			'interval'  => 1800,
			'display'   => __('Every 30 Minutes', 'respacio-admin')
		];
		$schedules['every_sixty_minutes'] = [
			'interval'  => 3600,
			'display'   => __('Every 60 Minutes', 'respacio-admin')
		];
		return $schedules;
	}

	//TODO: refactor

	/** @noinspection PhpComplexFunctionInspection
	 * @noinspection PhpParamsInspection
	 * @noinspection PhpUndefinedVariableInspection
	 *
	 */
	public static function  respacio_sync_properties(){
        $sa_apikey_verify = get_option( 'verify_api');
		include( ABSPATH . 'wp-includes/pluggable.php' );
        if($sa_apikey_verify){
            $api_key = get_option( 'property_verification_api');
    		$sync_type = get_option( 'sync_type');
    		$posted_properties = 10;
    		global $wpdb;

			$current_lang = apply_filters( 'wpml_default_language', NULL);
			$languages = apply_filters('wpml_active_languages',NULL,'orderby=id&order=desc');
    		$table_name = $wpdb->prefix . 'postmeta';
    		$post_img = $wpdb->get_results("SELECT * FROM $table_name WHERE meta_key = 'is_from_crm' and meta_value = 1");
    		$posted_properties =  count($post_img);
    		$url = RHIMO_FEED_URL;
    
    		$data = [
			    'theme_type'         =>RHIMO_THEME_TYPE,
			    'get_other_language' =>	1,
		    ];
    
    		$propData = wp_remote_post($url, [
    			'method'      => 'GET',
    			'timeout'     => 60,
    			'redirection' => 5,
    			'httpversion' => '1.0',
    			'blocking'    => true,
    			'body'    => $data,
    			'headers'     => [
				    'authorization' => 'Basic YWRtaW46MTIzNA==',
				    'x-api-key'     =>$api_key,
				    'Content-Type' => 'application/x-www-form-urlencoded'
			    ],
    			'cookies' => []
		    ] );

			if(empty($propData)) return;

	        $propDataJsonDcod = json_decode($propData['body'],TRUE);

			if(empty($propDataJsonDcod)) return;



			if( !empty($propDataJsonDcod['data']['inactive_properties']) ){
				self::inactive_property_update($propDataJsonDcod['data']['inactive_properties'],$wpdb);
			}

            if( !empty($propDataJsonDcod['data']['properties']) ){
                $propDataJsonDcod = $propDataJsonDcod['data']['properties'];
                $charset_collate = $wpdb->get_charset_collate();

                //CREATE TABLE FOR PROPERTY IMAGES AND DOCUMENTS
                self::create_table_property_img_doc($wpdb);

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

                foreach($propDataJsonDcod as $pData){
                    // Description
                    $propTitle = isset( $pData['post_title'] ) ? $pData['post_title'] : '';
                    $propContent = isset( $pData['post_content'] ) ? $pData['post_content'] : '';

                    //Details >> Rooms & Sizez
                    $propBedrooms = isset( $pData['fave_property_bedrooms'] ) ? $pData['fave_property_bedrooms'] : '';
                    $propBathrooms = isset( $pData['fave_property_bathrooms'] ) ? $pData['fave_property_bathrooms'] : '';
                    $propGarage = isset( $pData['fave_property_garage'] ) ? $pData['fave_property_garage'] : '';
                    $propGarageSize = isset( $pData['fave_property_garage_size'] ) ? $pData['fave_property_garage_size'] : '';
                    $propYear = isset( $pData['fave_property_year'] ) ? $pData['fave_property_year'] : '';
                    $propFavId = isset( $pData['fave_property_id'] ) ? $pData['fave_property_id'] : '';
                    $propDefPrice = isset( $pData['fave_property_price'] ) ? $pData['fave_property_price'] : '';
                    $propSize = isset( $pData['fave_property_size'] ) ? $pData['fave_property_size'] : '';
                    $propLand = isset( $pData['fave_property_land'] ) ? $pData['fave_property_land'] : '';
                    $fave_property_size_prefix = isset( $pData['fave_property_size_prefix'] ) ? $pData['fave_property_size_prefix'] : '';

                    //Address
                    $propAdd = isset( $pData['fave_property_address'] ) ? $pData['fave_property_address'] : '';
                    $prop_street_address = isset( $pData['fave_property_street_address'] ) ? $pData['fave_property_street_address'] : '';
                    $propCountry = isset( $pData['fave_property_country'] ) ? $pData['fave_property_country'] : '';
                    $propLat = isset( $pData['houzez_geolocation_lat'] ) ? $pData['houzez_geolocation_lat'] : '';
                    $propLong = isset( $pData['houzez_geolocation_long'] ) ? $pData['houzez_geolocation_long'] : '';
                    $propZip = isset( $pData['fave_property_zip'] ) ? $pData['fave_property_zip'] : '';

					$propLocation = $propLat.','.$propLong.',14';

                    //Details
                    $propAgent = isset( $pData['fave_agents'] ) ? $pData['fave_agents'] : '';

                    // Misc >> Customfields
                    $propIsAtt = isset( $pData['fave_additional_features_enable'] ) ? $pData['fave_additional_features_enable'] : '';
                    $propIsFloorPlan = isset( $pData['fave_floor_plans_enable'] ) ? $pData['fave_floor_plans_enable'] : '';
                    $propIsFeatured = isset( $pData['fave_featured'] ) ? $pData['fave_featured'] : '';
	                /** @noinspection PhpIssetCanBeReplacedWithCoalesceInspection */
	                /** @noinspection PhpIssetCanBeReplacedWithCoalesceInspection */
	                $propTerSize              = isset( $pData['fave_single_top_area'] ) ? $pData['fave_single_top_area'] : '';
                    $fave_single_content_area = isset( $pData['fave_single_content_area'] ) ? $pData['fave_single_content_area'] : '';
                    $propExcerpt              = isset($pData['post_excerpt']) ? trim($pData['post_excerpt']) : '';
                    $propMapSView             = isset( $pData['fave_property_map_street_view'] ) ? $pData['fave_property_map_street_view'] : '';
                    $propMUnitPlan            = isset( $pData['fave_multiunit_plans_enable'] ) ? $pData['fave_multiunit_plans_enable'] : '';
                    $fave_property_sec_price  = isset( $pData['fave_property_sec_price'] ) ? $pData['fave_property_sec_price'] : '';
                    $fave_energy_global_index = isset( $pData['fave_energy_global_index'] ) ? $pData['fave_energy_global_index'] : '';
                    $_houzez_expiration_date  = $houzez_manual_expire = '';
                    if( !empty($pData['_houzez_expiration_date']) ){
                        $_houzez_expiration_date = strtotime($pData['_houzez_expiration_date']) ;
                        $houzez_manual_expire = 'checked' ;
                    }
                    $fave_prop_homeslider = trim(strtolower($pData['fave_prop_homeslider']));
                    $fave_property_price_prefix = isset( $pData['fave_property_price_prefix'] ) ? $pData['fave_property_price_prefix'] : '';
                    $fave_property_price_postfix = isset( $pData['fave_property_price_postfix'] ) ? $pData['fave_property_price_postfix'] : '';

                    // Attachments
                    $propImages = isset( $pData['images'] ) ? $pData['images'] : '';
                    $propAttachment = isset( $pData['fave_attachments'] ) ? $pData['fave_attachments'] : '';
                    $propVTour = isset( $pData['fave_virtual_tour'] ) ? $pData['fave_virtual_tour'] : '';

                    // Details >> Notes
                    $propPNote = isset( $pData['fave_private_note'] ) ? $pData['fave_private_note'] : '';

                    // Misc >> Energy Performance
                    $fave_energy_class = isset($pData['fave_energy_class']) ? trim(strtoupper($pData['fave_energy_class'])) : '';
                    $fave_renewable_energy_global_index = isset( $pData['fave_renewable_energy_global_index'] ) ? $pData['fave_renewable_energy_global_index'] : '';
                    $fave_energy_performance = isset( $pData['fave_energy_performance'] ) ? $pData['fave_energy_performance'] : '';

                    $post_status = isset( $pData['post_status'] ) ? $pData['post_status'] : '';
                    $post_private = isset( $pData['is_private'] ) ? $pData['is_private'] : '';
                    if($post_status == 'Active' && $post_private == 1){
                        $post_status = 'private';
                    }
					else if($post_status == 'Active'){
                        $post_status = 'publish';
                    }
					else {
                        $post_status = 'draft' ;
                    }
                    $fave_property_land_postfix = 	$fave_property_size_prefix ;

                    // INSERT FLOOR PLAN START
                    // Attachments
                    $floorPlanData = [];
                    $propFloorPlan = [];
                    if(!empty($pData['floorplans'])){
                        $floor_plan = explode( ',',$pData['floorplans']);
						if(!empty($floor_plan)){
							foreach($floor_plan as $fplan){
								$propFloorPlan = ($fplan);
								$floorPlanData[] = [
									'fave_plan_title'=>'',
									'fave_plan_rooms'=>'',
									'fave_plan_bathrooms'=>'',
									'fave_plan_price'=>'',
									'fave_plan_size' =>'',
									'fave_plan_image'=>$propFloorPlan,
									'fave_plan_description'=>''
								];
							}
							$propFloorPlan = serialize($floorPlanData);
						}
                    }
                    // INSERT FLOOR PLAN END
                    // INSERT AGENT DATA IN WP POSTS START
                    // Details
                    if( !empty($propAgent) ){

                        $propAgent = explode('|',$propAgent);
                        $agentData = [];
                        $agentData = [
                            'post_author'=>1,
                            'post_date' => date('Y-m-d h:i:s'),
                            'post_date_gmt' => date('Y-m-d h:i:s'),
                            'post_title' => $propAgent[0],
                            'post_type'=>'houzez_agent'
					    ];
	                    /** @noinspection PhpRedundantOptionalArgumentInspection */
	                    $agentId = post_exists($propAgent,'','','') ;

                        if($agentId > 0){
                            $agentData['ID'] = $agentId ;
                        }
                        $agent_id = wp_insert_post($agentData);

                        post::respacio_update_property_postmeta($agent_id,'fave_agent_email',$propAgent[1]);
                        post::respacio_update_property_postmeta($agent_id,'fave_agent_mobile',$propAgent[2]);

                    }
                    // INSERT AGENT DATA IN WP POSTS END

                    $postData = [];
					$table1= $wpdb->prefix . 'posts as p';
					$table2= $wpdb->prefix . 'postmeta as pm';

					$sql = "SELECT pm.post_id FROM $table1 left join $table2 on p.ID = pm.post_id WHERE p.post_type = 'property' and meta_key = 'fave_property_id' and pm.meta_value = '".$propFavId."' and p.ID is not null";
					$post_post = $wpdb->get_results($sql);

					$all_post_id = array_column($post_post, 'post_id' );

					$table_name = $wpdb->prefix . 'icl_translations';
					$sql = "SELECT element_id FROM $table_name WHERE element_type = 'post_property' and element_id in (".implode(',',$all_post_id).") and language_code = '".$current_lang."'";
					$post_post = $wpdb->get_results($sql);

					// The Loop
					if(!empty($post_post)){

						$postData = [
							'ID' => $post_post[0]->element_id,
							'post_content' => $propContent,
							'post_title' => $propTitle,
							'post_excerpt' => $propExcerpt,
							'post_status'=>$post_status,
							'post_type'=>'property',
							'post_modified_gmt'=>date( 'Y-m-d H:i:s' ),
							'post_name'	=>	sanitize_title_with_dashes(remove_accents(wp_strip_all_tags($propTitle)),'','save'),
						];
						$postId = wp_update_post( $postData );
						update_post_meta($postId,'fave_property_id',$propFavId);
					}
					else{

						$postData = [
							'post_author'   => 1,
							'post_date' => date('Y-m-d h:i:s'),
							'post_date_gmt' => date('Y-m-d h:i:s'),
							'post_content' => !empty(trim($propContent)) ? trim($propContent) : '',
							'post_title' => wp_strip_all_tags($propTitle),
							'post_excerpt' => $propExcerpt,
							'post_status'=>$post_status,
							'post_name'	=>	sanitize_title_with_dashes(remove_accents(wp_strip_all_tags($propTitle)),'','save'),
							'post_modified_gmt'=>date( 'Y-m-d H:i:s' ),
							'post_type'=>'property'
						];
						$postId = wp_insert_post($postData);
					}

                    // PROPERTY VIDEO URL & IMAGE START

					if(!empty($postId)){
						$propVideoImage = '';
						if( !empty($pData['fave_video_url']) ){
							$propVideoLink = ($pData['fave_video_url']);
							$propVideoUrl = $propVideoLink[0]['inspiry_video_group_url'] ;

							$propVideoImage = $propVideoLink[0]['inspiry_video_group_image'] ;

							$table_name1 = $wpdb->prefix . 'property_images';
							$post_attch = $wpdb->get_results("SELECT id,image_url,image_id FROM $table_name1 WHERE type = 3 AND post_id = ".$postId);
							$vImg_array = [];

							if(!empty($post_attch)) {
								$post_attch = json_decode(json_encode($post_attch), true);
								$vImg_array = array_column($post_attch, 'image_url' );
								$vImg_ids = array_column($post_attch, 'image_id' );
							}

							$propVideoImage = explode('?image_id=',$propVideoImage);
							if( !empty($propVideoImage[1]) ){
								$inspiry_video_group_image_id = $propVideoImage[1] ;
								post::respacio_update_property_postmeta($postId,'fave_video_image',$inspiry_video_group_image_id);
							} else {

								$inspiry_video_group_image_id = $propVideoImage[0];

								//INSERT IMAGE URL IN PROPERTY IMAGES TABLE TO DOWNLOAD START
								if(!in_array($inspiry_video_group_image_id,$vImg_array)){
									$videoImage_array = [
										'post_id'     =>	$postId,
										'image_url'   =>  $inspiry_video_group_image_id,
										'type'        =>	3, //FOR VIDEO IMAGE
										'is_download' =>	0,
									];
									$wpdb->insert($table_name1,$videoImage_array);
								} else if (($key = array_search($inspiry_video_group_image_id,$vImg_array)) !== false) {

									$inspiry_video_group_image_id = $vImg_ids[$key];
									post::respacio_update_property_postmeta($postId,'fave_video_image',$inspiry_video_group_image_id);
								}
								//INSERT IMAGE URL IN PROPERTY IMAGES TABLE TO DOWNLOAD END

							}
						}
						// PROPERTY VIDEO URL & IMAGE END

						//PROPERTY METAS UPDATE START
						post::respacio_update_property_postmeta($postId,'_edit_last','2');
						post::respacio_update_property_postmeta($postId,'_edit_lock', strtotime(date( 'Y-m-d H:i:s' )) . ':' . '2');
						post::respacio_update_property_postmeta($postId,'_houzez_expiration_date_status','saved');
						post::respacio_update_property_postmeta($postId,'fave_currency_info', '&nbsp;' );
						post::respacio_update_property_postmeta($postId,'slide_template','default');
						post::respacio_update_property_postmeta($postId,'_vc_post_settings','');
						post::respacio_update_property_postmeta($postId,'fave_property_size_prefix',$fave_property_size_prefix);
						post::respacio_update_property_postmeta($postId,'fave_property_map',1);
						post::respacio_update_property_postmeta($postId,'is_from_crm',1);
						post::respacio_update_property_postmeta($postId,'fave_property_size',$propSize);
						post::respacio_update_property_postmeta($postId,'_houzez_expiration_date',$_houzez_expiration_date);
						post::respacio_update_property_postmeta($postId,'houzez_manual_expire',$houzez_manual_expire);
						post::respacio_update_property_postmeta($postId,'fave_property_bedrooms',$propBedrooms);
						post::respacio_update_property_postmeta($postId,'fave_property_bathrooms',$propBathrooms);
						post::respacio_update_property_postmeta($postId,'fave_property_garage',$propGarage);
						post::respacio_update_property_postmeta($postId,'fave_property_garage_size',$propGarageSize);
						post::respacio_update_property_postmeta($postId,'fave_property_year',$propYear);
						post::respacio_update_property_postmeta($postId,'fave_property_id',$propFavId);
						post::respacio_update_property_postmeta($postId,'fave_property_price',$propDefPrice);
						post::respacio_update_property_postmeta($postId,'fave_property_location',$propLocation);
						post::respacio_update_property_postmeta($postId,'fave_agents',$agent_id);
						post::respacio_update_property_postmeta($postId,'fave_floor_plans_enable',$propIsFloorPlan);
						post::respacio_update_property_postmeta($postId,'floor_plans',$propFloorPlan);//serialize data
						post::respacio_update_property_postmeta($postId,'fave_featured',$propIsFeatured);
						post::respacio_update_property_postmeta($postId,'fave_property_map_address',$propAdd);
						post::respacio_update_property_postmeta($postId,'fave_property_address',$prop_street_address);
						//post::respacio_update_property_postmeta($postId,'fave_property_address',$propAdd);
						post::respacio_update_property_postmeta($postId,'fave_video_url',$propVideoUrl);
						post::respacio_update_property_postmeta($postId,'_dp_original','');
						post::respacio_update_property_postmeta($postId,'houzez_geolocation_lat',$propLat);
						post::respacio_update_property_postmeta($postId,'houzez_geolocation_long',$propLong);
						post::respacio_update_property_postmeta($postId,'fave_single_top_area',$propTerSize);
						post::respacio_update_property_postmeta($postId,'fave_property_zip',$propZip);
						post::respacio_update_property_postmeta($postId,'fave_property_land',$propLand);
						post::respacio_update_property_postmeta($postId,'fave_virtual_tour',$propVTour);
						post::respacio_update_property_postmeta($postId,'fave_private_note',$propPNote);
						post::respacio_update_property_postmeta($postId,'fave_property_map_street_view',$propMapSView);
						post::respacio_update_property_postmeta($postId,'fave_multiunit_plans_enable',$propMUnitPlan);
						post::respacio_update_property_postmeta($postId,'fave_property_sec_price',$fave_property_sec_price);
						post::respacio_update_property_postmeta($postId,'fave_energy_global_index',$fave_energy_global_index);
						post::respacio_update_property_postmeta($postId,'fave_energy_class',$fave_energy_class);
						post::respacio_update_property_postmeta($postId,'fave_prop_homeslider',$fave_prop_homeslider);
						post::respacio_update_property_postmeta($postId,'fave_property_price_postfix',$fave_property_price_postfix);
						post::respacio_update_property_postmeta($postId,'fave_renewable_energy_global_index',$fave_renewable_energy_global_index);
						post::respacio_update_property_postmeta($postId,'fave_energy_performance',$fave_energy_performance);
						post::respacio_update_property_postmeta($postId,'fave_property_land_postfix',$fave_property_land_postfix);
						post::respacio_update_property_postmeta($postId,'fave_single_content_area',$fave_single_content_area);
						//PROPERTY METAS UPDATE END

						//PROPERTY IMAGE START //
						$table_name = $wpdb->prefix . 'property_images';
						$post_img = $wpdb->get_results("SELECT id,image_url,image_id FROM $table_name WHERE type = 1 AND image_url != '' AND post_id = ".$postId);
						$img_array = [];
						if(!empty($post_img)){
							$post_img = json_decode(json_encode($post_img), true);
							$img_array = array_column($post_img, 'image_url' );
							$img_ids = array_column($post_img, 'image_id' );
							$img_ids = array_combine($img_array,$img_ids);
						}

						$new_properties = [];
						if(!empty($propImages)){
							$propImages = explode( ',',$propImages);
							foreach($propImages as $mainkey => $img){

								$img = explode( '?image_id=',$img);
								$new_properties[] =  $img[0];
								if(!in_array($img[0],$img_array)){

									$is_download = (isset($img[1]) &&  $img[1] > 0) ? 1 : 0 ;
									$image_id = (isset($img[1]) &&  $img[1] > 0) ? $img[1] : '' ;
										$images_array = [
											'post_id'     =>	$postId,
											'image_url'   =>  $img[0],
											'sequence'    => $mainkey + 1,
											'type'        =>	1,
											'image_id'    =>	$image_id,
											'is_download' =>	$is_download
										];

									$table_name = $wpdb->prefix . 'property_images';
									$wpdb->insert($table_name,$images_array);
								}
								else if ((array_search($img[0], $img_array)) !== false) {

									$image_id = $img_ids[$img[0]];

									if(!empty($image_id)){

										$prop_images_tbl = $wpdb->prefix . 'property_images';
										$wpdb->update($prop_images_tbl, [ 'sequence' => $mainkey + 1 ], [ 'post_id' =>$postId, 'image_id' =>$image_id ] );

										$table_name = $wpdb->prefix . 'postmeta';
										$sql = 'delete FROM ' . $table_name . ' WHERE post_id = ' . $postId . ' and meta_value = ' . $image_id;
										$wpdb->get_results($sql);

										if($mainkey == 0){

										    $table_name = $wpdb->prefix . 'postmeta';
                                            $sql = 'delete FROM ' . $table_name . ' WHERE post_id = ' . $postId . " and meta_key = '_thumbnail_id'";
                                            $wpdb->get_results($sql);

											$add_same_image = [
												'post_id'    =>	$postId,
												'meta_key'   => '_thumbnail_id',
												'meta_value' =>	$image_id
											];
											$table_name = $wpdb->prefix . 'postmeta';
											$wpdb->insert($table_name,$add_same_image);
										}


										$table_name = $wpdb->prefix . 'postmeta';
										$sql = 'delete FROM ' . $table_name . ' WHERE post_id = ' . $postId . ' and meta_value = ' . $image_id . " and meta_key = 'fave_property_images'";
										$wpdb->get_results($sql);

										$add_same_image = [
											'post_id'    =>	$postId,
											'meta_key'   => 'fave_property_images',
											'meta_value' =>	$image_id
										];

										$table_name = $wpdb->prefix . 'postmeta';
										$wpdb->insert($table_name,$add_same_image);

									}
								}
							}
						}

						$img_array = array_diff($img_array,$new_properties);
						$post_id = [];
						if(!empty($img_array)){
							if(!empty($post_img)){
								foreach($post_img as $img_str){

									$img_val = $img_str['image_url'];
									if(in_array($img_val,$img_array)){

										$i_id = $img_str['id'];
										$img_id = $img_str['image_id'];

										if(!empty($img_id)){
											image::respacio_houzez_delete_image($postId,$img_id);
										}
										else{

											$tbl = $wpdb->prefix . 'property_images';
											$wpdb->delete($tbl, [ 'id' =>$i_id ] );
										}
									}
								}
							}
						}

						//PROPERTY IMAGE END //
						//PROPERTY ATTACHMENT DOCUMENT START //
						$table_name = $wpdb->prefix . 'property_images';
						$sql = "SELECT id,image_url,image_id FROM $table_name WHERE type = 2 and post_id = ".$postId;
						$post_attch = $wpdb->get_results($sql);
						$img_array = [];
						if(!empty($post_attch)){
							$post_attch = json_decode(json_encode($post_attch), true);
							$img_array = array_column($post_attch, 'image_url' );
						}

						if(!empty($propAttachment)){
							$propAttachment = explode( ',',$propAttachment);
							foreach($propAttachment as $img){
								$img = explode( '?image_id=',$img);
								if(!in_array($img[0],$img_array)){

									$is_download = (isset($img[1]) &&  $img[1] > 0) ? 1 : 0 ;
									$image_id = (isset($img[1]) &&  $img[1] > 0) ? $img[1] : '' ;
									$images_array = [
										'post_id'     =>	$postId,
										'image_url'   =>  $img[0],
										'type'        =>	2,
										'image_id'    =>	$image_id,
										'is_download' =>	$is_download,
									];
									$table_name = $wpdb->prefix . 'property_images';
									$wpdb->insert($table_name,$images_array);

								}
								else if (($key = array_search($img[0], $img_array)) !== false) {
									unset($img_array[$key]);
								}
							}
						}

						if(!empty($img_array)){
							$post_id = [];
							if(!empty($post_attch)){
								foreach($post_attch as $img_str){
									$img_val = $img_str['image_url'];
									if(in_array($img_val,$img_array)){
										$post_id[] = $img_str['image_id'];
									}
								}
							}
						}

						if(!empty($post_id)){
							foreach($post_id as $ids){

								$table = $wpdb->prefix . 'posts';
								$wpdb->delete($table, [ 'ID' =>$ids ] );

								$table = $wpdb->prefix . 'postmeta';
								$wpdb->delete($table, [ 'post_id' =>$postId, 'meta_value' =>$ids ] );
							}
						}
						//PROPERTY ATTACHMENT DOCUMENT END //

						self::respacio_update_features($postId,$pData['property_feature'],'property_feature');
						self::respacio_update_features($postId,$pData['property_type'],'property_type');
						self::respacio_update_features($postId,$pData['property_status'],'property_status');
						self::respacio_update_features($postId,$pData['property_city'],'property_city');
						self::respacio_update_features($postId,$pData['property_label'],'property_label');
						self::respacio_update_features($postId,$pData['fave_property_country'],'property_country');
						self::respacio_update_features($postId,$pData['property_state'],'property_state');
						self::respacio_update_features($postId,$pData['property_area'],'property_area');
					}

					image::respacio_houzez_update_image_order($postId);
	                if(!empty($pData['property_trans_data'])){
		                //respacio_houzez_add_other_language_post($postId,$pData,$current_lang,$activ_language);

		                $t = $wpdb->prefix . 'property_log';
		                $check = $wpdb->get_results("select id from $t where post_id = ".$postId . ' and is_sync = 0' );

		                if(empty($check)){
			                $add_data = [
				                'post_id'       =>	$postId,
				                'property_data' =>	json_encode($pData),
				                'is_sync'       =>	0,
				                'added_date'    =>	date( 'Y-m-d H:i:s' ),
			                ];
			                $tbl = $wpdb->prefix . 'property_log';
			                $wpdb->insert($tbl,$add_data);
		                }
		                else{

			                $update_data = [ 'property_data' =>json_encode($pData) ];
			                $wpdb->update($t,$update_data, [ 'id' =>$check[0]->id ] );
		                }
	                }
                }

            }

        }
	}



	/**
	 * creating table for property images and documents
	 * @return void
	 */
	public static function create_table_property_img_doc($wpdb){
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();


		$table_property_images = $wpdb->prefix . 'property_images';
		$sql_property_images = "CREATE TABLE  $table_property_images (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    post_id int(11),
                    image_url varchar(255),
                    image_id int(11),
                    type tinyint(1),
					sequence int(11) default '0',
                    is_download tinyint(1) default '0',
					title text,
					alt_text text,
					description text,
                    PRIMARY KEY  (id),
					KEY `post_id` (`post_id`),
					KEY `image_id` (`image_id`),
					KEY `type` (`type`),
					KEY `sequence` (`sequence`),
                ) $charset_collate;";
		dbDelta($sql_property_images );

		$table_property_metadata = $wpdb->prefix . 'property_metadata';
		$sql_property_metadata = "CREATE TABLE $table_property_metadata (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  post_id int(11),
				  language_code varchar(5),
				  image_name varchar(255),
				  title text,
				  alt_text text,
				  description text,
				  is_sync tinyint(1) default '0',
				  PRIMARY KEY  (id),
				  KEY `post_id` (`post_id`)
				) $charset_collate;";
		dbDelta( $sql_property_metadata );

		$table_property_log = $wpdb->prefix . 'property_log';
		$sql_property_log = "CREATE TABLE $table_property_log (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  post_id int(11),
				  property_data text,
				  is_sync tinyint(1) default '0',
				  added_date datetime,
				  PRIMARY KEY  (id),
				  KEY `post_id` (`post_id`)
				) $charset_collate;";
		dbDelta( $sql_property_log );
	}
	public static function inactive_property_update($inactive_properties,$wpdb){
		$languages = apply_filters('wpml_active_languages',NULL,'orderby=id&order=desc');
		$activ_language = array_keys($languages);


		foreach($inactive_properties as $pdata){

			$propFavId = isset( $pdata['reference_no'] ) ? $pdata['reference_no'] : '';

			$prop_status = $pdata['status'];

			$is_exclude = $pdata['is_exclude_from_website'];

			$propTitle = $pdata['title'];

			$propContent = $pdata['decriptions'];

			$web_ststus = 'trash';

			if($prop_status == 1 && $is_exclude == 1){
				$web_ststus = 'draft';
			}

			$args = [
				'post_type'              => [ 'property' ],
				'post_status'			=>	[ '*' ],
				'meta_query'             => [
					[
						'key'       => 'fave_property_id',
						'value'     => $propFavId,
					],
				],
			];
			// The Query
			$query = new WP_Query($args);
			// The Loop

			if ( $query->have_posts() ) {

				$query_data = json_decode(json_encode($query),true);

				$postData = [
					'ID' => $query_data['posts'][0]['ID'],
					'post_date' => date('Y-m-d h:i:s'),
					'post_date_gmt' => date('Y-m-d h:i:s'),
					'post_content' => $propContent,
					'post_title' => $propTitle,
					'post_status'=> $web_ststus,
					'post_type'=>'property',
				];
				$postId = wp_update_post( $postData );
				update_post_meta($postId,'fave_property_id',$propFavId);

				$ps_id = $query_data['posts'][0]['ID'];
				if(!empty($activ_language)){
					foreach($activ_language as $lan){

						$table_name = $wpdb->prefix . 'icl_translations';
						$sql = "SELECT element_id FROM $table_name WHERE element_type = 'post_property' and trid in (".$ps_id.") and language_code = '".$lan."' and element_id is not null and element_id != '' and element_id not in (".$ps_id . ')';
						$post_post = $wpdb->get_results($sql);

						if(!empty($post_post)){
							$postData = [
								'ID' => $post_post[0]->element_id,
								'post_date' => date('Y-m-d h:i:s'),
								'post_date_gmt' => date('Y-m-d h:i:s'),
								'post_status'=> $web_ststus,
								'post_type'=>'property',
							];
							$postId = wp_update_post( $postData );
						}
					}
				}
			}
		}
	}

	public static function respacio_update_features($postId, $customTaxo, $type){

		global $wpdb;
		if ( !empty($customTaxo) ) {
			
			$delete_feature = '';
			$propAtt = ($customTaxo);
			$table_name = $wpdb->prefix . 'term_relationships';

			$term_taxonomy = get_the_terms($postId, $type);

			$exist_array = [];
			if (!empty($term_taxonomy)) {
				$term_taxonomy = json_decode(json_encode($term_taxonomy), true);
				$exist_array = array_column($term_taxonomy, 'term_id' );
			}

			$came_taxonomy = [];
			if ( !empty($propAtt) ) {
				
				$propAtt = array_values(array_filter(explode('|', $propAtt)));
				foreach ($propAtt as $pAtt) {

					$propFeatureTermId = '';
					$propFeatureTermId = term_exists($pAtt, $type);
					if ( empty($propFeatureTermId) ) {
						$propFeatureTermId = wp_insert_term($pAtt, $type);
					}

					if (is_array($propFeatureTermId)) {
						$taxonomy = $propFeatureTermId['term_taxonomy_id'];
					} else {
						$taxonomy = $propFeatureTermId;
					}

					$came_taxonomy[] = 	$taxonomy;
					try {
						$table_name = $wpdb->prefix . 'term_relationships';

						$sql = 'SELECT object_id,term_taxonomy_id FROM ' . $table_name . ' WHERE object_id = ' . $postId . ' and term_taxonomy_id = ' . $taxonomy;

						$check = $wpdb->get_results($sql);

						if (empty($check)) {

							$term_relationship = [
								'object_id'        =>	$postId,
								'term_taxonomy_id' =>	$taxonomy
							];

							$wpdb->insert($table_name, $term_relationship);
						}
					} catch ( Exception $e) {
					}
				}
			}

			$delete_feature = array_diff($exist_array, $came_taxonomy);

			if (!empty($delete_feature)) {
				$table_name = $wpdb->prefix . 'term_relationships';
				$sql = 'delete FROM ' . $table_name . ' WHERE object_id = ' . $postId . ' and term_taxonomy_id in (' . implode(',', $delete_feature) . ')';
				$wpdb->get_results($sql);
			}
		}
	}

	public static function respacio_add_property_image_hourly(){
		// do something every hour
		$sa_apikey_verify = get_option('verify_api');
		if ($sa_apikey_verify) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'property_images';
			$post_img = $wpdb->get_results("SELECT * FROM $table_name WHERE image_url != '' AND is_download = 0 AND type = 1 order by id asc limit 10");
			$image_sizes = image::respacio_get_image_sizes();

			if (!empty($post_img)) {
				foreach ($post_img as $key => $val) {
					$url = $val->image_url;
					$postId = $val->post_id;
					$id = $val->id;
					post::respacio_add_postmetadata($postId, $url, $image_sizes, $id);
				}
			}
		}
	}

	/** @noinspection PhpUnused */
	public static function respacio_cron_log($log_type = ''){
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		if (!empty($log_type)) {
			$table_name = $wpdb->prefix . 'cron_log';
			$sql = "CREATE TABLE $table_name (
    		   id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    		   log_type varchar(255),
    		   logtime DATETIME,
    		   PRIMARY KEY (id)
    		) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);

			$table_name = $wpdb->prefix . 'cron_log';
			$insert_thumb = [
				'log_type' =>	$log_type,
				'logtime' =>	date( 'Y-m-d H:i:s' )
			];

			$wpdb->insert($table_name, $insert_thumb);
		}
	}
}
