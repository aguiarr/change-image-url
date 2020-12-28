<?php
/*
 *Plugin Name: Change image URL
 *Description: This plugin changes the images listed for a new inserted image and delete the old images
 *Author: Matheus Aguiar
 *Author URI: https://github.com/aguiarr
 *Author Email: aguiartgv@gmail.com
 *Version: 1.0.0
 *License: GPLv3
 *Text Domain: change-image-url
 */


defined( 'ABSPATH' ) || exit;

if ( !function_exists( 'add_action' ) ) { exit; 
}
function add_management_page_change_image_url(){
  add_management_page("Change image URL", "Change image URL", "manage_options", basename(__FILE__), "change_image_url_management_page");
}

//pega as images do textarea e transforma em array
function get_images($images){
  $imagesArray = explode(';', $images);

  return $imagesArray;
}

//retornar o thumb_id com base na url
function get_post_id($url_image){
  try{

    if(!$url_image || $url_image == '') return;
    $query = "SELECT post_id FROM {$GLOBALS['wpdb']->prefix}postmeta WHERE meta_value = '$url_image';";
    $post_id = $GLOBALS['wpdb']->get_results($query);

    return $post_id;

  }catch(Exception $e){
    $error++;
    array_push($erros_post_id, $url_image);
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('Houve erros ao buscar o post_id de algumas imagens').'</p></strong></div>';

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
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('Houve erros ao buscar o thumbnail_id de algumas imagens.').'</p></strong></div>';
  }
}

//deleta os posts das imagens 
function delete_post($post_id, $url){

  $result = $GLOBALS['wpdb']->delete($GLOBALS['wpdb']->prefix . "posts", array("ID" => "$post_id"));

  if(!$result){
    $error++;
    array_push($erros_post_id, $url);
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('Não foi possível deletar algumas das urls passadas da tabela '. $GLOBALS['wpdb']->prefix .'posts.').'</p></strong></div>';
  }
}

//deleta a thumbnail do post_meta
function delete_thumbnail($thum_id, $url){

  $result = $GLOBALS['wpdb']->delete($GLOBALS['wpdb']->prefix . "postmeta", array("post_id" => "$thum_id"));
  delete_post($thum_id, $url);

  return $result;
}

//altera o id da thumb
function update_thumb_id($old_id, $new_id, $url){

  if($old_id == null || $old_id == '') return;
  if($new_id == null || $new_id == '') return;

  $result = $GLOBALS['wpdb']->update($GLOBALS['wpdb']->prefix . "postmeta", array("meta_value" => "$new_id"), array("meta_value" => "$old_id", 'meta_key' => '_thumbnail_id'));

  return $result;
}

function check_array_url($images){
  $array = [];
  try{

    if($images[count($images)] == ''){
      array_pop($images);
    }
    foreach($images as $image){
      $item = trim($image);

      $array[] = $item;
    }

    return $array;

  }

  catch(Exeption $e){
    $error++;
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('As URLs das imagens foram inseridas no formato errado').'</p></strong><p>'.__('As URLs devem ser separadas por ";".').'</p></div>';
  }
   
}

function check_id($id){
  if(intval($id)){
    return true;
  }else{
    $error++;
    echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('O valor inserido no campo "ID da nova imagem" não é válido').'</p></strong><p>'.__('Para esse campo entre com um valor inteiro.').'</p></div>';
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
      $error++;
      echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('O valor inserido no campo "ID da nova imagem" não é válido').'</p></strong><p>'.__('Por favor entre com um valor válido.').'</p></div>';
    }

    if( isset( $_POST['listaImages'] ) && $_POST['listaImages'] != '' ){
      $imageList = $_POST['listaImages'];
    }else{
      $error++;
      echo '<div id="message" class="error"><p><strong>'.__('ERROR').' - '.__('O valor inserido no campo "Lista de Imagens" não é válido').'</p></strong><p>'.__('Por favor entre com um valor válido.').'</p></div>';
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
}

  ?>
  <div">
    <h2 >Change Image URL</h2>
    <form method="post" action="tools.php?page=<?php echo basename(__FILE__);?>">
      <div>
        <strong>
          <?php _e('ID da nova imagem'); ?>
        </strong><br>
        <input name="imagemId" type="text" id="imagemId" style="width:100px;font-size:20px;" >
        <br>
        <div>
          <strong>
            <label>Lista de Imagens</label>
          </strong>
          <label style="color=gray">(listar imagens separadas por ";")</label>
          </br>
          <td><textarea name="listaImages" type="text" id="listaImages" placeholder="Ex: imagem-teste.png;imagem-teste2.jpg;imagem-teste3.jpeg;" style="width:650px;height:300px;font-size:15px;"></textarea></td>
        </div>
      </div>
      <br/>
      <input class="button-primary" style="margin-left: 550px;"name="btnSubmit" value="<?php _e('Alterar URLs'); ?>" type="submit" />
    </form>
    <div>
      <strong>
        <label>URLs alteradas com sucesso:</label>
      </strong>
      </br>
      <td><textarea name="success" type="text" readonly id="success" style="width:650px;height:100px;font-size:15px;"><?php if(empty($erros)) echo ''; foreach($success as $succes){ echo $succes . "\n";}?></textarea></td>
    </div>
    <div>
      <strong>
        <label>URLs não alteradas:</label>
      </strong>
      </br>
      <td><textarea name="erros" type="text" readonly id="erros"  style="width:650px;height:100px;font-size:15px;"><?php if(empty($erros)) echo ''; foreach($erros as $erro){ echo $erro . "\n";}?></textarea></td>
    </div>
  </div>
  <?php
  
}
add_action('admin_menu', 'add_management_page_change_image_url');
?>
