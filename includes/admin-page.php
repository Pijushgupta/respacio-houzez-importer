<?php
/**
 * checking if flag as key exits or not.
 */
if(array_key_exists('flag',$_GET) && $_GET["flag"] == 1 ) copy_thumb_image_to_gallary();


function copy_thumb_image_to_gallary(){
    global $wpdb;

    $table_name = $wpdb->prefix."posts";
    $sql = "SELECT ID FROM ".$table_name." WHERE post_type = 'property'";
    $all_properties = $wpdb->get_results($sql);

    $postmeta = $wpdb->prefix."postmeta";
    if(!empty($all_properties)){
        foreach($all_properties as $p){
            $post_id = $p->ID;

            $sql = "SELECT meta_id,post_id,meta_key,meta_value FROM ".$postmeta." WHERE post_id = ".$post_id." and meta_key = '_thumbnail_id'";
            $get_thumb = $wpdb->get_results($sql);

            if(!empty($get_thumb)){

                $sql = "SELECT meta_id,post_id,meta_key,meta_value FROM ".$postmeta." WHERE post_id = ".$post_id." and meta_key = 'fave_property_images' and meta_value = ".$get_thumb[0]->meta_value;
                $check = $wpdb->get_results($sql);

                if(empty($check)){

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
        }
    }
}

function respacio_update_features($postId,$customTaxo,$type){

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

function respacio_add_term_relationship($post_id,$term_id,$type){

    global $wpdb;
    $table_name = $wpdb->prefix . "term_taxonomy";
    $check = $wpdb->get_results("SELECT term_taxonomy_id FROM $table_name WHERE (term_id = ".$term_id.")");
    $table_name = $wpdb->prefix . "term_relationships";
    if(!empty($check)){
        $add_relationship = array(
            "object_id" =>     $post_id,
            "term_taxonomy_id"  =>  $check[0]->term_taxonomy_id,
        );

        $wpdb->insert($table_name,$add_relationship);
    }


}

function respacio_update_property_postmeta($postId,$meta_key,$meta_value){
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

function respacio_cron_log($log_type=''){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    if(!empty($log_type)){
        $table_name = $wpdb->prefix . "cron_log";
        $sql = "CREATE TABLE $table_name (
    		   id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    		   log_type varchar(255),
    		   logtime DATETIME,
    		   PRIMARY KEY (id)
    		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $table_name = $wpdb->prefix . "cron_log";
        $insert_thumb = array(
            "log_type"	=>	$log_type,
            "logtime"	=>	date("Y-m-d H:i:s")
        );

        $wpdb->insert($table_name,$insert_thumb);
    }
}

if(!wp_next_scheduled ('import_images_trigger')){
    wp_schedule_event(time(),'every_five_minutes','import_images_trigger');
}

add_action('import_images_trigger', 'respacio_add_property_image_hourly');

function respacio_add_property_image_hourly() {
    // do something every hour
    $sa_apikey_verify = get_option( 'verify_api');
    if($sa_apikey_verify){
        global $wpdb;
        $table_name = $wpdb->prefix . "property_images";
        $post_img = $wpdb->get_results("SELECT * FROM $table_name WHERE image_url != '' AND is_download = 0 AND type = 1 order by id asc limit 10");
        $image_sizes = respacio_get_image_sizes();

        if(!empty($post_img)){
            foreach($post_img as $key => $val){
                $url = $val->image_url;
                $postId = $val->post_id;
                $id = $val->id;
                respacio_add_postmetadata($postId,$url,$image_sizes,$id);
            }
        }
    }
}

function respacio_pr($arr){
    echo "<pre>";
    print_r($arr);
}

function respacio_get_image_sizes(){
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

function respacio_add_postmetadata($postId,$url,$image_sizes,$id){

    global $wpdb;

    if(!function_exists('wp_get_current_user')) {
        include(ABSPATH . "wp-includes/pluggable.php");
    }

    if(!empty($url)){
        $headers = get_headers($url);
        $attachment_id = '';
        if(!empty($headers) && $headers[0] == "HTTP/1.1 200 OK"){

            $request = wp_remote_get($url, array( 'timeout' => 7200000, 'httpversion' => '1.1' ) );
            $file_content = wp_remote_retrieve_body( $request );
            $res = wp_upload_dir();

            $file_obj = explode("/",$url);
            $full_file_name = $file_obj[count($file_obj)-1];
            list($file_name,$extention) = explode(".",$full_file_name);
            $upload_dir = $res["path"].'/'.$file_name.'.'.$extention;
            $uploaded_url = $res["url"];
            $subdir = $res['subdir'];
            file_put_contents($upload_dir,$file_content);

            $attachment_id = respacio_insert_post_data($postId,$uploaded_url,$file_name,$id,$extention);
            $serialize_array = array(
                "width"	=>	110,
                "height"	=>	200,
                "file"	=>	$subdir.'/'.$file_name.'.'.$extention
            );
            foreach($image_sizes as $ims){

                $width = $ims["width"];
                $height = $ims["height"];
                $new_file_name = $file_name.'-'.$width.'x'.$height.'.'.$extention;
                $upload_dir = $res["path"].'/'.$new_file_name;
                $img_url = $uploaded_url.'/'.$new_file_name;
                file_put_contents($upload_dir,$file_content);

                $image = wp_get_image_editor($upload_dir,array());
                if ( ! is_wp_error( $image ) ) {
                    $image->resize( $width, $height, true );
                    $image->save($upload_dir);
                }

                $serialize_array["sizes"][$ims["type"]] = array(
                    "file"	=>	$new_file_name,
                    "width"	=>	$width,
                    "height"	=>	$height,
                );
            }

            respacio_add_post_metadata($attachment_id,$subdir,$file_name,$serialize_array,$extention);
            if(!empty($id)){
                $table_name = $wpdb->prefix . "property_images";
                $wpdb->update($table_name, array('is_download'=>1,"image_id"=>$attachment_id), array('id'=>$id));
            }
        }

        return $attachment_id;
    }
}

function respacio_insert_post_data($postId,$uploaded_url,$file_name,$flag,$extention){

    global $wpdb;
    $post_array = array(
        "post_author"	=>	1,
        "post_date"		=>	date("Y-m-d H:i:s"),
        "post_date_gmt"	=>	date("Y-m-d H:i:s"),
        "post_status"	=>	'inherit',
        "comment_status"=>	"closed",
        "ping_status"	=>	"closed",
        "post_name"		=>	$file_name,
        "post_parent"	=>	$postId,
        "guid"			=>	$uploaded_url.'/'.$file_name.'.'.$extention,
        "post_type"		=>	"attachment",
        "post_mime_type"=>	"image/jpg",
    );

    $post_attachment_id = wp_insert_post($post_array);
    /*
    $table_name = $wpdb->prefix . "postmeta";
    $insert_thumb = array(
        "post_id"	=>	$postId,
        "meta_key"	=>	"_thumbnail_id",
        "meta_value"	=>	$post_attachment_id
    );

    $wpdb->insert($table_name,$insert_thumb);
    */
    $table_name = $wpdb->prefix . "postmeta";
    $post_img = $wpdb->get_results("SELECT meta_id,meta_value FROM $table_name WHERE (post_id = ".$postId." AND meta_key = '_thumbnail_id')");

    if(!empty($post_attachment_id)){
        if(empty($post_img)){

            if(!empty($flag)){
                $insert_thumb = array(
                    "post_id"	=>	$postId,
                    "meta_key"	=>	"_thumbnail_id",
                    "meta_value"	=>	$post_attachment_id
                );

                $wpdb->insert($table_name,$insert_thumb);

                $insert_thumb = array(
                    "post_id"	=>	$postId,
                    "meta_key"	=>	"fave_property_images",
                    "meta_value"	=>	$post_attachment_id
                );

                $wpdb->insert($table_name,$insert_thumb);
            }
        }
        else if(!empty($post_img) && empty($post_img[0]->meta_value)){
            $table_name = $wpdb->prefix . "postmeta";
            $wpdb->update($table_name, array("meta_value"	=>	$post_attachment_id), array('meta_id'=>$post_img[0]->meta_id));
        }
        else
        {
            if(!empty($flag)){
                $insert_thumb = array(
                    "post_id"	=>	$postId,
                    "meta_key"	=>	"fave_property_images",
                    "meta_value"	=>	$post_attachment_id
                );

                $wpdb->insert($table_name,$insert_thumb);
            }
        }
    }
    return $post_attachment_id;
}


function respacio_add_post_metadata($attachment_id,$subdir,$file_name,$serialize_array,$extention){
    global $wpdb;
    $post_meta = array(
        "post_id"	=>	$attachment_id,
        "meta_key"	=>	'_wp_attached_file',
        'meta_value'	=>	$subdir.'/'.$file_name.'.'.$extention,
    );

    $table_name = $wpdb->prefix . "postmeta";
    $wpdb->insert($table_name,$post_meta);

    $post_meta = array(
        "post_id"	=>	$attachment_id,
        "meta_key"	=>	'_wp_attachment_metadata',
        'meta_value'	=>	serialize($serialize_array),
    );

    $table_name = $wpdb->prefix . "postmeta";
    $wpdb->insert($table_name,$post_meta);
}


function respacio_add_property_documents(){

    global $wpdb;

    //GET DOCUMENTS FROM TABLES
    $table_name = $wpdb->prefix . "property_images";
    $post_docs = $wpdb->get_results("SELECT * FROM $table_name WHERE is_download = 0 AND type = 2 order by id asc limit 300");

    $attachment_id = '' ;
    if(!empty($post_docs)){
        foreach($post_docs as $dKey => $dVal){
            $docUrl = $dVal->image_url;
            $docPostId = $dVal->post_id;
            $docId = $dVal->id;

            $attachment_id = respacio_add_property_docdata($docId,$docPostId,$docUrl);

            if(!empty($attachment_id)){
                $table_name = $wpdb->prefix . "property_images";
                $wpdb->update($table_name, array('is_download'=>1,"image_id"=>$attachment_id), array('id'=>$docId));
            }
        }
    }
}

function respacio_add_property_docdata($docId,$docPostId,$docUrl){

    global $wpdb;

    if(!empty($docUrl)){
        $docHeaders = get_headers($docUrl);

        $docAttachmentId = '';
        if(!empty($docHeaders) && $docHeaders[0] == "HTTP/1.1 200 OK"){

            $request = wp_remote_get($docUrl, array( 'timeout' => 7200000, 'httpversion' => '1.1' ) );
            $doc_content = wp_remote_retrieve_body( $request );

            $res = wp_upload_dir();
            $file_obj = explode("/",$docUrl);
            $full_file_name = $file_obj[count($file_obj)-1];
            list($file_name,$extention) = explode(".",$full_file_name);
            $upload_dir = $res["path"].'/'.$file_name.'.'.$extention;
            $uploaded_url = $res["url"];
            $subdir = $res['subdir'];
            file_put_contents($upload_dir,$doc_content);

            // GET MIME TYPE OF FILE
            //$mimeType = mime_content_type($upload_dir);
            $mimeType = 'application/pdf';
            //INSERT INTO POST TABLE START
            $post_array = array(
                "post_author"	=>	1,
                "post_date"		=>	date("Y-m-d H:i:s"),
                "post_date_gmt"	=>	date("Y-m-d H:i:s"),
                "post_title"    =>  $file_name,
                "post_status"	=>	'inherit',
                "comment_status"=>	"closed",
                "ping_status"	=>	"closed",
                "post_name"		=>	$file_name,
                "post_parent"	=>	$docPostId,
                "guid"			=>	$uploaded_url.'/'.$file_name.'.'.$extention,
                "post_type"		=>	"attachment",
                "post_mime_type"=>	$mimeType
            );

            $post_attachment_id = wp_insert_post($post_array);
            //INSERT INTO POST TABLE END

            //INSERT INTO POST META TABLE START

            $post_meta = array(
                "post_id"	=>	$post_attachment_id,
                "meta_key"	=>	'_wp_attached_file',
                'meta_value'	=>	$subdir.'/'.$file_name.'.'.$extention
            );

            $table_name = $wpdb->prefix . "postmeta";
            $wpdb->insert($table_name,$post_meta);

            $post_meta = array(
                "post_id"	=>	$docPostId,
                "meta_key"	=>	'fave_attachments',
                'meta_value'	=>	$post_attachment_id
            );

            $table_name = $wpdb->prefix . "postmeta";
            $wpdb->insert($table_name,$post_meta);

            // INSERT INTO POST META TABLE END

            return $post_attachment_id ;
        }
    }
}


if (! wp_next_scheduled ('property_attachment_download')) {
    wp_schedule_event(time(),'every_sixty_minutes','property_attachment_download');
}

add_action('property_attachment_download', 'respacio_add_property_documents');


/* VIDEO IMAGE DOWNLOAD START */
function respacio_download_video_image(){

    global $wpdb;

    $table_name = $wpdb->prefix . "property_images";
    $post_img = $wpdb->get_results("SELECT * FROM $table_name WHERE image_url != '' AND is_download = 0 AND type = 3 order by id asc limit 300");

    $image_sizes = respacio_get_image_sizes();

    if(!empty($post_img)){
        foreach($post_img as $key => $val){

            $url = $val->image_url;
            $postId = $val->post_id;
            $id = $val->id;
            respacio_add_imagepostmetadata($postId,$url,$image_sizes,$id);
        }
    }
}

function respacio_add_imagepostmetadata($postId,$url,$image_sizes,$id){

    //echo "<br> post id : ".$postId;
    global $wpdb;
    if(!empty($url)){
        $headers = get_headers($url);

        $attachment_id = '';
        if(!empty($headers) && $headers[0] == "HTTP/1.1 200 OK"){
            $request = wp_remote_get($url, array( 'timeout' => 7200000, 'httpversion' => '1.1' ) );
            $file_content = wp_remote_retrieve_body( $request );

            $res = wp_upload_dir();

            $file_obj = explode("/",$url);
            $full_file_name = $file_obj[count($file_obj)-1];
            list($file_name,$extention) = explode(".",$full_file_name);
            $upload_dir = $res["path"].'/'.$file_name.'.'.$extention;
            $uploaded_url = $res["url"];
            $subdir = $res['subdir'];
            file_put_contents($upload_dir,$file_content);

            //INSERT INTO POST TABLE START //

            $post_array = array(
                "post_author"	=>	1,
                "post_date"		=>	date("Y-m-d H:i:s"),
                "post_date_gmt"	=>	date("Y-m-d H:i:s"),
                "post_status"	=>	'inherit',
                "comment_status"=>	"closed",
                "ping_status"	=>	"closed",
                "post_name"		=>	$file_name,
                "post_parent"	=>	$postId,
                "guid"			=>	$uploaded_url.'/'.$file_name.'.'.$extention,
                "post_type"		=>	"attachment",
                "post_mime_type"=>	"image/jpg",
            );

            $attachment_id = wp_insert_post($post_array);
            // INSERT INTO POST TABLE END

            //respacio_update_property_postmeta($postId,'fave_video_image',$attachment_id);
            add_post_meta($postId,'fave_video_image',$attachment_id, true );

            foreach($image_sizes as $ims){

                $width = $ims["width"];
                $height = $ims["height"];
                $new_file_name = $file_name.'-'.$width.'x'.$height.'.'.$extention;
                $upload_dir = $res["path"].'/'.$new_file_name;
                file_put_contents($upload_dir,$file_content);

                $image = wp_get_image_editor($upload_dir);
                if ( ! is_wp_error( $image ) ) {
                    $image->resize( $width, $height, true );
                    $image->save($upload_dir);
                }

                $serialize_array["sizes"][$ims["type"]] = array(
                    "file"	=>	$new_file_name,
                    "width"	=>	$width,
                    "height"	=>	$height,
                );
            }

            if(isset($attachment_id) && !empty($attachment_id)){

                respacio_add_post_metadata($attachment_id,$subdir,$file_name,$serialize_array,$extention);

            }

            if(!empty($id)){
                $table_name = $wpdb->prefix . "property_images";
                $wpdb->update($table_name, array('is_download'=>1,"image_id"=>$attachment_id), array('id'=>$id));
            }

        }

        return $attachment_id;
    }
}
/* VIDEO IMAGE DOWNLOAD END */

if (! wp_next_scheduled ('add_hourly_properties_url')) {
    wp_schedule_event(time(),'every_sixty_minutes','add_hourly_properties_url');
}

add_action('add_hourly_properties_url', 'respacio_update_property_link');

function respacio_update_property_link(){
    global $wpdb;

    $table_name = $wpdb->prefix . "posts as p";
    $join = $wpdb->prefix . "postmeta as pm";
    $post_img = $wpdb->get_results("SELECT p.ID,p.guid,pm.meta_value,p.post_name FROM $table_name left join $join on pm.post_id = p.ID WHERE p.post_type = 'property' and pm.meta_key = 'fave_property_id'");

    $api_key = get_option( 'property_verification_api');
    $data = array("property_friendly_url"	=>	json_encode($post_img));
    $propData = wp_remote_post(RHIMO_PROPERTY_WEB_URL, array(
        'method'      => 'POST',
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
}

if (! wp_next_scheduled ('add_video_images')) {
    wp_schedule_event(time(),'every_ninteen_minutes','add_video_images');
}

add_action('add_video_images', 'respacio_download_video_image');

function respacio_export(){

    $template_path = plugin_dir_path( __FILE__ ) . "template/export.php";
    require_once ($template_path);
}

function respacio_export_XML($finalFilePath,$finalFileSrc){

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


function respacio_export_XLS($finalFilePath,$finalFileSrc){

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
?>