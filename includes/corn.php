<?php

namespace RespacioHouzezImport;

class corn {
	public static function activate(){
		add_filter( 'cron_schedules', array('RespacioHouzezImport\corn','addCronEvents'));
		if (! wp_next_scheduled ('add_daily_properties')) {
			wp_schedule_event(time(),'every_eleven_minutes','add_daily_properties');
		}
		add_action('add_daily_properties', array('RespacioHouzezImport\corn','respacio_sync_properties'));
	}

	public static function addCronEvents($schedules ){

		$schedules['every_three_minutes'] = array(
			'interval'  => 180,
			'display'   => __( 'Every 3 Minutes', 'respacio-admin' )
		);
		$schedules['every_five_minutes'] = array(
			'interval'  => 300,
			'display'   => __( 'Every 5 Minutes', 'respacio-admin' )
		);
		$schedules['every_seven_minutes'] = array(
			'interval'  => 420,
			'display'   => __( 'Every 7 Minutes', 'respacio-admin' )
		);
		$schedules['every_eleven_minutes'] = array(
			'interval'  => 660,
			'display'   => __( 'Every 11 Minutes', 'respacio-admin' )
		);
		$schedules['every_thirteen_minutes'] = array(
			'interval'  => 780,
			'display'   => __( 'Every 13 Minutes', 'respacio-admin' )
		);
		$schedules['every_fifteen_minutes'] = array(
			'interval'  => 900,
			'display'   => __( 'Every 15 Minutes', 'respacio-admin' )
		);
		$schedules['every_seventeen_minutes'] = array(
			'interval'  => 1020,
			'display'   => __( 'Every 17 Minutes', 'respacio-admin' )
		);
		$schedules['every_ninteen_minutes'] = array(
			'interval'  => 1140,
			'display'   => __( 'Every 19 Minutes', 'respacio-admin' )
		);
		$schedules['every_thirty_minutes'] = array(
			'interval'  => 1800,
			'display'   => __( 'Every 30 Minutes', 'respacio-admin' )
		);
		$schedules['every_sixty_minutes'] = array(
			'interval'  => 3600,
			'display'   => __( 'Every 60 Minutes', 'respacio-admin' )
		);
		return $schedules;
	}

	public static function respacio_sync_properties(){

		$sa_apikey_verify = get_option( 'verify_api');
		include(ABSPATH . "wp-includes/pluggable.php");
		if($sa_apikey_verify){
			$api_key = get_option( 'property_verification_api');
			$sync_type = get_option( 'sync_type');
			$posted_properties = 10;
			global $wpdb;

			$table_name = $wpdb->prefix . "postmeta";
			$post_img = $wpdb->get_results("SELECT * FROM $table_name WHERE meta_key = 'is_from_crm' and meta_value = 1");
			$posted_properties =  count($post_img);
			$url = RHIMO_FEED_URL;

			$data = array(
				"theme_type"=>RHIMO_THEME_TYPE
			);

			$propData = wp_remote_post($url, array(
				'method'      => 'GET',
				'timeout'     => 60,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'body'    => $data,
				'headers'     => array(
					"authorization"=> "Basic YWRtaW46MTIzNA==",
					"x-api-key"=>$api_key,
					"Content-Type"=>"application/x-www-form-urlencoded"
				),
				'cookies' => array()
			));

			if(!empty($propData)){

				$propDataJsonDcod = json_decode($propData['body'],TRUE);
				if(isset($propDataJsonDcod) && !empty($propDataJsonDcod)){

					if(!empty($propDataJsonDcod) && !empty($propDataJsonDcod["data"]["inactive_properties"])){
						$inactive_properties = $propDataJsonDcod["data"]["inactive_properties"];

						if(!empty($inactive_properties)){
							foreach($inactive_properties as $pdata){
								$propFavId = isset($pdata['reference_no']) ? $pdata['reference_no'] : '';

								$args = array (
									'post_type'              => array('property'),
									'post_status'			 =>	array( 'publish' ),
									'meta_query'             => array(
										array(
											'key'       => 'fave_property_id',
											'value'     => $propFavId,
										),
									),
								);
								// The Query
								$query = new WP_Query($args);
								// The Loop
								if ( $query->have_posts() ) {

									$query_data = json_decode(json_encode($query),true);
									$postData = array(
										'ID' => $query_data['posts'][0]['ID'],
										'post_date' => date('Y-m-d h:i:s'),
										'post_date_gmt' => date('Y-m-d h:i:s'),
										'post_content' => $propContent,
										'post_title' => $propTitle,
										'post_excerpt' => $propExcerpt,
										'post_status'=>'trash',
										'post_type'=>'property',
									);
									$postId = wp_update_post( $postData );
									update_post_meta($postId,'fave_property_id',$propFavId);
								}
							}
						}
					}

					if(!empty($propDataJsonDcod) && !empty($propDataJsonDcod["data"]["all_properties"])){
						$all_properties = $propDataJsonDcod["data"]["all_properties"];
						$all_properties = array_column($all_properties,"reference_no");

						$table1= $wpdb->prefix . "posts as p";
						$table2= $wpdb->prefix . "postmeta as pm";

						$sql = "SELECT pm.meta_value FROM $table1 left join $table2 on p.ID = pm.post_id WHERE p.post_type = 'property' and meta_key = 'fave_property_id' and p.ID is not null";
						$post_post = $wpdb->get_results($sql);
						$post_post = array_column($post_post,"meta_value");

						$deleted_prop = array_diff($post_post,$all_properties);
						if(!empty($deleted_prop)){
							foreach($deleted_prop as $ref){
								$args = array (
									'post_type'              => array('property'),
									'post_status'			=>	array('*'),
									'meta_query'             => array(
										array(
											'key'       => 'fave_property_id',
											'value'     => $ref,
										),
									),
								);
								// The Query
								$query = new WP_Query($args);

								if ( $query->have_posts() ) {

									$query_data = json_decode(json_encode($query),true);
									wp_delete_post($query_data['posts'][0]['ID']);
								}
							}
						}
					}

					if(!empty($propDataJsonDcod) && !empty($propDataJsonDcod["data"]["properties"])){
						$propDataJsonDcod = $propDataJsonDcod["data"]["properties"];
						$charset_collate = $wpdb->get_charset_collate();

						//CREATE TABLE FOR PROPERTY IMAGES AND DOCUMENTS
						$table_name = $wpdb->prefix . "property_images";
						$sql = "CREATE TABLE $table_name (
    						id mediumint(9) NOT NULL AUTO_INCREMENT,
    						post_id int(11),
    						image_url varchar(255),
    						image_id int(11),
    						type tinyint(1),
    						is_download tinyint(1) default '0',
    						PRIMARY KEY  (id)
    					) $charset_collate;";

						require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
						dbDelta( $sql );

						foreach($propDataJsonDcod as $pData){
							// Description
							$propTitle = isset($pData['post_title'])  ? $pData['post_title'] : '';
							$propContent = isset($pData['post_content'])  ? $pData['post_content'] : '';

							//Details >> Rooms & Sizez
							$propBedrooms = isset($pData['fave_property_bedrooms']) ? $pData['fave_property_bedrooms'] : '';
							$propBathrooms = isset($pData['fave_property_bathrooms']) ? $pData['fave_property_bathrooms'] : '';
							$propGarage = isset($pData['fave_property_garage']) ? $pData['fave_property_garage'] : '';
							$propGarageSize = isset($pData['fave_property_garage_size']) ? $pData['fave_property_garage_size'] : '';
							$propYear = isset($pData['fave_property_year']) ? $pData['fave_property_year'] : '';
							$propFavId = isset($pData['fave_property_id']) ? $pData['fave_property_id'] : '';
							$propDefPrice = isset($pData['fave_property_price']) ? $pData['fave_property_price'] : '';
							$propSize = isset($pData['fave_property_size']) ? $pData['fave_property_size'] : '';
							$propLand = isset($pData['fave_property_land']) ? $pData['fave_property_land'] : '';
							$fave_property_size_prefix = isset($pData['fave_property_size_prefix']) ? $pData['fave_property_size_prefix'] : '';

							//Address
							$propAdd = isset($pData['fave_property_address']) ? $pData['fave_property_address'] : '';
							$prop_street_address = isset($pData['fave_property_street_address']) ? $pData['fave_property_street_address'] : '';
							$propLocation = isset($pData['fave_property_location']) ? $pData['fave_property_location'] : '';
							$propCountry = isset($pData['fave_property_country']) ? $pData['fave_property_country'] : '';
							$propLat = isset($pData['houzez_geolocation_lat']) ? $pData['houzez_geolocation_lat'] : '';
							$propLong = isset($pData['houzez_geolocation_long']) ? $pData['houzez_geolocation_long'] : '';
							$propZip = isset($pData['fave_property_zip']) ? $pData['fave_property_zip'] : '';

							//Details
							$propAgent = isset($pData['fave_agents']) ? $pData['fave_agents'] : '';

							// Misc >> Customfields
							$propIsAtt = isset($pData['fave_additional_features_enable']) ? $pData['fave_additional_features_enable'] : '';
							$propIsFloorPlan = isset($pData['fave_floor_plans_enable']) ? $pData['fave_floor_plans_enable'] : '';
							$propIsFeatured = isset($pData['fave_featured']) ? $pData['fave_featured'] : '';
							$propTerSize = isset($pData['fave_single_top_area']) ? $pData['fave_single_top_area'] : '';
							$fave_single_content_area = isset($pData['fave_single_content_area']) ? $pData['fave_single_content_area'] : '';
							$propExcerpt = isset($pData['post_excerpt']) ? trim($pData['post_excerpt']) : '';
							$propMapSView = isset($pData['fave_property_map_street_view']) ? $pData['fave_property_map_street_view'] : '';
							$propMUnitPlan = isset($pData['fave_multiunit_plans_enable']) ? $pData['fave_multiunit_plans_enable'] : '';
							$fave_property_sec_price = isset($pData['fave_property_sec_price']) ? $pData['fave_property_sec_price'] : '';
							$fave_energy_global_index = isset($pData['fave_energy_global_index']) ? $pData['fave_energy_global_index'] : '';
							$_houzez_expiration_date = $houzez_manual_expire = '';
							if(isset($pData['_houzez_expiration_date']) && !empty($pData['_houzez_expiration_date'])){
								$_houzez_expiration_date = strtotime($pData['_houzez_expiration_date']) ;
								$houzez_manual_expire = 'checked' ;
							}
							$fave_prop_homeslider = trim(strtolower($pData['fave_prop_homeslider']));
							$fave_property_price_prefix = isset($pData['fave_property_price_prefix']) ? $pData['fave_property_price_prefix'] : '';
							$fave_property_price_postfix = isset($pData['fave_property_price_postfix']) ? $pData['fave_property_price_postfix'] : '';

							// Attachments
							$propImages = isset($pData['images']) ? $pData['images'] : '';
							$propAttachment = isset($pData['fave_attachments']) ? $pData['fave_attachments'] : '';
							$propVTour = isset($pData['fave_virtual_tour']) ? $pData['fave_virtual_tour'] : '';

							// Details >> Notes
							$propPNote = isset($pData['fave_private_note']) ? $pData['fave_private_note'] : '';

							// Misc >> Energy Performance
							$fave_energy_class = isset($pData['fave_energy_class']) ? trim(strtoupper($pData['fave_energy_class'])) : '';
							$fave_renewable_energy_global_index = isset($pData['fave_renewable_energy_global_index']) ? $pData['fave_renewable_energy_global_index'] : '';
							$fave_energy_performance = isset($pData['fave_energy_performance']) ? $pData['fave_energy_performance'] : '';

							$post_status = isset($pData['post_status']) ? $pData['post_status'] : '';
							if($post_status == 'Active'){
								$post_status = 'publish' ;
							} else {
								$post_status = 'draft' ;
							}
							$fave_property_land_postfix = 	$fave_property_size_prefix ;

							// INSERT FLOOR PLAN START
							// Attachments
							$floorPlanData = array();
							$propFloorPlan = array();
							if(!empty($pData['floorplans'])){
								$floor_plan = explode(",",$pData['floorplans']);
								if(!empty($floor_plan)){
									foreach($floor_plan as $fplan){
										$propFloorPlan = ($fplan);
										$floorPlanData[] = array(
											'fave_plan_title'=>'',
											'fave_plan_rooms'=>'',
											'fave_plan_bathrooms'=>'',
											'fave_plan_price'=>'',
											'fave_plan_size' =>'',
											'fave_plan_image'=>$propFloorPlan,
											'fave_plan_description'=>''
										);
									}
									$propFloorPlan = serialize($floorPlanData);
								}
							}
							// INSERT FLOOR PLAN END
							// INSERT AGENT DATA IN WP POSTS START
							// Details
							if(isset($propAgent) && !empty($propAgent)){

								$propAgent = explode('|',$propAgent);
								$agentData = array();
								$agentData = array(
									'post_author'=>1,
									'post_date' => date('Y-m-d h:i:s'),
									'post_date_gmt' => date('Y-m-d h:i:s'),
									'post_title' => $propAgent[0],
									'post_type'=>'houzez_agent'
								);
								$agentId = post_exists($propAgent,'','','') ;

								if($agentId > 0){
									$agentData['ID'] = $agentId ;
								}
								$agent_id = wp_insert_post($agentData);

								respacio_update_property_postmeta($agent_id,'fave_agent_email',$propAgent[1]);
								respacio_update_property_postmeta($agent_id,'fave_agent_mobile',$propAgent[2]);

							}
							// INSERT AGENT DATA IN WP POSTS END

							$postData = array() ;
							//$check_title=get_page_by_title($propTitle, 'OBJECT', 'property');
							//$favrt_id_check = get_post_meta( $check_title->ID, 'fave_property_id', true );
							// CHECK POST EXISTS
							//if(empty($favrt_id_check)){
							$args = array (
								'post_type'              => array( 'property' ),
								'post_status'            => array( 'publish' ),
								'meta_query'             => array(
									array(
										'key'       => 'fave_property_id',
										'value'     => $propFavId,
									),
								),
							);
							// The Query
							$query = new WP_Query($args);
							// The Loop
							if ( $query->have_posts() ) {

								$query_data = json_decode(json_encode($query),true);
								$postData = array(
									'ID' => $query_data['posts'][0]['ID'],
									'post_date' => date('Y-m-d h:i:s'),
									'post_date_gmt' => date('Y-m-d h:i:s'),
									'post_content' => $propContent,
									'post_title' => $propTitle,
									'post_excerpt' => $propExcerpt,
									'post_status'=>$post_status,
									'post_type'=>'property',
									'post_name'	=>	sanitize_title_with_dashes(remove_accents(wp_strip_all_tags($propTitle)),'','save'),
								);
								$postId = wp_update_post( $postData );
								update_post_meta($postId,'fave_property_id',$propFavId);
							}
							else{

								$postData = array(
									'post_author'   => 1,
									'post_date' => date('Y-m-d h:i:s'),
									'post_date_gmt' => date('Y-m-d h:i:s'),
									'post_content' => !empty(trim($propContent)) ? trim($propContent) : '',
									'post_title' => wp_strip_all_tags($propTitle),
									'post_excerpt' => $propExcerpt,
									'post_status'=>$post_status,
									'post_name'	=>	sanitize_title_with_dashes(remove_accents(wp_strip_all_tags($propTitle)),'','save'),
									'post_modified_gmt'=>date("Y-m-d H:i:s"),
									'post_type'=>'property'
								);
								$postId = wp_insert_post($postData);
							}
							// CHECK POST EXISTS
							// $table_name = $wpdb->prefix . "postmeta";
							// $post_img = $wpdb->get_results("SELECT post_id FROM $table_name WHERE meta_key = 'fave_property_id' and meta_value = '".$propFavId."'");

							// if(!empty($post_img) && !empty($post_img[0]->post_id)){
							// 	$postData['ID'] = $post_img[0]->post_id;
							// }
							// $postId = wp_insert_post($postData);

							// PROPERTY VIDEO URL & IMAGE START
							$propVideoImage = '';
							if(isset($pData['fave_video_url']) && !empty($pData['fave_video_url'])){
								$propVideoLink = ($pData['fave_video_url']);
								$propVideoUrl = $propVideoLink[0]['inspiry_video_group_url'] ;

								$propVideoImage = $propVideoLink[0]['inspiry_video_group_image'] ;

								$table_name1 = $wpdb->prefix . "property_images";
								$post_attch = $wpdb->get_results("SELECT id,image_url,image_id FROM $table_name1 WHERE type = 3 AND post_id = ".$postId);
								$vImg_array = array();

								if(!empty($post_attch)) {
									$post_attch = json_decode(json_encode($post_attch), true);
									$vImg_array = array_column($post_attch,"image_url");
									$vImg_ids = array_column($post_attch,"image_id");
								}

								$propVideoImage = explode('?image_id=',$propVideoImage);
								if(isset($propVideoImage[1]) && !empty($propVideoImage[1])){
									$inspiry_video_group_image_id = $propVideoImage[1] ;
									respacio_update_property_postmeta($postId,'fave_video_image',$inspiry_video_group_image_id);
								} else {

									$inspiry_video_group_image_id = $propVideoImage[0];

									//INSERT IMAGE URL IN PROPERTY IMAGES TABLE TO DOWNLOAD START
									if(!in_array($inspiry_video_group_image_id,$vImg_array)){
										$videoImage_array = array(
											"post_id"	    =>	$postId,
											"image_url"	    =>  $inspiry_video_group_image_id,
											"type"		    =>	3, //FOR VIDEO IMAGE
											"is_download"   =>	0,
										);
										$wpdb->insert($table_name1,$videoImage_array);
									} else if (($key = array_search($inspiry_video_group_image_id,$vImg_array)) !== false) {

										$inspiry_video_group_image_id = $vImg_ids[$key];
										respacio_update_property_postmeta($postId,'fave_video_image',$inspiry_video_group_image_id);
									}
									//INSERT IMAGE URL IN PROPERTY IMAGES TABLE TO DOWNLOAD END

								}
							}
							// PROPERTY VIDEO URL & IMAGE END

							//PROPERTY METAS UPDATE START
							self::respacio_update_property_postmeta($postId,'_edit_last','2');
							self::respacio_update_property_postmeta($postId,'_edit_lock',strtotime(date("Y-m-d H:i:s")).":".'2');
							self::respacio_update_property_postmeta($postId,'_houzez_expiration_date_status','saved');
							self::respacio_update_property_postmeta($postId,'fave_currency_info',"&nbsp;");
							self::respacio_update_property_postmeta($postId,'slide_template','default');
							self::respacio_update_property_postmeta($postId,'_vc_post_settings','');
							self::respacio_update_property_postmeta($postId,'fave_property_size_prefix',$fave_property_size_prefix);
							self::respacio_update_property_postmeta($postId,'fave_property_map',1);
							self::respacio_update_property_postmeta($postId,'is_from_crm',1);
							self::respacio_update_property_postmeta($postId,'fave_property_size',$propSize);
							self::respacio_update_property_postmeta($postId,'_houzez_expiration_date',$_houzez_expiration_date);
							self::respacio_update_property_postmeta($postId,'houzez_manual_expire',$houzez_manual_expire);
							self::respacio_update_property_postmeta($postId,'fave_property_bedrooms',$propBedrooms);
							self::respacio_update_property_postmeta($postId,'fave_property_bathrooms',$propBathrooms);
							self::respacio_update_property_postmeta($postId,'fave_property_garage',$propGarage);
							self::respacio_update_property_postmeta($postId,'fave_property_garage_size',$propGarageSize);
							self::respacio_update_property_postmeta($postId,'fave_property_year',$propYear);
							self::respacio_update_property_postmeta($postId,'fave_property_id',$propFavId);
							self::respacio_update_property_postmeta($postId,'fave_property_price',$propDefPrice);
							self::respacio_update_property_postmeta($postId,'fave_property_location',$propLocation);
							self::respacio_update_property_postmeta($postId,'fave_agents',$agent_id);
							self::respacio_update_property_postmeta($postId,'fave_floor_plans_enable',$propIsFloorPlan);
							self::respacio_update_property_postmeta($postId,'floor_plans',$propFloorPlan);//serialize data
							self::respacio_update_property_postmeta($postId,'fave_featured',$propIsFeatured);
							self::respacio_update_property_postmeta($postId,'fave_property_map_address',$propAdd);
							self::respacio_update_property_postmeta($postId,'fave_property_address',$prop_street_address);
							//self::respacio_update_property_postmeta($postId,'fave_property_address',$propAdd);
							self::respacio_update_property_postmeta($postId,'fave_video_url',$propVideoUrl);
							self::respacio_update_property_postmeta($postId,'_dp_original','');
							self::respacio_update_property_postmeta($postId,'houzez_geolocation_lat',$propLat);
							self::respacio_update_property_postmeta($postId,'houzez_geolocation_long',$propLong);
							self::respacio_update_property_postmeta($postId,'fave_single_top_area',$propTerSize);
							self::respacio_update_property_postmeta($postId,'fave_property_zip',$propZip);
							self::respacio_update_property_postmeta($postId,'fave_property_land',$propLand);
							self::respacio_update_property_postmeta($postId,'fave_virtual_tour',$propVTour);
							self::respacio_update_property_postmeta($postId,'fave_private_note',$propPNote);
							self::respacio_update_property_postmeta($postId,'fave_property_map_street_view',$propMapSView);
							self::respacio_update_property_postmeta($postId,'fave_multiunit_plans_enable',$propMUnitPlan);
							self::respacio_update_property_postmeta($postId,'fave_property_sec_price',$fave_property_sec_price);
							self::respacio_update_property_postmeta($postId,'fave_energy_global_index',$fave_energy_global_index);
							self::respacio_update_property_postmeta($postId,'fave_energy_class',$fave_energy_class);
							self::respacio_update_property_postmeta($postId,'fave_prop_homeslider',$fave_prop_homeslider);
							self::respacio_update_property_postmeta($postId,'fave_property_price_postfix',$fave_property_price_postfix);
							self::respacio_update_property_postmeta($postId,'fave_renewable_energy_global_index',$fave_renewable_energy_global_index);
							self::respacio_update_property_postmeta($postId,'fave_energy_performance',$fave_energy_performance);
							self::respacio_update_property_postmeta($postId,'fave_property_land_postfix',$fave_property_land_postfix);
							self::respacio_update_property_postmeta($postId,'fave_single_content_area',$fave_single_content_area);
							//PROPERTY METAS UPDATE END

							//PROPERTY IMAGE START //
							$table_name = $wpdb->prefix . "property_images";
							$post_img = $wpdb->get_results("SELECT id,image_url,image_id FROM $table_name WHERE type = 1 AND image_url != '' AND post_id = ".$postId);
							$img_array = array();
							if(!empty($post_img)){
								$post_img = json_decode(json_encode($post_img), true);
								$img_array = array_column($post_img,"image_url");
								$img_ids = array_column($post_img,"image_id");
							}

							if(!empty($propImages)){
								$propImages = explode(",",$propImages);
								foreach($propImages as $key => $img){

									$img = explode("?image_id=",$img);

									if(!in_array($img[0],$img_array)){

										$is_download = (isset($img[1]) &&  $img[1] > 0) ? 1 : 0 ;
										$image_id = (isset($img[1]) &&  $img[1] > 0) ? $img[1] : '' ;
										$images_array = array(
											"post_id"	    =>	$postId,
											"image_url"	    =>  $img[0],
											"type"		    =>	1,
											"image_id"		    =>	$image_id,
											"is_download"   =>	$is_download
										);
										$table_name = $wpdb->prefix . "property_images";
										$wpdb->insert($table_name,$images_array);
									}
									else if (($key = array_search($img[0], $img_array)) !== false) {

										$image_id = $img_ids[$key];
										if(!empty($image_id)){
											$table_name = $wpdb->prefix . "postmeta";
											$sql = "delete FROM ".$table_name." WHERE post_id = ".$postId." and meta_value = ".$image_id;

											$wpdb->get_results($sql);

											$meta_key = "fave_property_images";
											if($key == 0){
												//$meta_key = "_thumbnail_id";

												$add_same_image = array(
													"post_id"	=>	$postId,
													"meta_key"	=>	"_thumbnail_id",
													"meta_value"	=>	$image_id
												);
												$table_name = $wpdb->prefix . "postmeta";
												$wpdb->insert($table_name,$add_same_image);
											}

											$add_same_image = array(
												"post_id"	=>	$postId,
												"meta_key"	=>	$meta_key,
												"meta_value"	=>	$image_id
											);
											$table_name = $wpdb->prefix . "postmeta";
											$wpdb->insert($table_name,$add_same_image);
											unset($img_array[$key]);
										}
									}
								}
							}

							$post_id = array();
							if(!empty($img_array)){
								if(!empty($post_img)){
									foreach($post_img as $img_str){

										$img_val = $img_str["image_url"];
										if(in_array($img_val,$img_array)){
											$post_id[] = $img_str["image_id"];
										}
									}
								}
							}

							if(!empty($post_id)){
								foreach($post_id as $ids){
									$table = $wpdb->prefix . "posts";
									$wpdb->delete($table, array('ID'=>$ids));

									$table = $wpdb->prefix . "postmeta";
									$wpdb->delete($table, array('post_id'=>$postId,"meta_value"=>$ids));
								}
							}
							//PROPERTY IMAGE END //

							//PROPERTY ATTACHMENT DOCUMENT START //
							$table_name = $wpdb->prefix . "property_images";
							$sql = "SELECT id,image_url,image_id FROM $table_name WHERE type = 2 and post_id = ".$postId;
							$post_attch = $wpdb->get_results($sql);
							$img_array = array();
							if(!empty($post_attch))
							{
								$post_attch = json_decode(json_encode($post_attch), true);
								$img_array = array_column($post_attch,"image_url");
							}

							if(!empty($propAttachment)){
								$propAttachment = explode(",",$propAttachment);

								foreach($propAttachment as $img){

									$img = explode("?image_id=",$img);
									if(!in_array($img[0],$img_array)){

										$is_download = (isset($img[1]) &&  $img[1] > 0) ? 1 : 0 ;
										$image_id = (isset($img[1]) &&  $img[1] > 0) ? $img[1] : '' ;
										$images_array = array(
											"post_id"	    =>	$postId,
											"image_url"	    =>  $img[0],
											"type"		    =>	2,
											"image_id"		    =>	$image_id,
											"is_download"   =>	$is_download,
										);
										$table_name = $wpdb->prefix . "property_images";
										$wpdb->insert($table_name,$images_array);

									} else if (($key = array_search($img[0], $img_array)) !== false) {

										unset($img_array[$key]);

									}
								}
							}

							if(!empty($img_array)){
								$post_id = array();

								if(!empty($post_attch)){
									foreach($post_attch as $img_str){

										$img_val = $img_str["image_url"];
										if(in_array($img_val,$img_array)){
											$post_id[] = $img_str["image_id"];
										}
									}
								}
							}

							if(!empty($post_id)){
								foreach($post_id as $ids){

									$table = $wpdb->prefix . "posts";
									$wpdb->delete($table, array('ID'=>$ids));

									$table = $wpdb->prefix . "postmeta";
									$wpdb->delete($table, array('post_id'=>$postId,"meta_value"=>$ids));
								}
							}
							//PROPERTY ATTACHMENT DOCUMENT END //

							// INSERT PROPERTY FEATURE START
							//Details >> Features
							self::respacio_update_features($postId,$pData['property_feature'],'property_feature');
							// INSERT PROPERTY FEATURE END

							//INSERT PROPERTY TYPE START
							//Details
							self::respacio_update_features($postId,$pData['property_type'],'property_type');
							//INSERT PROPERTY TYPE END

							// INSERT PROPERTY STATUS START
							self::respacio_update_features($postId,$pData['property_status'],'property_status');
							//INSERT PROPERTY STATUS END

							// INSERT PROPERTY CITY START
							self::respacio_update_features($postId,$pData['property_city'],'property_city');
							// INSERT PROPERTY CITY END

							// INSERT PROPERTY LABEL START
							self::respacio_update_features($postId,$pData['property_label'],'property_label');
							// INSERT PROPERTY LABEL END

							//INSERT PROPERTY STATE START
							self::respacio_update_features($postId,$pData['fave_property_country'],'property_country');

							//INSERT PROPERTY STATE START
							self::respacio_update_features($postId,$pData['property_state'],'property_state');
							// INSERT PROPERTY STATE END

							// INSERT PROPERTY AREA START
							self::respacio_update_features($postId,$pData['property_area'],'property_area');
							// INSERT PROPERTY AREA END
						}
					}
				}
			}
		}
	}

	public static function respacio_update_features($postId,$customTaxo,$type){

		global $wpdb;
		if(isset($customTaxo) && !empty($customTaxo)){

			$delete_feature = '' ;

			$propAtt = ($customTaxo);
			$table_name = $wpdb->prefix."term_relationships";

			$term_taxonomy = get_the_terms($postId, $type );

			$exist_array = array();
			if(!empty($term_taxonomy)){
				$term_taxonomy = json_decode(json_encode($term_taxonomy), true);
				$exist_array = array_column($term_taxonomy,"term_id");
			}

			$came_taxonomy = array();
			if(isset($propAtt) && !empty($propAtt)){
				$propAtt = explode('|',$propAtt);
				foreach($propAtt as $pAtt){

					$propFeatureTermId = '';
					$propFeatureTermId = term_exists($pAtt,$type);
					if(!isset($propFeatureTermId) || empty($propFeatureTermId)){
						$propFeatureTermId = wp_insert_term($pAtt,$type);
					}

					if(is_array($propFeatureTermId)){
						$taxonomy = $propFeatureTermId["term_taxonomy_id"];
					}
					else{
						$taxonomy = $propFeatureTermId;
					}

					$came_taxonomy[] = 	$taxonomy;
					try{
						$table_name = $wpdb->prefix."term_relationships";

						$sql = "SELECT object_id,term_taxonomy_id FROM ".$table_name." WHERE object_id = ".$postId." and term_taxonomy_id = ".$taxonomy;

						$check = $wpdb->get_results($sql);

						if(empty($check)){

							$term_relationship = array(
								"object_id"	=>	$postId,
								"term_taxonomy_id"	=>	$taxonomy
							);

							$wpdb->insert($table_name,$term_relationship);
						}

					}
					catch(Exception $e) {

					}

				}
			}

			$delete_feature = array_diff($exist_array,$came_taxonomy);

			if(!empty($delete_feature)){
				$table_name = $wpdb->prefix."term_relationships";
				$sql = "delete FROM ".$table_name." WHERE object_id = ".$postId." and term_taxonomy_id in (".implode(',',$delete_feature).")";
				$wpdb->get_results($sql);
			}
		}
	}

	public static function respacio_update_property_postmeta($postId,$meta_key,$meta_value){
		global $wpdb;

		if(!empty($meta_value)){
			$table_name = $wpdb->prefix . "postmeta";
			$post_img = $wpdb->get_results("SELECT meta_id FROM $table_name WHERE (post_id = ".$postId." AND meta_key = '".$meta_key."')");


			if(!empty($post_img)){

				if($meta_key != "fave_video_image"){
					$table_name = $wpdb->prefix . "postmeta";
					$wpdb->update($table_name, array("meta_value"=>$meta_value),array('meta_id'=>$post_img[0]->meta_id));
				}
			}
			else{

				if($meta_key == "fave_video_image"){
					$url = $meta_value;
					$image_sizes = array(
						array("width"	=>	150,"height"	=>	150,"type"	=>	"thumbnail"),
						array("width"	=>	300,"height"	=>	227,"type"	=>	"medium"),
						array("width"	=>	150,"height"	=>	114,"type"	=>	"post-thumbnail"),
						array("width"	=>	385,"height"	=>	258,"type"	=>	"houzez-property-thumb-image"),
						array("width"	=>	380,"height"	=>	280,"type"	=>	"houzez-property-thumb-image-v2"),
						array("width"	=>	570,"height"	=>	340,"type"	=>	"houzez-image570_340"),
						array("width"	=>	810,"height"	=>	430,"type"	=>	"houzez-property-detail-gallery"),
						array("width"	=>	350,"height"	=>	350,"type"	=>	"houzez-image350_350"),
						array("width"	=>	150,"height"	=>	110,"type"	=>	"thumbnail"),
						array("width"	=>	350,"height"	=>	9999,"type"	=>	"houzez-widget-prop"),
						array("width"	=>	0,"height"	=>	480,"type"	=>	"houzez-image_masonry"),
					);


					$meta_value = respacio_add_postmetadata($postId,$url,$image_sizes,0);

				}

				$meta_add = array(
					"post_id"	=>	$postId,
					"meta_key"	=>	$meta_key,
					"meta_value"	=>	$meta_value
				);

				$wpdb->insert($table_name,$meta_add);
			}
		}
	}
}