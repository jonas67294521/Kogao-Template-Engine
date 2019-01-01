<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */

class Error_Index extends Database{

    var $template = "page/error/index";

    /**
     * @var string
     * @info Default Parameter
     */
    var $page_title         = "Kogao";
    var $page_keywords      = "kogao";
    var $page_description   = "Kogao";
    var $page_language      = "de";

    var $view, $settings, $mail, $options;
    var $language;
    var $event;

    public function __construct()
    {
        parent::__construct();

        $args = func_get_args();

        $this->view     = $args[0];
        $this->language = $args[1];
        $this->options  = $args[2];
        $this->event    = new Functions();

        $this->settings = [
            "title"             => $this->page_title,
            "version_code"      => version_code,
            "theme_color"       => theme_color,
            "meta_description"  => $this->page_description,
            "meta_keywords"     => $this->page_keywords,
            "html_language"     => $this->page_language,
            "facebook_app_id"   => facebook_app_id,
            "js_jRange"         => js_jrange,
            "js_jQuery"         => js_jQuery,
            "js_functions"      => js_functions,
            "internet_explorer" => $this->event->isInternetExplorer(),
            "is_login"          => _Cookie("login"),
            "is_login_field_1"  => _Cookie("login_1"),
            "is_login_field_2"  => _Cookie("login_2"),
            "is_login_field_3"  => _Cookie("login_3"),
        ];

    }

    public function onLoad(){

        $this->view->assign(
            $this->options
        );

    }

    public function onExecute(){

        $this->onLoad();

        $this->view->assign($this->settings);
        $this->view->assign($this->language);

        echo $this->view->draw($this->template, $return_string = true);

    }

}
