<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE html>
<html lang="en">
<head>

    <base href="<?php echo $base_dir;?>">

    <?php if( responsive_view == true ){ ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="theme-color" content="<?php echo $theme_color;?>" />
    <?php } ?>

    <meta charset="UTF-8">
    <title><?php echo $title;?></title>

    <meta name="description" content="<?php echo $meta_description;?>">
    <meta name="keywords" content="<?php echo $meta_keywords;?>">

    <meta http-equiv="content-Language" content="de" />
    <meta name="robots" content="index,follow" />

    <link rel="stylesheet" href="view.php?p=style.scss&v=<?php echo $version_code;?>" media="screen">
    <?php if( $js_jQuery ){ ?><script type="text/javascript" src="view/assets/js/jquery.js"></script><?php } ?>
    <?php if( $js_jRange ){ ?><script type="text/javascript" src="view/assets/js/jrange.js"></script><?php } ?>
    <?php if( $js_functions ){ ?><script type="text/javascript" src="view/assets/js/functions.js"></script><?php } ?>

    <!-- @bigpipe -->
    <script type="text/javascript" src="view/assets/js/bigpipe/index.js"></script>

</head>
<body>