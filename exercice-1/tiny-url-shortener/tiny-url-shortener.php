<?php
/*
 * Plugin Name: Tiny URL Shortener
 * Plugin URI: 
 * Description: Minifie les urls.
 * Author: Maxime Moraine
 * Version: 1.0
 * Author URI: 
 */

function dummy_tiny_url_call($permalink) {
  return "https://dummytinyurl.com/" . $permalink;
}

// Hook à la sauvegarde d'un post
function tus_generate_tiny_url($post_id, $post_object, $is_update) {
  // On ne veut pas générer des urls pour les révisions
  if (!wp_is_post_revision($post_object)) {
    
    // On appelle le service TinyURL qui nous retourne l'url minifiée
    $permalink = get_permalink($post_id);
    $generate_tiny_url = true;
    
    // On vérifie que le permalien a changé avant de générer un nouveau lien minifié
    if ($is_update) {
      $meta_value = get_post_meta($post_id, "shortened_url", true);
      $decoded_meta_value = json_decode($meta_value);
      // Si une tiny url est associée à un permalien, on n'en génère pas de nouvelle
      if ($decoded_meta_value[0] == $permalink) {
        error_log("PAS DE NOUVELLE TINY URL");
        $generate_tiny_url = false;
      }
    }

    if ($generate_tiny_url) {
      $shortened_url = dummy_tiny_url_call($permalink);
    }

    error_log("DUMMY TINY URL : " . $shortened_url);

    // On sauvegarde le couple permalien et URL minifiée dans une post_meta
    $meta_value = wp_slash( wp_json_encode( [$permalink, $shortened_url] ) );
    update_post_meta($post_id, "shortened_url", $meta_value);
  }
}
add_action('save_post', 'tus_generate_tiny_url', 10, 3);


// Afficher l'url minifée dans l'interface d'édition
function shortened_url_form_meta_box($post) {
  $meta_value = get_post_meta($post->ID, 'shortened_url', true);
  if ($meta_value) {
    echo json_decode($meta_value)[1];
  } else {
    echo "Pas d'URL minifiée générée";
  }
}
function add_shortened_url_form_meta_box() {
  add_meta_box('shortened-url-form-meta-box-id', 'URL Minifiée', 'shortened_url_form_meta_box', 'shortened_url_form', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_shortened_url_form_meta_box');


// Afficher l'url minifée dans la liste des posts
function set_custom_edit_posts_columns($columns) {
  $columns['shortened_url'] = 'URL Minifiée';
  
  return $columns;
}
add_filter( 'manage_post_posts_columns', 'set_custom_edit_posts_columns' );

function show_shortened_url_posts_list($column_key, $post_id) {
  if ($column_key == 'shortened_url') {
		$meta_value = get_post_meta($post_id, 'shortened_url', true);
		if ($meta_value) {
			echo '<a href="'.json_decode($meta_value)[1].'" target="_blank">'.json_decode($meta_value)[1].'</a>';
		} else {
			echo '<span style="color:red;">'; "Pas d'URL minifiée générée"; echo '</span>';
		}
	}
}
add_action('manage_post_posts_custom_column', 'show_shortened_url_posts_list', 10, 2);