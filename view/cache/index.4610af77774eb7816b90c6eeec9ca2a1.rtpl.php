<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl->assign( $this->var );$tpl->draw( "page/header" );?>

<div id="id_b068931cc450442b63f5b3d276ea4297"></div><script id="id_b068931cc450442b63f5b3d276ea4297_1">BigPipe.onArrive({"innerHTML":"","id":"id_b068931cc450442b63f5b3d276ea4297","css_files":[],"js_files":[],"js_code":"","is_last":true});</script>


<?php if( _Price('100') > 120 ){ ?>
    hallo
    <?php }elseif( 90 > 80 ){ ?>Dann eher das hier
    <?php }else{ ?>Nein
<?php } ?>

<?php $tpl = new RainTPL;$tpl->assign( $this->var );$tpl->draw( "page/footer" );?>