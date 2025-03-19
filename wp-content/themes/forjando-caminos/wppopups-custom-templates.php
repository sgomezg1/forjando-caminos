/**
* Plugin Name: Plantilla popup quienes somos
* Plugin URI: https://wppopups.com
* Description: Plantilla para popups de informacion de Forjando Caminos Col
* Author: Sebastian Gomez
* Author URI: https://wppopups.com
* Version: 1.0.0
*/

<?
    $postId = get_the_ID();
    $postTitle = get_the_title();
    $postContent = get_the_content();
    $postImageUrl = get_the_post_thumbnail_url($postId, 'full')
?>
<div class="popup-informacion-forjando-caminos">
    <div class="imagen-popup-forjando-caminos">
        <img src = "<? echo $postImageUrl; ?>" alt="Forjando Caminos Imagen Popup">
    </div>
    <div class="contenido-popup-forjando-caminos">
        <h2 class = "titulo-popup-forjando-caminos"><? echo $postTitle; ?></h2>
        <? echo $postContent; ?>
    </div>
</div>
