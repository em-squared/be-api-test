<?php 
// à placer à la racine
require_once('wp-load.php');
$path = 'generated.json';
$json_string = file_get_contents($path);
$json_data = json_decode($json_string, true);
// var_dump($json_data);
foreach ($json_data as $key => $value) {
  $post = array(
    'post_title' => $value['name'],
    'post_content' => $value['content'],
    'post_status' => 'publish',
    'post_author' => 1,
    'post_type' => 'post'
  );
     
  $post_id = wp_insert_post( $post );

  $wp_upload_dir = wp_upload_dir();

  $filename = $value['picture'];

  
  $attachment = array(
    'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
    'post_mime_type' => $filetype['type'],
    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
    'post_content'   => '',
    'post_status'    => 'inherit'
  );

  $attachment_id = wp_insert_attachment($attachment, $filename, $post_id);

  set_post_thumbnail( $parent_post_id, $attachment_id );
}