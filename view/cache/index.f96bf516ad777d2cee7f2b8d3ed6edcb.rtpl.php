<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl->assign( $this->var );$tpl->draw( "page/header" );?>
<div id="id_1718f873eb56a277b7ad944b0b076259"></div><script id="id_1718f873eb56a277b7ad944b0b076259_1">BigPipe.onArrive({"innerHTML":"","id":"id_1718f873eb56a277b7ad944b0b076259","css_files":["view.php?p=browser.scss&v=1"],"js_files":[],"js_code":"","is_last":true});</script>


<div class="BrowserLayout">
    <div class="BrowserLayoutInner">
        <div class="BrowserLayoutInnerTitle">Unterstützte Browser</div>
        <div class="BrowserLayoutInnerContent">
            Wir entwerfen <?php echo ucfirst($meta_keywords); ?>, um die neuesten Webbrowser zu unterstützen. Wir unterstützen die aktuellen Versionen von Chrome, Firefox, Safari und Microsoft Edge.
            <br><br>
            <a href="https://caniuse.com/#compare=ie+11,edge+18,firefox+64,chrome+71,safari+12" target="_blank">Internet Explorer vs. (Chrome/Firefox/Safari/Edge)</a>
        </div>
        <div class="BrowserLayoutInnerTitle">BigPipe</div>
        <div class="BrowserLayoutInnerContent">
            BigPipe: Für Webseiten mit hoher Leistungsperformence<br>
            <a href="https://de-de.facebook.com/notes/facebook-engineering/bigpipe-pipelining-web-pages-for-high-performance/389414033919/" target="_blank">https://de-de.facebook.com/notes/facebook-engineering/bigpipe-pipelining-web-pages-for-high-performance/389414033919/</a>
            <br><br>
            Wir verwenden mitunter BigPipe, um unserem Produkt die maximale Effizienz und Leistung an Performance zu bieten.
        </div>
        <div class="BrowserLayoutInnerTitle">HTML5 & CSS3</div>
        <div class="BrowserLayoutInnerContent">
            Wir verwenden die neusten HTML5/CSS3 Standard's, diese werden zum größten Teil/ oder teils garnicht vom Internet Explorer unterstützt.<br><br>
            <a href="https://www.heise.de/newsticker/meldung/Kommentar-zum-Internet-Explorer-Ein-Gespenst-geht-um-im-World-Wide-Web-4229968.html" target="_blank">https://www.heise.de/newsticker/meldung/Kommentar-zum-Internet-Explorer-Ein-Gespenst-geht-um-im-World-Wide-Web-4229968.html</a>
        </div>
        <div class="BrowserLayoutInnerContent">
            <a href="https://mozilla.org/firefox/" target="_blank"><img src="view/assets/img/icons8_Firefox_48px.png"></a>
            <a href="https://chrome.google.com/" target="_blank"><img src="view/assets/img/icons8_Chrome_48px.png"></a>
            <a href="https://support.apple.com/de-de/HT204416" target="_blank"><img src="view/assets/img/icons8_Safari_48px.png"></a>
            <a href="https://www.microsoft.com/en-us/windows/microsoft-edge" target="_blank"><img src="view/assets/img/icons8_Microsoft_Edge_48px.png"></a>
        </div>
    </div>
</div>

<?php $tpl = new RainTPL;$tpl->assign( $this->var );$tpl->draw( "page/footer" );?>