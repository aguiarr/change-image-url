<?php
/*
 *Plugin Name: Change image URL
 *Description: This plugin changes the images listed for a new inserted image and delete the old images
 *Author: Matheus Aguiar
 *Author URI: https://github.com/aguiarr
 *Author Email: aguiartgv@gmail.com
 *Version: 1.0.1
 *License: GPLv3
 *Text Domain: change-image-url
 */


defined( 'ABSPATH' ) || exit;

if ( !function_exists( 'add_action' ) ) { exit; 
}
function add_management_page_change_image_url(){
  add_management_page("Change image URL", "Change image URL", "manage_options", basename(__FILE__), "change_image_url_management_page");
}

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


function urls(){
  try{

    $query = "SELECT meta_value FROM {$GLOBALS['wpdb']->prefix}postmeta WHERE meta_key= '_wp_attached_file' and meta_value like '%loteca%';";
    $urls = $GLOBALS['wpdb']->get_results($query);

    
    $str = '';
    foreach ($urls as $url) {
      $str .= $url->meta_value . ';';
    }
    return $str;

    
  }catch(Exception $e){
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('There were errors when fetching the thumbnail_id of some images.').'</p></strong></div>';
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

function change_image_url_management_page(){
  
  $erros = [];
  $success = [];



  if(isset( $_POST['btnSubmit']) ){

    if( isset( $_POST['imagemId'] )  && $_POST['imagemId'] != ''){
      $imageId = $_POST['imagemId'];
      check_id($imageId);
      
    }else{
      echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('The value entered in the "New Image ID" field is not valid').'</p></strong><p>'.__('Please enter a valid value.').'</p></div>';
    }

    if( isset( $_POST['listaImages'] ) && $_POST['listaImages'] != '' ){
      $imageList = $_POST['listaImages'];
    }else{
      echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('The value entered in the "Image List" field is not valid').'</p></strong><p>'.__('Please enter a valid value.').'</p></div>';
    }


    $images = check_array_url(get_images($imageList));


    foreach ($images as $image) {
      $thumb_id = get_post_id($image)[0]->post_id;
      

      if(update_thumb_id($thumb_id,$imageId, $image) == false){
        $erros[] = $image . " - UPDATE";
      }else{
        $success[] = $image . " - UPDATE";

        if(delete_thumbnail($thumb_id, $image) == false){
          $erros[] = $image . " - DELETE";
        }else{
          $success[] = $image . " - UPDATE";
        }
      }
    }
      if(!empty($erros))  echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('There was an error in any of the processes, please check the logs').'</p></strong></div>';
  }



  ?>
  <div>
    <h2 >Change Image URL</h2>
    <form method="post" action="tools.php?page=<?php echo basename(__FILE__);?>">
      <div>
        <strong>
          <?php _e('New Image ID'); ?>
        </strong><br>
        <input name="imagemId" type="text" id="imagemId" style="width:100px;font-size:20px;" >
        <br>
        <div>
          <strong>
            <label>Image List</label>
          </strong>
          <label style="color=gray">(list images separated by ";")</label>
          </br>
          <td><textarea name="listaImages" type="text" id="listaImages" placeholder="Ex: imagem-teste.png;imagem-teste2.jpg;imagem-teste3.jpeg;" style="width:650px;height:300px;font-size:15px;"></textarea></td>
        </div>
      </div>
      <br/>
      <input class="button-primary" style="margin-left: 550px;"name="btnSubmit" value="<?php _e('Alterar URLs'); ?>" type="submit" />
    </form>
    <div>
      <strong>
        <label>Successfully changed URLs:</label>
      </strong>
      </br>
      <td><textarea name="success" type="text" readonly id="success" style="width:650px;height:100px;font-size:15px;color:green;"><?php if(empty($erros)) echo ''; foreach($success as $succes){ echo $succes . "\n";}?></textarea></td>
    </div>
    <div>
      <strong>
        <label>Unchanged URLs:</label>
      </strong>
      </br>
      <td><textarea name="erros" type="text" readonly id="erros"  style="width:650px;height:100px;font-size:15px;"><?php if(empty($erros)) echo ''; foreach($erros as $erro){ echo $erro . "\n";}?></textarea></td>
    </div>
  </div>
  <?php
  
}
add_action('admin_menu', 'add_management_page_change_image_url');
?>
