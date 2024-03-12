<?php
namespace RespacioHouzezImport;
class cpt{

    public static function configure(){
        if ( ! post_type_exists( 'property_log' ) ){
        // Labels for the custom post type
        $labels = array(
            'name'               => __( 'Property Logs', 'text-domain' ),
            'singular_name'      => __( 'Property Log', 'text-domain' ),
            'add_new'            => __( 'Add New', 'text-domain' ),
            'add_new_item'       => __( 'Add New Property Log', 'text-domain' ),
            'edit_item'          => __( 'Edit Property Log', 'text-domain' ),
            'new_item'           => __( 'New Property Log', 'text-domain' ),
            'view_item'          => __( 'View Property Log', 'text-domain' ),
            'search_items'       => __( 'Search Property Logs', 'text-domain' ),
            'not_found'          => __( 'No property logs found', 'text-domain' ),
            'not_found_in_trash' => __( 'No property logs found in Trash', 'text-domain' ),
            'parent_item_colon'  => __( 'Parent Property Log:', 'text-domain' ),
            'menu_name'          => __( 'Property Logs', 'text-domain' ),
        );

        // Arguments for registering the custom post type
        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => 'property-log' ), // Custom slug
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
        );

        // Register the custom post type
        add_action('init',function() use ($args){
            register_post_type( 'property_log', $args );
        });
        

        }
    }
}