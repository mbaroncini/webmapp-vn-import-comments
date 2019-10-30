<?php

/*
  Plugin Name: Verde Natura - Import Comments
  Description: Import Comments from csv
  Author: Marco Baroncini
  Version: 0.1
 */


define('VN_IC_CSV_PATH' , __DIR__ . '/commenti.csv');
define('VN_IC_BACKUP_DIR' , __DIR__ . '/backups/');
/**
 * Load WP CLI commands and utils
 */

if ( defined( 'WP_CLI' ) && WP_CLI )
{



    include_once('functions.php');
    include_once('WebMapp_WpCli_Utils_vncimport.php');
    /**
     * Returns all post meta of post id provided
     *
     *
     * @when after_wp_load
     */
    $wm_get_meta = function( $args, $assoc_args )
    {

        $wp_cli_utils = new WebMapp_WpCli_Utils_vncimport();
        $wp_cli_utils->backup_db( VN_IC_BACKUP_DIR . time() . '.sql' );

        $comments = vn_ic_csv_to_array(VN_IC_CSV_PATH );
        foreach ( $comments as $comment )
            vn_ic_update_create_comment( $comment );




    };

    WP_CLI::add_command( 'vn-import-comments', $wm_get_meta );
}

