<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE html>
<html lang="en">
<head>

    <base href="<?php  echo base_href;?>">

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

    <?php if( isset($facebook_app_id) ){ ?><script>var facebook_api_id = "<?php echo $facebook_app_id;?>";</script><?php }else{ ?><script>var facebook_app_id = "";</script><?php } ?>

    <?php if( $js_functions == 1 ){ ?>
    <script type="text/javascript" src="view/assets/dist/core.js"></script>
        <?php }else{ ?>
            <?php if( $js_jQuery ){ ?><script type="text/javascript" src="view/assets/js/jquery.js"></script><?php } ?>
            <?php if( $js_jRange ){ ?><script type="text/javascript" src="view/assets/js/jrange.js"></script><?php } ?>
            <?php if( $js_functions ){ ?><script type="text/javascript" src="view/assets/js/functions.js"></script><?php } ?>
        
    <?php } ?>

    <script type="text/javascript" src="view/assets/js/bigpipe/index.js"></script>

</head>
<body>

<?php if( $internet_explorer == true ){ ?>
    <div class="InternetExplorerLayout">
        <div class="InternetExplorerLayoutInner">
            <span><img src="view/assets/img/ie_support.png"></span>
            <span>
                <b>Bitte beachten Sie, dass <?php echo ucfirst($meta_keywords); ?> Internet Explorer nicht mehr unterstützt.</b><br>
                Wir empfehlen ein Upgrade auf die neuesten Versionen von <a href="https://www.microsoft.com/en-us/windows/microsoft-edge" target="_blank">Microsoft Edge</a>, <a href="https://chrome.google.com/" target="_blank">Google Chrome</a> oder <a href="https://mozilla.org/firefox/" target="_blank">Firefox</a>.
            </span>
            <span><a href="supported-browsers"><div class="Button">Mehr dazu</div></a></span>
            <span><div class="Button">Ignorieren</div></span>
        </div>
    </div>
<?php } ?>