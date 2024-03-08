<?php
/*
 * unable to find the usage
 */
if(!function_exists('respacio_add_term_relationship')){
	function respacio_add_term_relationship($post_id,$term_id,$type){

		global $wpdb;
		$table_name = $wpdb->prefix . 'term_taxonomy';
		$check = $wpdb->get_results("SELECT term_taxonomy_id FROM $table_name WHERE (term_id = ".$term_id . ')' );
		$table_name = $wpdb->prefix . 'term_relationships';
		if(!empty($check)){
			$add_relationship = [
				'object_id'        =>     $post_id,
				'term_taxonomy_id' =>  $check[0]->term_taxonomy_id,
			];

			$wpdb->insert($table_name,$add_relationship);
		}


	}
}


/*
 * unable to find the usage
 */
if(!function_exists('respacio_pr')){
	function respacio_pr($arr){
		echo '<pre>';
		print_r($arr);
	}
}

?>