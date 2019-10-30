<?php


function vn_ic_csv_to_array( $path )
{
    $array= array();
    $indexes = array();


    $row = 0;
    if (($handle = fopen($path, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
        {
            $num = count($data);
            for ($c=0; $c < $num; $c++)
            {
                if ( $row == 0 )
                    $indexes[] = $data[$c];
                else
                    $array[$row][ $indexes[$c] ] = $data[$c];
            }
            $row++;
        }
        fclose($handle);
    }

    return $array;
}


function vn_ic_update_create_comment( $comment )
{
    //get fields
    $route_code = $comment['route-code'];

    $routes = get_posts(
        array(
            'post_type' => 'route',
            'nopaging' => true,
            'meta_key' => 'n7webmapp_route_cod',
            'meta_value' => $route_code
        )
    );

    if ( ! $routes )
    {
        echo "ERROR: Impossible import comment with route code $route_code\n";
        return false;
    }


    $comment_date = $comment['data'];
    $comment_date_gmt = get_gmt_from_date( $comment_date );

    $images  = $comment['Attachments'];
    $comment_content = isset( $comment['msg'] ) ? $comment['msg'] : '';

    $comment_route_id = reset($routes)->ID;
    $comment_author_name = isset( $comment['nome'] ) ? $comment['nome'] : '';
    //$comment_journey_date = isset( $comment['data'] ) ? $comment['data'] : '';


    //set fields
    $comment_metafields = array(
        //'wm_comment_journey_date' => $comment_journey_date
    );
    $comment_data = array(
        'comment_author' => $comment_author_name,
        'comment_content' => $comment_content,
        'comment_date' => $comment_date,
        'comment_date_gmt' => $comment_date_gmt,
        'comment_post_ID' => $comment_route_id,
        //'comment_type' => 'comment',//static
        //'comment_meta' => $comment_metafields
    );
    $parent_comment = vn_ic_insert_comment( $comment_data );
    if ( $parent_comment )
    {
        //gallery
        $gallery_images = $images;
        if ( $gallery_images )
        {

           $urls = explode(',', $images );
           $wp_cli_utils = new WebMapp_WpCli_Utils_vncimport();
            //update media
            foreach ( $urls as $key => $url )
            {
                $attachment_id = $wp_cli_utils->import_media( $url );
                $attachment_id_sanitized = intval( $attachment_id );
                $post = get_post($attachment_id_sanitized);
                if ( $post instanceof WP_Post )
                    $attachments[] = $attachment_id_sanitized;
            }
            if ( ! empty( $attachments ) )
                update_field( 'wm_comment_gallery', $attachments, 'comment_' . $parent_comment );
        }

    }
    return $parent_comment;
}
function vn_ic_insert_comment( $comment_data )
{
    $check = wp_insert_comment( $comment_data );
    if ( ! $check )
    {
        echo("ERROR: Impossible add comment of route with id : " . $comment_data['comment_post_ID'] . "\n" );
    }
    else
    {
        echo("SUCCESS: Added comment with id : $check \n");
    }
    return $check;
}
