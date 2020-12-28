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

include_once 'functions.php';

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
          <td><textarea name="listaImages" type="text" id="listaImages" placeholder="Ex: image-test.png;image-test2.jpg;image-test3.jpeg;" style="width:650px;height:300px;font-size:15px;"></textarea></td>
        </div>
      </div>
      <br/>
      <input class="button-primary" style="margin-left: 550px;"name="btnSubmit" value="<?php _e('Change URLs'); ?>" type="submit" />
    </form>
    <div>
      <strong>
        <label>Successfully changed URLs:</label>
      </strong>
      </br>
      <td><textarea name="success" type="text" readonly id="success" style="width:650px;height:100px;font-size:12px;color:green;"><?php if(empty($success)) echo ''; foreach($success as $succes){ echo $succes . "\n";}?></textarea></td>
    </div>
    <div>
      <strong>
        <label>Unchanged URLs:</label>
      </strong>
      </br>
      <td><textarea name="erros" type="text" readonly id="erros"  style="width:650px;height:100px;font-size:12px;color:red;"><?php if(empty($erros)) echo ''; foreach($erros as $erro){ echo $erro . "\n";}?></textarea></td>
    </div>
  </div>
  <?php
  
}
add_action('admin_menu', 'add_management_page_change_image_url');
?>
