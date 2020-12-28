<?php

//take the textarea images and transform it into an array
function get_images($images){
  $imagesArray = explode(';', $images);

  return $imagesArray;
}

//return thumb_id based on url
function get_post_id($url_image){

    if(!$url_image || $url_image == '') {
      echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('There was an error with the passed URLs. ').'</p></strong></div>';
    }
    $query = "SELECT post_id FROM {$GLOBALS['wpdb']->prefix}postmeta WHERE meta_value = '$url_image';";
    $post_id = $GLOBALS['wpdb']->get_results($query);

    if(empty($post_id)){ 
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('There were errors when fetching the post_id of some images').'</p></strong></div>';
    }else{
      return $post_id;
    }
}

//delete image posts
function delete_post($post_id, $url){
  $result = $GLOBALS['wpdb']->delete($GLOBALS['wpdb']->prefix . "posts", array("ID" => "$post_id"));
}

//delete the post_meta thumbnail
function delete_thumbnail($thum_id, $url){

  $result = $GLOBALS['wpdb']->delete($GLOBALS['wpdb']->prefix . "postmeta", array("post_id" => "$thum_id"));
  delete_post($thum_id, $url);

  return $result;
}

//change the thumb id
function update_thumb_id($old_id, $new_id, $url){

  if($old_id == null || $old_id == '') return;
  if($new_id == null || $new_id == '') return;

  $result = $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . "postmeta", array("meta_value" => "$new_id"), array("meta_value" => "$old_id", 'meta_key' => '_thumbnail_id'));

  return $result;
}

function check_array_url($images){
  $array = [];

    if($images[count($images)] == ''){
      array_pop($images);
    }
    foreach($images as $image){
      $item = trim($image);

      $array[] = $item;
    }
    if(empty($array)) {
      echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('Image URLs were entered in the wrong format').'</p></strong><p>'.__('URLs must be separated by ";".').'</p></div>';
    }
    else{
        return $array;
    }
}


function check_id($id){
  if(intval($id)){
    return true;
  }else{
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('The value entered in the "New image ID" field is not valid').'</p></strong><p>'.__('For this field, enter an integer value.').'</p></div>';
  }
}