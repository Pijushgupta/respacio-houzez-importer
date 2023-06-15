<?php

namespace RespacioHouzezImport;

class export {
	public static function init(){
		\RespacioHouzezImport\export::downloadModal();
        \RespacioHouzezImport\export::handleSubmit();
        \RespacioHouzezImport\export::exportSelectionUi();

	}

	/**
     * This to show download/copy newly generated xls file after export
	 * @return void
	 */
	public static function downloadModal(){ ?>
		<div class="respacio-notice respacio-notice-export">
			<div id="myModal" class="modal">
			<!-- Modal content -->
				<div class="modal-content">
					<span class="close" onclick="respacio_hideModal();">&times;</span>
					<div class="modal-header"><h2>Exported URL</h2></div>
					<div class="modal-body">
						<p id="contentText"></p>
						<input type="text" id="contentText1" valuae="" />
						<a id="copyClip" class="copyClip" onclick="respacio_copyURL();">Copy URL</a>
					</div>
				</div>
			</div>
		</div>
	<?php }

	public static function respacio_export_XML($finalFilePath,$finalFileSrc){

		global $wpdb;

		/* GET PROPERTIES FROM wp_posts TABLE START */
		$args = array(
			'post_type'   => 'property',
			'numberposts' => -1,
			'post_status' => 'any'
		);

		$properties = get_posts( $args );
		//echo '<pre> ';  print_r($properties); die;
		if(isset($properties) && !empty($properties)){

			$doc = new DOMDocument();
			$doc->formatOutput = true;

			$mainTag = $doc->createElement("data");
			$doc->appendChild( $mainTag );

			// APPEND PROPERTY DATA TO XML START //
			foreach($properties as $property){

				//echo '<pre> ';  print_r($property); die;
				$favPropImgs = $favAttachments = array();

				$property_id = $property->ID ;
				$propertyMetaDatas = get_post_meta($property_id,false,false);
				//echo '<pre> ';  print_r($propertyMetaDatas); die;

				$propTag = $doc->createElement("Property");

				$post_id = $doc->createElement("post_id");
				$post_id->appendChild($doc->createTextNode($property_id));
				$propTag->appendChild( $post_id );

				$property_title = $doc->createElement("post_title");
				$property_title->appendChild($doc->createTextNode($property->post_title));
				$propTag->appendChild( $property_title );

				$property_content = $doc->createElement("post_content");
				$property_content->appendChild($doc->createTextNode($property->post_content));
				$propTag->appendChild( $property_content );

				$post_name = $doc->createElement("post_name");
				$post_name->appendChild($doc->createTextNode($property->post_name));
				$propTag->appendChild( $post_name );

				$property_modified = $doc->createElement("post_modified");
				$property_modified->appendChild($doc->createTextNode($property->post_modified));
				$propTag->appendChild( $property_modified );

				$property_excerpt = $doc->createElement("post_excerpt");
				$property_excerpt->appendChild($doc->createTextNode($property->post_excerpt));
				$propTag->appendChild( $property_excerpt );
				$is_private = 0;
				$post_status = $doc->createElement("post_status");
				$post_status1 = $property->post_status ;
				if($post_status1 == 'publish'){
					$post_status1 = 'Active' ;
				}
				else if($post_status1 == 'private'){
					$is_private = 1;
					$post_status1 = 'Active' ;
				}
				else {
					$post_status1 = 'Inactive' ;
				}
				$post_status->appendChild($doc->createTextNode($post_status1));
				$propTag->appendChild( $post_status );

				$property_private = $doc->createElement("is_private");
				$property_private->appendChild($doc->createTextNode($is_private));
				$propTag->appendChild( $property_private );

				//GET META DATA START
				if(isset($propertyMetaDatas) && !empty($propertyMetaDatas)){
					foreach($propertyMetaDatas as $propertyMetaKey=>$propertyMetaVal){

						if($propertyMetaKey != 'fave_attachments' && $propertyMetaKey != 'fave_currency_info' && $propertyMetaKey != 'floor_plans' && $propertyMetaKey != 'fave_property_images' && !empty($propertyMetaVal)){
							$$propertyMetaKey = $doc->createElement($propertyMetaKey);
						}

						if($propertyMetaKey == '_thumbnail_id' || $propertyMetaKey == 'fave_video_image' || $propertyMetaKey == 'fave_prop_slider_image'){

							$postMetaVal = get_the_guid($propertyMetaVal[0]) ;
							if(isset($postMetaVal) && !empty($postMetaVal)){
								$postMetaVal .= '?image_id='.$propertyMetaVal[0] ;
							}
						} elseif($propertyMetaKey == 'fave_agents'){

							$postMetaVal = '' ;
							$agnetId = $propertyMetaVal[0] ;

							if(isset($agnetId) && !empty($agnetId)){

								//GET AGENT NAME
								$postMetaVal .= get_the_title($agnetId);

								// GET AGENT EMAIL ID
								$agentEmail = get_post_meta($agnetId,'fave_agent_email',true);
								if(isset($agentEmail) && !empty($agentEmail)){
									$postMetaVal .= " | ".$agentEmail;
								}

								// GET AGENT WORK NUMBER
								$agentOfcNo = get_post_meta($agnetId,'fave_agent_office_num',true);
								if(isset($agentOfcNo) && !empty($agentOfcNo)){
									$postMetaVal .= " | ".$agentOfcNo;
								}

								// GET IMAGE URL
								$agentThumbId = get_post_meta($agnetId,'_thumbnail_id',true);
								if(isset($agentThumbId) && !empty($agentThumbId)){
									$postMetaVal .= " | ".get_the_guid($agentThumbId) ;
								}

							}

						} else if($propertyMetaKey == 'fave_property_images'){

							$favPropImgs = $propertyMetaVal ;

						} else if($propertyMetaKey == 'houzez_views_by_date'){

							if(!empty($propertyMetaVal[0])){

								$unSerializeData = array_keys(unserialize($propertyMetaVal[0]));
								if(isset($unSerializeData) && !empty($unSerializeData)){

									$propertyCreateDate = $doc->createElement('property_create_date');
									$propertyCreateDate->appendChild($doc->createTextNode($unSerializeData[0]));
									$propTag->appendChild( $propertyCreateDate );

									if(!empty($unSerializeData[1])){
										$propertyModifiedDate = $doc->createElement('property_modified_date');
										$propertyModifiedDate->appendChild($doc->createTextNode($unSerializeData[1]));
										$propTag->appendChild($propertyModifiedDate);
									}
								}
							}

						}else if($propertyMetaKey == 'floor_plans'){

							$floorPlanData = unserialize($propertyMetaVal[0]);
							//if($property_id == '8408'){ echo '<pre>' ; print_r($floorPlanData) ;  die; }
							if(isset($floorPlanData) && !empty($floorPlanData)){

								$floorPlanWrapTag = $doc->createElement("floorplans");
								foreach($floorPlanData as $fpData){

									if(isset($fpData['fave_plan_image']) && !empty($fpData['fave_plan_image'])){
										$fpDataImgId = $fpData['fave_plan_image'] ;
										$floorPlanID = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid=%s", $fpDataImgId ) );
										if(isset($floorPlanID) && !empty($floorPlanID)){
											$fpDataImgId .= '?image_id='.$floorPlanID ;
										}

										$fpData1 = $doc->createElement("image");
										$fpData1->appendChild($doc->createTextNode($fpDataImgId));
										$floorPlanWrapTag->appendChild($fpData1);
									}

								}
								$propTag->appendChild( $floorPlanWrapTag );
							}

						} else if($propertyMetaKey == 'additional_features'){

							$addiFeaturesData = unserialize($propertyMetaVal[0]);
							//echo '<pre>' ; print_r($addiFeaturesData); die;
							if(isset($addiFeaturesData) && !empty($addiFeaturesData)){
								$postMetaVal = '' ;
								foreach($addiFeaturesData as $addFeaData){
									if(!empty($postMetaVal)){
										$postMetaVal .= " | ";
									}
									$postMetaVal .= $addFeaData['fave_additional_feature_title']." : ".$addFeaData['fave_additional_feature_value'] ;

								}
							}

						} else if($propertyMetaKey == 'fave_attachments'){

							$favAttachments = $propertyMetaVal ;

						} else if( $propertyMetaKey == '_houzez_expiration_date'){

							$postMetaVal = date('d-m-Y h:i:s',$propertyMetaVal[0]);

						} else if($propertyMetaKey == 'fave_virtual_tour'){

							$virtualTour = $propertyMetaVal[0];
							if(isset($virtualTour) && !empty($virtualTour)){
								preg_match('@src="([^"]+)"@' ,$virtualTour,$match);
								if(isset($match[1])){
									$postMetaVal = $match[1] ;
								} else {
									$postMetaVal = $virtualTour ;
								}
							}
						} else {

							$postMetaVal = $propertyMetaVal[0];

						}

						if($propertyMetaKey != 'fave_attachments' && $propertyMetaKey != 'fave_currency_info' && $propertyMetaKey != 'floor_plans' && $propertyMetaKey != 'fave_property_images' && !empty($propertyMetaVal)){
							$$propertyMetaKey->appendChild($doc->createTextNode($postMetaVal));
							$propTag->appendChild( $$propertyMetaKey );
						}

						$$propertyMetaKey = $postMetaVal = '' ;
					}
				}
				// GET META DATA END

				//echo '<pre>'; print_r($favAttachments); die;
				// PROPERTY IMAGE START
				if(isset($favPropImgs) && !empty($favPropImgs)){

					$imageWrapTag = $doc->createElement("images");
					foreach($favPropImgs as $favPropImg){

						$imgUrl = get_the_guid($favPropImg);
						$imgUrl .= '?image_id='.$favPropImg ;

						$favPropImg1 = $doc->createElement("image");
						$favPropImg1->appendChild($doc->createTextNode($imgUrl));
						$imageWrapTag->appendChild( $favPropImg1 );

					}
					$propTag->appendChild( $imageWrapTag );

				}
				// PROPERTY IMAGE END

				// PROPERTY ATTACHMENT START
				if(isset($favAttachments) && !empty($favAttachments)){

					$attachWrapTag = $doc->createElement("fave_attachments");
					foreach($favAttachments as $favAttachment){

						$attchUrl = get_the_guid($favAttachment);
						$attchUrl .= '?image_id='.$favAttachment ;

						$favAttachment1 = $doc->createElement("image");
						$favAttachment1->appendChild($doc->createTextNode($attchUrl));
						$attachWrapTag->appendChild( $favAttachment1 );

					}
					$propTag->appendChild( $attachWrapTag );

				}
				// PROPERTY ATTACHMENT END

				$property_type = wp_get_post_terms( $property_id, 'property_type');
				if(isset($property_type) && !empty($property_type)){

					$property_type_names = implode('| ',wp_list_pluck($property_type,'name'));
					$propertyType = $doc->createElement("property_type");
					$propertyType->appendChild($doc->createTextNode($property_type_names));
					$propTag->appendChild($propertyType);

				}

				$property_status = wp_get_post_terms( $property_id, 'property_status');
				if(isset($property_status) && !empty($property_status)){

					$property_status_names = implode('| ',wp_list_pluck($property_status,'name'));
					$propertyStatus = $doc->createElement("property_status");
					$propertyStatus->appendChild($doc->createTextNode($property_status_names));
					$propTag->appendChild($propertyStatus);

				}

				$property_features = wp_get_post_terms( $property_id, 'property_feature');
				if(isset($property_features) && !empty($property_features)){

					$property_features_names = implode('| ',wp_list_pluck($property_features,'name'));
					$propertyFeature = $doc->createElement("property_feature");
					$propertyFeature->appendChild($doc->createTextNode($property_features_names));
					$propTag->appendChild( $propertyFeature );

				}

				$property_labels = wp_get_post_terms( $property_id, 'property_label');
				if(isset($property_labels) && !empty($property_labels)){

					$property_label_names = implode('| ',wp_list_pluck($property_labels,'name'));
					$propertyLabel = $doc->createElement("property_label");
					$propertyLabel->appendChild($doc->createTextNode($property_label_names));
					$propTag->appendChild( $propertyLabel );

				}

				$property_city = wp_get_post_terms( $property_id, 'property_city');
				if(isset($property_city) && !empty($property_city)){

					$property_city_names = implode('| ',wp_list_pluck($property_city,'name'));
					$propertyCity = $doc->createElement("property_city");
					$propertyCity->appendChild($doc->createTextNode($property_city_names));
					$propTag->appendChild( $propertyCity );

				}

				$property_state = wp_get_post_terms( $property_id, 'property_state');
				if(isset($property_state) && !empty($property_state)){

					$property_state_names = implode('| ',wp_list_pluck($property_state,'name'));
					$propertyState = $doc->createElement("property_state");
					$propertyState->appendChild($doc->createTextNode($property_state_names));
					$propTag->appendChild( $propertyState );

				}

				$property_area = wp_get_post_terms( $property_id, 'property_area');
				if(isset($property_area) && !empty($property_area)){

					$property_area_names = implode('| ',wp_list_pluck($property_area,'name'));
					$propertyArea = $doc->createElement("property_area");
					$propertyArea->appendChild($doc->createTextNode($property_area_names));
					$propTag->appendChild( $propertyArea );

				}

				$currencyInfo = $doc->createElement("fave_currency_info");
				$currencyInfo->appendChild($doc->createTextNode("€"));
				$propTag->appendChild( $currencyInfo );

				$mainTag->appendChild( $propTag );

			}
			// APPEND PROPERTY DATA TO XML END //

			// SAVE XML
			$doc->saveXML();
			$xml = $doc->save($finalFilePath);


			if($xml !== false){
				?>
                <script>respacio_showModal('<?php echo $finalFileSrc;?>');</script>
				<?php
			} else {
				echo $xml ;
			}
		}
		/* GET PROPERTIES FROM wp_posts TABLE END */

	}


	public static function respacio_export_XLS($finalFilePath,$finalFileSrc){

		$args = array(
			'post_type'   => 'property',
			'numberposts' => -1,
			'post_status' => 'any'
		);

		$properties = get_posts( $args );

		header("Content-Disposition: attachment; filename=\"$finalFilePath\"");
		header("Content-Type: application/vnd.ms-excel");
		header("Pragma: no-cache");
		header("Expires: 0");

		$finalData = array();
		$headings[] = array("post_id","post_title","post_content","post_modified","slide_template","_thumbnail_id","fave_property_size","fave_property_size_prefix","fave_property_bedrooms","fave_property_bathrooms","fave_property_garage","fave_property_garage_size","fave_property_year","fave_property_id","fave_property_price","fave_property_price_postfix","fave_property_map","fave_property_map_address","fave_property_location","fave_property_country","fave_agents","fave_additional_features_enable","additional_features","fave_floor_plans_enable","floor_plans","fave_featured","fave_property_address","fave_property_zip","fave_video_url","fave_payment_status","fave_property_map_street_view","_dp_original","fave_property_sec_price","houzez_total_property_views","fave_multiunit_plans_enable","property_create_date","property_modified_date","houzez_recently_viewed","houzez_geolocation_lat","houzez_geolocation_long","fave_virtual_tour","fave_single_top_area","fave_single_content_area","fave_agent_display_option","fave_property_agency","_edit_lock","_edit_last","fave_currency_info","houzez_manual_expire","_houzez_expiration_date_status","fave_video_image","fave_attachments","images","property_type","property_status","property_feature","property_label","property_city","property_state","post_status");

		$out = fopen($finalFilePath, 'w');

		foreach($headings as $heading) {

			fputcsv($out, $heading,"\t");

		}
		foreach($properties as $property) {

			$propertyId = $property->ID;
			$propertyMetaDatas = get_post_meta($propertyId,false,false);

			$favPropImgs = $favAttachments = array();

			$propertyTitle = $propertyContent = $propertyModified = $slide_template = $_thumbnail_id = $fave_property_size = $fave_property_size_prefix = $fave_property_bedrooms = $fave_property_bathrooms = $fave_property_garage = $fave_property_garage_size = $fave_property_year = $fave_property_id = $fave_property_price = $fave_property_price_postfix = $fave_property_map = $fave_property_map_address = $fave_property_location = $fave_property_country = $fave_agents = $fave_additional_features_enable = $additional_features = $fave_floor_plans_enable = $floor_plans = $fave_featured = $fave_property_address = $fave_property_zip = $fave_video_url = $fave_payment_status = $fave_property_map_street_view = $_dp_original = $fave_property_sec_price = $houzez_total_property_views = $fave_multiunit_plans_enable = $propertyCreateDate = $propertyModifiedDate = $houzez_recently_viewed = $houzez_geolocation_lat = $houzez_geolocation_long = $fave_virtual_tour = $fave_single_top_area = $fave_single_content_area = $fave_agent_display_option = $fave_property_agency = $_edit_lock = $_edit_last = $fave_currency_info = $houzez_manual_expire = $_houzez_expiration_date_status = $fave_property_images = $fave_video_image = $favAtta = $images = $property_type = $property_status = $property_feature = $property_label = $property_city = $property_state = $post_status = '' ;

			$propertyTitle = $property->post_title;
			$propertyContent = $property->post_content;
			$propertyModified = $property->post_modified;
			$fave_currency_info = "€" ;
			$post_status = $property->post_status ;
			if($post_status == 'publish'){
				$post_status = 'Active' ;
			} else {
				$post_status = 'Inactive' ;
			}

			if(isset($propertyMetaDatas) && !empty($propertyMetaDatas)){
				foreach($propertyMetaDatas as $propertyMetaKey=>$propertyMetaVal){

					if($propertyMetaKey == '_thumbnail_id' || $propertyMetaKey == 'fave_video_image'){

						$$propertyMetaKey = get_the_guid($propertyMetaVal[0]) ;
						if(isset($propertyMetaKey) && !empty($propertyMetaKey)){
							$$propertyMetaKey .= '?image_id='.$propertyMetaVal[0] ;
						}

					} elseif($propertyMetaKey == 'fave_agents'){

						$$propertyMetaKey = '' ;
						$agnetId = $propertyMetaVal[0] ;

						if(isset($agnetId) && !empty($agnetId)){

							//GET AGENT NAME
							$$propertyMetaKey .= get_the_title($agnetId);

							// GET AGENT EMAIL ID
							$agentEmail = get_post_meta($agnetId,'fave_agent_email',true);
							if(isset($agentEmail) && !empty($agentEmail)){
								$$propertyMetaKey .= " | ".$agentEmail;
							}

							// GET AGENT WORK NUMBER
							$agentOfcNo = get_post_meta($agnetId,'fave_agent_office_num',true);
							if(isset($agentOfcNo) && !empty($agentOfcNo)){
								$$propertyMetaKey .= " | ".$agentOfcNo;
							}

							// GET IMAGE URL
							$agentThumbId = get_post_meta($agnetId,'_thumbnail_id',true);
							if(isset($agentThumbId) && !empty($agentThumbId)){
								$$propertyMetaKey .= " | ".get_the_guid($agentThumbId) ;
							}

						}

					} else if($propertyMetaKey == 'fave_property_images'){

						$favPropImgs = $propertyMetaVal ;
						//array_push($favPropImgs,$propertyMetaVal[0]);

					} else if($propertyMetaKey == 'houzez_views_by_date'){

						if(!empty($propertyMetaVal[0])){

							$unSerializeData = array_keys(unserialize($propertyMetaVal[0]));
							if(isset($unSerializeData) && !empty($unSerializeData)){
								$propertyCreateDate = $unSerializeData[0];
								$propertyModifiedDate = $unSerializeData[1];
							}
						}

					} else if($propertyMetaKey == 'floor_plans'){

						$floorPlanData = unserialize($propertyMetaVal[0]);

						if(isset($floorPlanData) && !empty($floorPlanData)){
							$fpData1 = '' ;
							foreach($floorPlanData as $fpData){
								if(isset($fpData1) && !empty($fpData1)){ $fpData1 .= " | "; }
								$fpData1 .= $fpData['fave_plan_image'] ;
							}
						}

					}   else if($propertyMetaKey == 'additional_features'){

						$addiFeaturesData = unserialize($propertyMetaVal[0]);
						if(isset($addiFeaturesData) && !empty($addiFeaturesData)){
							$addiFeatureData = '' ;
							foreach($addiFeaturesData as $addFeaData){
								if(!empty($addiFeatureData)){
									$addiFeatureData .= " | ";
								}
								$addiFeatureData .= $addFeaData['fave_additional_feature_title']." : ".$addFeaData['fave_additional_feature_value'] ;
							}
						}

					}   else if($propertyMetaKey == 'fave_currency_info'){

						$$propertyMetaKey = "€" ;

					}   else if($propertyMetaKey == 'fave_attachments'){

						$favAttachments = $propertyMetaVal ;

					}   else {

						$$propertyMetaKey = $propertyMetaVal[0];

					}

				}
			}

			if(isset($favPropImgs) && !empty($favPropImgs)){

				$images = $imgUrl = '' ;
				foreach($favPropImgs as $favPropImg){

					$imgUrl = '' ;
					$imgUrl = get_the_guid($favPropImg);
					if(isset($imgUrl) && !empty($imgUrl)){ $imgUrl .= '?image_id='.$favPropImg ; }
					if(isset($images) && !empty($images)){ $images .= ' | ' ;}
					$images .= $imgUrl ;

				}
			}


			// PROPERTY ATTACHMENT START
			if(isset($favAttachments) && !empty($favAttachments)){

				$favAtta = $attchUrl = '' ;
				foreach($favAttachments as $favAttachment){

					$attchUrl = '' ;
					$attchUrl = get_the_guid($favAttachment);
					if(isset($attchUrl) && !empty($attchUrl)){ $attchUrl .= '?image_id='.$favAttachment ; }
					if(isset($favAtta) && !empty($favAtta)){ $favAtta .= ' | ' ;}
					$favAtta .= $attchUrl ;

				}
			}

			// PROPERTY ATTACHMENT END

			$property_type1 = wp_get_post_terms( $propertyId, 'property_type');
			if(isset($property_type1) && !empty($property_type1)){
				$property_type = implode(' | ',wp_list_pluck($property_type1,'name'));
			}

			$property_status1 = wp_get_post_terms( $propertyId, 'property_status');
			if(isset($property_status1) && !empty($property_status1)){

				$property_status = implode(' | ',wp_list_pluck($property_status1,'name'));

			}

			$property_features1 = wp_get_post_terms( $propertyId, 'property_feature');
			if(isset($property_features1) && !empty($property_features1)){

				$property_feature = implode(' | ',wp_list_pluck($property_features1,'name'));

			}

			$property_labels1 = wp_get_post_terms( $propertyId, 'property_label');

			if(isset($property_labels1) && !empty($property_labels1)){

				$property_label = implode(' | ',wp_list_pluck($property_labels1,'name'));

			}

			$property_city1 = wp_get_post_terms( $propertyId, 'property_city');
			if(isset($property_city1) && !empty($property_city1)){

				$property_city = implode(' | ',wp_list_pluck($property_city1,'name'));

			}

			$property_state1 = wp_get_post_terms( $propertyId, 'property_state');
			if(isset($property_state1) && !empty($property_state1)){

				$property_state = implode(' | ',wp_list_pluck($property_state1,'name'));

			}

			$row = array($propertyId,$propertyTitle,$propertyContent,$propertyModified,$slide_template,$_thumbnail_id,$fave_property_size,$fave_property_size_prefix,$fave_property_bedrooms,$fave_property_bathrooms,$fave_property_garage,$fave_property_garage_size,$fave_property_year,$fave_property_id,$fave_property_price,$fave_property_price_postfix,$fave_property_map,$fave_property_map_address,$fave_property_location,$fave_property_country,$fave_agents,$fave_additional_features_enable,$addiFeatureData,$fave_floor_plans_enable,$fpData1,$fave_featured,$fave_property_address,$fave_property_zip,$fave_video_url,$fave_payment_status,$fave_property_map_street_view,$_dp_original,$fave_property_sec_price,$houzez_total_property_views,$fave_multiunit_plans_enable,$propertyCreateDate,$propertyModifiedDate,$houzez_recently_viewed,$houzez_geolocation_lat,$houzez_geolocation_long,$fave_virtual_tour,$fave_single_top_area,$fave_single_content_area,$fave_agent_display_option,$fave_property_agency,$_edit_lock,$_edit_last,$fave_currency_info,$houzez_manual_expire,$_houzez_expiration_date_status,$fave_video_image,$favAtta,$images,$property_type,$property_status,$property_feature,$property_label,$property_city,$property_state,$post_status);

			fputcsv($out, $row,"\t");
		}

		fclose($out);
		if($out){
			?>
            <script>respacio_showModal('<?php echo $finalFileSrc;?>');</script>
			<?php
		} else {
			echo $out ;
		}
	}

    public static function handleSubmit(){

        global $wpdb;
        $exportType = !empty(sanitize_text_field($_POST['export_type'])) ? trim(sanitize_text_field($_POST['export_type'])) : 'XML' ;

        $uploadFolderPath = wp_upload_dir();
        $uploadBaseDir = $uploadFolderPath['basedir'] ;
        $uploadBaseUrl = $uploadFolderPath['baseurl'] ;

        $finalFilePath = $uploadBaseDir.'/properties_export/';
        $finalFileSrc = $uploadBaseUrl.'/properties_export/';

        if (!file_exists($finalFilePath)) {
            mkdir($finalFilePath, 0777, true);
        }

        if($exportType == 'XML'){

            $fileName = 'properties_export_'.date('dmYhis').'.xml';
            $finalFilePath .= $fileName ;
            $finalFileSrc .= $fileName ;

            \RespacioHouzezImport\export::respacio_export_XML($finalFilePath,$finalFileSrc);
        } else {

            $fileName = 'properties_export_'.date('dmYhis').'.xls';
            $finalFilePath .= $fileName ;
            $finalFileSrc .= $fileName ;

            \RespacioHouzezImport\export::respacio_export_XLS($finalFilePath,$finalFileSrc);
        }

    }

	public static function exportSelectionUi(){ ?>
        <div class="respacio-notice">
            <h2 class="activation_title">Export Properties</h2>

            <form action="" method="post">
                <div id="title-wrap" class="input-text-wrap">
                    <input type="radio" name="export_type" class="export_type" id="export_type" value="XML" checked="checked" /> XML
                    <input type="radio" name="export_type" class="export_type" id="export_type" value="Excel" /> Excel
                </div>
                <input type="submit" name="submit" class="submit btn btn-submit" id="submit" value="Submit" />
            </form>
        </div>
	<?php }
}