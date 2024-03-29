<?php
/**
 * Copyright © 2011–2014 Federico Ulfo and a lot of awesome contributors
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */
require("controller/browser.php");
require("controller/bigpipe/index.inc");
require("controller/bigpipe/pagelet.inc");

class RainTPL{

    static $tpl_dir = "tpl/";
    static $cache_dir = "tmp/";
    static $base_url = null;
    static $tpl_ext = "html";
    static $path_replace = true;
    static $path_replace_list = array( 'a', 'img', 'link', 'script', 'input' );
    static $black_list = array( '\$this', 'raintpl::', 'self::', '_SESSION', '_SERVER', '_ENV',  'eval', 'exec', 'unlink', 'rmdir' );
    static $check_template_update = true;
    static $php_enabled = false;
    static $debug = false;
    static $root_dir = '';

    public $var = array();

    protected $tpl = array(),
        $cache = false,
        $cache_id = null;

    protected static $config_name_sum = array();

    const CACHE_EXPIRE_TIME = 3600;

    function assign( $variable, $value = null ){
        if( is_array( $variable ) )
            $this->var = $variable + $this->var;
        else
            $this->var[ $variable ] = $value;
    }

    function draw( $tpl_name, $return_string = false ){

        try {
            $this->check_template( $tpl_name );
        } catch (RainTpl_Exception $e) {
            $output = $this->printDebug($e);
            die($output);
        }

        if( !$this->cache && !$return_string ){
            extract( $this->var );
            include $this->tpl['compiled_filename'];
            unset( $this->tpl );
        }

        else{

            ob_start();
            extract( $this->var );
            include $this->tpl['compiled_filename'];
            $raintpl_contents = ob_get_clean();

            if( $this->cache )
                file_put_contents( $this->tpl['cache_filename'], "<?php if(!class_exists('raintpl')){exit;}?>" . $raintpl_contents );

            unset( $this->tpl );

            if( $return_string ) return $raintpl_contents; else echo $raintpl_contents;

        }

    }

    function cache( $tpl_name, $expire_time = self::CACHE_EXPIRE_TIME, $cache_id = null ){

        $this->cache_id = $cache_id;

        if( !$this->check_template( $tpl_name ) && file_exists( $this->tpl['cache_filename'] ) && ( time() - filemtime( $this->tpl['cache_filename'] ) < $expire_time ) ){
            return substr( file_get_contents( $this->tpl['cache_filename'] ), 43 );
        }
        else{
            if (file_exists($this->tpl['cache_filename']))
                unlink($this->tpl['cache_filename'] );
            $this->cache = true;
        }
    }

    static function configure( $setting, $value = null ){
        if( is_array( $setting ) )
            foreach( $setting as $key => $value )
                self::configure( $key, $value );
        else if( property_exists( __CLASS__, $setting ) ){
            self::$$setting = $value;
            self::$config_name_sum[ $setting ] = $value; // take trace of all config
        }
    }

    protected function check_template( $tpl_name ){

        if( !isset($this->tpl['checked']) ){

            $tpl_basename                       = basename( $tpl_name );														// template basename
            $tpl_basedir                        = strpos($tpl_name,"/") ? dirname($tpl_name) . '/' : null;						// template basedirectory
            $this->tpl['template_directory']    = self::$tpl_dir . $tpl_basedir;								// template directory
            $this->tpl['tpl_filename']          = self::$root_dir . $this->tpl['template_directory'] . $tpl_basename . '.' . self::$tpl_ext;    // template filename
            $temp_compiled_filename             = self::$root_dir . self::$cache_dir . $tpl_basename . "." . md5( $this->tpl['template_directory'] . serialize(self::$config_name_sum));
            $this->tpl['compiled_filename']     = $temp_compiled_filename . '.rtpl.php';	// cache filename
            $this->tpl['cache_filename']        = $temp_compiled_filename . '.s_' . $this->cache_id . '.rtpl.php';	// static cache filename
            $this->tpl['checked']               = true;

            // if the template doesn't exist and is not an external source throw an error
            if( self::$check_template_update && !file_exists( $this->tpl['tpl_filename'] ) && !preg_match('/http/', $tpl_name) ){
                $e = new RainTpl_NotFoundException( 'Template '. $tpl_basename .' not found!' );
                throw $e->setTemplateFile($this->tpl['tpl_filename']);
            }

            // We check if the template is not an external source
            if(preg_match('/http/', $tpl_name)){
                $this->compileFile('', '', $tpl_name, self::$root_dir . self::$cache_dir, $this->tpl['compiled_filename'] );
                return true;
            }
            // file doesn't exist, or the template was updated, Rain will compile the template
            elseif( !file_exists( $this->tpl['compiled_filename'] ) || ( self::$check_template_update && filemtime($this->tpl['compiled_filename']) < filemtime( $this->tpl['tpl_filename'] ) ) ){
                $this->compileFile( $tpl_basename, $tpl_basedir, $this->tpl['tpl_filename'], self::$root_dir . self::$cache_dir, $this->tpl['compiled_filename'] );
                return true;
            }

        }
    }


    /**
     * execute stripslaches() on the xml block. Invoqued by preg_replace_callback function below
     * @access protected
     */
    protected function xml_reSubstitution($capture) {
        return "<?php echo '<?xml ".stripslashes($capture[1])." ?>'; ?>";
    }

    /**
     * Compile and write the compiled template file
     * @access protected
     */
    protected function compileFile( $tpl_basename, $tpl_basedir, $tpl_filename, $cache_dir, $compiled_filename ){

        //read template file
        $this->tpl['source'] = $template_code = file_get_contents( $tpl_filename );

        //xml substitution
        $template_code = preg_replace( "/<\?xml(.*?)\?>/s", "##XML\\1XML##", $template_code );

        //disable php tag
        if( !self::$php_enabled )
            $template_code = str_replace( array("<?","?>"), array("&lt;?","?&gt;"), $template_code );

        //xml re-substitution
        $template_code = preg_replace_callback ( "/##XML(.*?)XML##/s", array($this, 'xml_reSubstitution'), $template_code );

        //compile template
        $template_compiled = "<?php if(!class_exists('raintpl')){exit;}?>" . $this->compileTemplate( $template_code, $tpl_basedir );


        // fix the php-eating-newline-after-closing-tag-problem
        $template_compiled = str_replace( "?>\n", "?>\n\n", $template_compiled );

        // create directories
        if( !is_dir( $cache_dir ) )
            mkdir( $cache_dir, 0755, true );

        if( !is_writable( $cache_dir ) )
            throw new RainTpl_Exception ('Cache directory ' . $cache_dir . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to false. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

        //write compiled file
        file_put_contents( $compiled_filename, $template_compiled );
    }


    /**
     * Compile template
     * @access protected
     */
     function compileTemplate( $template_code, $tpl_basedir ){

        //tag list
        $tag_regexp = [

            //BigPipe Loader
            'bigpipe'       => '(\<bigpipe(?: id){0,1}="[^"]*"(?: css="(\w*?)(?:.*?)"){0,1}(?: js="(\w*?)(?:.*?)"){0,1}(?: content="(\w*?)(?:.*?)"){0,1}\>)',
            'bigpipe_close' => '(\<\/bigpipe\>)',

            'loop'          => '(\<loop(?: name){0,1} id="\${0,1}[^"]*"\>)',
            'loop_close'    => '(\<\/loop\>)',
            //Debug Mode
            'debug'         => '(\<\debug\>)',
            'debug_end'     => '(\<\/debug\>)',

            //Functions
            'function'      => '(\<function call="[^"]*"\>)',
            'function_end'  => '(\<\/function\>)',
            'function'		=> '(\<function call="(\w*?)(?:.*?)"\>)',

            'number'        => '(\<number int="(\w*?)(?:.*?)"\>)',
            'number_end'    => '(\<\/number\>)',

            'date'          => '(\<date time="(\w*?)(?:.*?)"\>)',
            'date_end'      => '(\<\/date\>)',

            'if_'           => '(\<if(?: case){0,1}="[^"]*"\>)',
            'else_'         => '(\<else\>)',
            'else_c'        => '(\<\/else\>)',
            'elseif_c'      => '(\<\/elseif\>)',
            'elseif_'       => '(\<elseif(?: case){0,1}="[^"]*"\>)',
            'if_close_'     => '(\<\/if\>)',

            'break'	        => '(\{break\})',
            'if'            => '(\{if(?: condition){0,1}="[^"]*"\})',
            'elseif'        => '(\{elseif(?: condition){0,1}="[^"]*"\})',
            'else'          => '(\{else\})',
            'if_close'      => '(\{\/if\})',

            'noparse'       => '(\{noparse\})',
            'noparse_close' => '(\{\/noparse\})',
            'ignore'        => '(\{ignore\}|\{\*)',
            'ignore_close' 	=> '(\{\/ignore\}|\*\})',
            'include'       => '(\{include="[^"]*"(?: cache="[^"]*")?\})',

            'include_html'  => '(\<include(?: file){0,1}="[^"]*"(?: cache="[^"]*")?\>)',
            'include_end'   => '(\<\/include\>)',
        ];

        $tag_regexp = "/" . join( "|", $tag_regexp ) . "/";

        //path replace (src of img, background and href of link)
        $template_code = $this->path_replace( $template_code, $tpl_basedir );

        //split the code with the tags regexp
        $template_code = preg_split ( $tag_regexp, $template_code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

        //compile the code
        $compiled_code = $this->compileCode( $template_code );

        //return the compiled code
        return $compiled_code;

    }



    /**
     * Compile the code
     * @access protected
     */
    protected function compileCode( $parsed_code ){


        // if parsed code is empty return null string
        if( !$parsed_code )
            return "";

        //variables initialization
        $compiled_code = $open_if = $comment_is_open = $ignore_is_open = null;
        $loop_level = 0;


        //read all parsed code
        foreach( $parsed_code as $html ){

            //close ignore tag
            if( !$comment_is_open && ( strpos( $html, '{/ignore}' ) !== FALSE || strpos( $html, '*}' ) !== FALSE ) )
                $ignore_is_open = false;

            //code between tag ignore id deleted
            elseif( $ignore_is_open ){
                //ignore the code
            }

            //close no parse tag
            elseif( strpos( $html, '{/noparse}' ) !== FALSE )
                $comment_is_open = false;

            //code between tag noparse is not compiled
            elseif( $comment_is_open )
                $compiled_code .= $html;

            //ignore
            elseif( strpos( $html, '{ignore}' ) !== FALSE || strpos( $html, '{*' ) !== FALSE )
                $ignore_is_open = true;

            //noparse
            elseif( strpos( $html, '{noparse}' ) !== FALSE )
                $comment_is_open = true;

            //include tag
            elseif( preg_match( '/\{include="([^"]*)"(?: cache="([^"]*)"){0,1}\}/', $html, $code ) ){
                if (preg_match("/http/", $code[1])) {
                    $content = file_get_contents($code[1]);
                    $compiled_code .= $content;
                } else {
                    //variables substitution
                    $include_var = $this->var_replace( $code[ 1 ], $left_delimiter = null, $right_delimiter = null, $php_left_delimiter = '".' , $php_right_delimiter = '."', $loop_level );

                    //get the folder of the actual template
                    $actual_folder = substr( $this->tpl['template_directory'], strlen(self::$tpl_dir) );

                    //get the included template
                    $include_template = $actual_folder . $include_var;

                    // reduce the path
                    $include_template = $this->reduce_path( $include_template );

                    // if the cache is active
                    if( isset($code[ 2 ]) ){

                        //include
                        $compiled_code .= '<?php $tpl = new '.get_called_class().';' .
                            'if( $cache = $tpl->cache( "'.$include_template.'" ) )' .
                            '	echo $cache;' .
                            'else{' .
                            '$tpl->assign( $this->var );' .
                            ( !$loop_level ? null : '$tpl->assign( "key", $key'.$loop_level.' ); $tpl->assign( "value", $value'.$loop_level.' );' ).
                            '$tpl->draw( "'.$include_template.'" );'.
                            '}' .
                            '?>';

                    }
                    else{
                        //include
                        $compiled_code .= '<?php $tpl = new '.get_called_class().';' .
                            '$tpl->assign( $this->var );' .
                            ( !$loop_level ? null : '$tpl->assign( "key", $key'.$loop_level.' ); $tpl->assign( "value", $value'.$loop_level.' );' ).
                            '$tpl->draw( "'.$include_template.'" );'.
                            '?>';

                    }
                }
            }
            //included tag html
            elseif( preg_match( '/\<include(?: file){0,1}="([^"]*)"(?: cache="([^"]*)"){0,1}\>/', $html, $code ) ){
                if (preg_match("/http/", $code[1])) {
                    $content = file_get_contents($code[1]);
                    $compiled_code .= $content;
                } else {
                    //variables substitution
                    $include_var = $this->var_replace( $code[ 1 ], $left_delimiter = null, $right_delimiter = null, $php_left_delimiter = '".' , $php_right_delimiter = '."', $loop_level );

                    //get the folder of the actual template
                    $actual_folder = substr( $this->tpl['template_directory'], strlen(self::$tpl_dir) );

                    //get the included template
                    $include_template = $actual_folder . $include_var;

                    // reduce the path
                    $include_template = $this->reduce_path( $include_template );

                    // if the cache is active
                    if( isset($code[ 2 ]) ){

                        //include
                        $compiled_code .= '<?php $tpl = new '.get_called_class().';' .
                            'if( $cache = $tpl->cache( "'.$include_template.'" ) )' .
                            '	echo $cache;' .
                            'else{' .
                            '$tpl->assign( $this->var );' .
                            ( !$loop_level ? null : '$tpl->assign( "key", $key'.$loop_level.' ); $tpl->assign( "value", $value'.$loop_level.' );' ).
                            '$tpl->draw( "'.$include_template.'" );'.
                            '}' .
                            '?>';

                    }
                    else{
                        //include
                        $compiled_code .= '<?php $tpl = new '.get_called_class().';' .
                            '$tpl->assign( $this->var );' .
                            ( !$loop_level ? null : '$tpl->assign( "key", $key'.$loop_level.' ); $tpl->assign( "value", $value'.$loop_level.' );' ).
                            '$tpl->draw( "'.$include_template.'" );'.
                            '?>';

                    }
                }
            }
            //loop
            elseif( preg_match( '/\<loop(?: name){0,1} id="\${0,1}([^"]*)"\>/', $html, $code ) ){

                //increase the loop counter
                $loop_level++;

                //replace the variable in the loop
                $var = $this->var_replace( '$' . $code[ 1 ], $tag_left_delimiter=null, $tag_right_delimiter=null, $php_left_delimiter=null, $php_right_delimiter=null, $loop_level-1 );

                //loop variables
                $counter = "\$counter$loop_level";       // count iteration
                $key = "\$key$loop_level";               // key
                $value = "\$value$loop_level";           // value

                //loop code
                $compiled_code .=  "<?php $counter=-1; if( !is_null($var) && is_array($var) && sizeof($var) ) foreach( $var as $key => $value ){ $counter++; ?>";

            }

            // loop break
            elseif( strpos( $html, '{break}' ) !== FALSE ) {

                //else code
                $compiled_code .=   '<?php break; ?>';

            }

            //close loop tag
            elseif( strpos( $html, '</loop>' ) !== FALSE ) {

                //iterator
                $counter = "\$counter$loop_level";

                //decrease the loop counter
                $loop_level--;

                //close loop code
                $compiled_code .=  "<?php } ?>";

            }

            // BigPipe Start
            elseif( preg_match( '/\<bigpipe(?: id){0,1}="([^"]*)"(?: css="(.*?)"){0,1}(?: js="(.*?)"){0,1}(?: content="(.*?)"){0,1}\>/', $html, $code ) ){

                $tag    = $code[0];
                $id     = $code[1];
                $css    = $code[2];
                $js     = $code[3];
                $coder  = $code[4] ? $code[4] : $code[5];
                $this->function_check( $tag );

                $bigpipe = new Pagelet($id);
                if(!empty($css)){
                    $css_styles = explode(",", $css);
                    foreach($css_styles as $css_value){
                        $bigpipe->add_css($css_value);
                    }
                }

                if(!empty($js)){
                    $js_script = explode(",", $js);
                    foreach($js_script as $js_value){
                        $bigpipe->add_javascript($js_value);
                    }
                }

                $parsed_function = $this->var_replace( $coder, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = '<?php ', $php_right_delimiter = ';?>', $loop_level, $echo = true );

                $bigpipe->add_content($parsed_function);

                $compiled_code .= $bigpipe;
                $compiled_code .= BigPipe::render();

            }

            //if new
            elseif( preg_match( '/\<if(?: case){0,1}="([^"]*)"\>/', $html, $code ) ){

                $open_if++;
                $tag = $code[ 0 ];
                $condition = $code[ 1 ];
                $this->function_check( $tag );
                $parsed_condition = $this->var_replace( $condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level );

                $compiled_code .=   "<?php if( $parsed_condition ){ ?>";

            }

            //if
            elseif( preg_match( '/\{if(?: condition){0,1}="([^"]*)"\}/', $html, $code ) ){

                //increase open if counter (for intendation)
                $open_if++;

                //tag
                $tag = $code[ 0 ];

                //condition attribute
                $condition = $code[ 1 ];

                // check if there's any function disabled by black_list
                $this->function_check( $tag );

                //variable substitution into condition (no delimiter into the condition)
                $parsed_condition = $this->var_replace( $condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level );

                //if code
                $compiled_code .=   "<?php if( $parsed_condition ){ ?>";

            }

            //elseif New
            elseif( preg_match( '/\<elseif(?: case){0,1}="([^"]*)"\>/', $html, $code ) ){
                $tag = $code[ 0 ];
                $condition = $code[ 1 ];
                $parsed_condition = $this->var_replace( $condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level );
                $compiled_code .=   "<?php }elseif( $parsed_condition ){ ?>";
            }

            //elseif
            elseif( preg_match( '/\{elseif(?: condition){0,1}="([^"]*)"\}/', $html, $code ) ){
                $tag = $code[ 0 ];
                $condition = $code[ 1 ];
                $parsed_condition = $this->var_replace( $condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level );
                $compiled_code .=   "<?php }elseif( $parsed_condition ){ ?>";
            }

            //else > New Close Hidden
            elseif( strpos( $html, '</elseif>' ) !== FALSE ) {
                $compiled_code .=   '';
            }

            //else > New Close Hidden
            elseif( strpos( $html, '</else>' ) !== FALSE ) {
                $compiled_code .=   '';
            }

            //else > New
            elseif( strpos( $html, '<else>' ) !== FALSE ) {
                $compiled_code .=   '<?php }else{ ?>';
            }

            //else
            elseif( strpos( $html, '{else}' ) !== FALSE ) {
                $compiled_code .=   '<?php }else{ ?>';
            }

            //Close Tags
            elseif( strpos( $html, '</bigpipe>' ) !== FALSE ) {}
            elseif( strpos( $html, '</function>' ) !== FALSE ) {}
            elseif( strpos( $html, '</include>' ) !== FALSE ) {}

            //close if tag > new
            elseif( strpos( $html, '</if>' ) !== FALSE ) {
                $open_if--;
                $compiled_code .=   '<?php } ?>';
            }

            //close if tag
            elseif( strpos( $html, '{/if}' ) !== FALSE ) {
                $open_if--;
                $compiled_code .=   '<?php } ?>';
            }

            //Number
            elseif( preg_match( '/\<number int="(\w*)(.*?)"\>/', $html, $code ) ){

                if(!empty($code[2])){
                    $parsed_function = $this->var_replace( $code[ 2 ], $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level );
                }else{
                    $parsed_function = $code[1];
                }

                $compiled_code .= "<?php echo number_format($parsed_function,2,',','.'); ?>";

            }
            elseif( strpos( $html, '</number>' ) !== FALSE ) {
                $compiled_code .= "";
            }

            //Date
            elseif( preg_match( '/\<date time="(\w*)(.*?)"\>/', $html, $code ) ){

                $NumberFormat = date('d.m.Y', $code[1]);
                $compiled_code .= "<?php echo '$NumberFormat'; ?>";

            }
            elseif( strpos( $html, '</date>' ) !== FALSE ) {
                $compiled_code .= "";
            }

            //function
            elseif( preg_match( '/\<function call="(\w*)(.*?)"\>/', $html, $code ) ){

                //tag
                $tag = $code[ 0 ];

                //function
                $function = $code[ 1 ];

                // check if there's any function disabled by black_list
                $this->function_check( $tag );

                if( empty( $code[ 2 ] ) )
                    $parsed_function = $function . "()";
                else
                    // parse the function
                    $parsed_function = $function . $this->var_replace( $code[ 2 ], $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level );

                //if code
                $compiled_code .=   "<?php echo $parsed_function; ?>";
            }

            // show all vars
            elseif ( strpos( $html, '<debug>' ) !== FALSE ) {

                //tag
                $tag  = '<debug>';

                //if code
                $compiled_code .=   '<?php echo "<pre>"; print_r( $this->var ); echo "</pre>"; ?>';
            }
            elseif( strpos( $html, '</debug>' ) !== FALSE ) {
                $compiled_code .= "";
            }


            //all html code
            else{

                //variables substitution (es. {$title})
                $html = $this->var_replace( $html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = '<?php ', $php_right_delimiter = ';?>', $loop_level, $echo = true );
                //const substitution (es. {#CONST#})
                $html = $this->const_replace( $html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = '<?php ', $php_right_delimiter = ';?>', $loop_level, $echo = true );
                //functions substitution (es. {"string"|functions})
                $compiled_code .= $this->func_replace( $html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = '<?php ', $php_right_delimiter = ';?>', $loop_level, $echo = true );
            }
        }

        if( $open_if > 0 ) {
            $e = new RainTpl_SyntaxException('Error! You need to close an {if} tag in ' . $this->tpl['tpl_filename'] . ' template');
            throw $e->setTemplateFile($this->tpl['tpl_filename']);
        }
        return $compiled_code;
    }



    /**
     * Reduce a path, eg. www/library/../filepath//file => www/filepath/file
     * @param type $path
     * @return type
     */
    protected function reduce_path( $path ){
        $path = str_replace( "://", "@not_replace@", $path );
        $path = preg_replace( "#(/+)#", "/", $path );
        $path = preg_replace( "#(/\./+)#", "/", $path );
        $path = str_replace( "@not_replace@", "://", $path );

        while( preg_match( '#\.\./#', $path ) ){
            $path = preg_replace('#\w+/\.\./#', '', $path );
        }
        return $path;
    }



    /**
     * Replace URL according to the following rules:
     * http://url => http://url
     * url# => url
     * /url => base_dir/url
     * url => path/url (where path generally is base_url/template_dir)
     * (The last one is => base_dir/url for <a> href)
     *
     * @param string $url Url to rewrite.
     * @param string $tag Tag in which the url has been found.
     * @param string $path Path to prepend to relative URLs.
     * @return string rewritten url
     */
    protected function rewrite_url( $url, $tag, $path ) {
        // If we don't have to rewrite for this tag, do nothing.
        if( !in_array( $tag, self::$path_replace_list ) ) {
            return $url;
        }

        // Make protocol list. It is a little bit different for <a>.
        $protocol = 'http|https|ftp|file|apt|magnet';
        if ( $tag == 'a' ) {
            $protocol .= '|mailto|javascript';
        }

        // Regex for URLs that should not change (except the leading #)
        $no_change = "/(^($protocol)\:)|(#$)/i";
        if ( preg_match( $no_change, $url ) ) {
            return rtrim( $url, '#' );
        }

        // Regex for URLs that need only base url (and not template dir)
        $base_only = '/^\//';
        if ( $tag == 'a' or $tag == 'form' ) {
            $base_only = '//';
        }
        if ( preg_match( $base_only, $url ) ) {
            return rtrim( self::$base_url, '/' ) . '/' . ltrim( $url, '/' );
        }

        // Other URLs
        return $path . $url;
    }



    /**
     * replace one single path corresponding to a given match in the `path_replace` regex.
     * This function has no reason to be used anywhere but in `path_replace`.
     * @see path_replace
     *
     * @param array $matches
     * @return replacement string
     */
    protected function single_path_replace ( $matches ){
        $tag  = $matches[1];
        $_    = $matches[2];
        $attr = $matches[3];
        $url  = $matches[4];
        $new_url = $this->rewrite_url( $url, $tag, $this->path );

        return "<$tag$_$attr=\"$new_url\"";
    }



    /**
     * replace the path of image src, link href and a href.
     * @see rewrite_url for more information about how paths are replaced.
     *
     * @param string $html
     * @return string html sostituito
     */
    protected function path_replace( $html, $tpl_basedir ){

        if( self::$path_replace ){

            $tpl_dir = self::$base_url . self::$tpl_dir . $tpl_basedir;

            // Prepare reduced path not to compute it for each link
            $this->path = $this->reduce_path( $tpl_dir );

            $url = '(?:(?:\\{.*?\\})?[^{}]*?)*?'; // allow " inside {} for cases in which url contains {function="foo()"}

            $exp = array();

            $tags = array_intersect( array( "link", "a" ), self::$path_replace_list );
            $exp[] = '/<(' . join( '|', $tags ) . ')(.*?)(href)="(' . $url . ')"/i';

            $tags = array_intersect( array( "img", "script", "input" ), self::$path_replace_list );
            $exp[] = '/<(' . join( '|', $tags ) . ')(.*?)(src)="(' . $url . ')"/i';

            $tags = array_intersect( array( "form" ), self::$path_replace_list );
            $exp[] = '/<(' . join( '|', $tags ) . ')(.*?)(action)="(' . $url . ')"/i';

            return preg_replace_callback( $exp, 'self::single_path_replace', $html );

        }
        else
            return $html;

    }



    // replace const
    function const_replace( $html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null ){
        // const
        return preg_replace( '/\{\#(\w+)\#{0,1}\}/', $php_left_delimiter . ( $echo ? " echo " : null ) . '\\1' . $php_right_delimiter, $html );
    }



    // replace functions/modifiers on constants and strings
    function func_replace( $html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null ){

        preg_match_all( '/' . '\{\#{0,1}(\"{0,1}.*?\"{0,1})(\|\w.*?)\#{0,1}\}' . '/', $html, $matches );

        for( $i=0, $n=count($matches[0]); $i<$n; $i++ ){

            //complete tag ex: {$news.title|substr:0,100}
            $tag = $matches[ 0 ][ $i ];

            //variable name ex: news.title
            $var = $matches[ 1 ][ $i ];

            //function and parameters associate to the variable ex: substr:0,100
            $extra_var = $matches[ 2 ][ $i ];

            // check if there's any function disabled by black_list
            $this->function_check( $tag );

            $extra_var = $this->var_replace( $extra_var, null, null, null, null, $loop_level );


            // check if there's an operator = in the variable tags, if there's this is an initialization so it will not output any value
            $is_init_variable = preg_match( "/^(\s*?)\=[^=](.*?)$/", $extra_var );

            //function associate to variable
            $function_var = ( $extra_var and $extra_var[0] == '|') ? substr( $extra_var, 1 ) : null;

            //variable path split array (ex. $news.title o $news[title]) or object (ex. $news->title)
            $temp = preg_split( "/\.|\[|\-\>/", $var );

            //variable name
            $var_name = $temp[ 0 ];

            //variable path
            $variable_path = substr( $var, strlen( $var_name ) );

            //parentesis transform [ e ] in [" e in "]
            $variable_path = str_replace( '[', '["', $variable_path );
            $variable_path = str_replace( ']', '"]', $variable_path );

            //transform .$variable in ["$variable"]
            $variable_path = preg_replace('/\.\$(\w+)/', '["$\\1"]', $variable_path );

            //transform [variable] in ["variable"]
            $variable_path = preg_replace('/\.(\w+)/', '["\\1"]', $variable_path );

            //if there's a function
            if( $function_var ){

                // check if there's a function or a static method and separate, function by parameters
                $function_var = str_replace("::", "@double_dot@", $function_var );

                // get the position of the first :
                if( $dot_position = strpos( $function_var, ":" ) ){

                    // get the function and the parameters
                    $function = substr( $function_var, 0, $dot_position );
                    $params = substr( $function_var, $dot_position+1 );

                }
                else{

                    //get the function
                    $function = str_replace( "@double_dot@", "::", $function_var );
                    $params = null;

                }

                // replace back the @double_dot@ with ::
                $function = str_replace( "@double_dot@", "::", $function );
                $params = str_replace( "@double_dot@", "::", $params );


            }
            else
                $function = $params = null;

            $php_var = $var_name . $variable_path;

            // compile the variable for php
            if( isset( $function ) ){
                if( $php_var )
                    $php_var = $php_left_delimiter . ( !$is_init_variable && $echo ? 'echo ' : null ) . ( $params ? "( $function( $php_var, $params ) )" : "$function( $php_var )" ) . $php_right_delimiter;
                else
                    $php_var = $php_left_delimiter . ( !$is_init_variable && $echo ? 'echo ' : null ) . ( $params ? "( $function( $params ) )" : "$function()" ) . $php_right_delimiter;
            }
            else
                $php_var = $php_left_delimiter . ( !$is_init_variable && $echo ? 'echo ' : null ) . $php_var . $extra_var . $php_right_delimiter;

            $html = str_replace( $tag, $php_var, $html );

        }

        return $html;

    }



    function var_replace( $html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null ){

        //all variables
        if( preg_match_all( '/' . $tag_left_delimiter . '\$(\w+(?:\.\${0,1}[A-Za-z0-9_]+)*(?:(?:\[\${0,1}[A-Za-z0-9_]+\])|(?:\-\>\${0,1}[A-Za-z0-9_]+))*)(.*?)' . $tag_right_delimiter . '/', $html, $matches ) ){

            for( $parsed=array(), $i=0, $n=count($matches[0]); $i<$n; $i++ )
                $parsed[$matches[0][$i]] = array('var'=>$matches[1][$i],'extra_var'=>$matches[2][$i]);

            foreach( $parsed as $tag => $array ){

                //variable name ex: news.title
                $var = $array['var'];

                //function and parameters associate to the variable ex: substr:0,100
                $extra_var = $array['extra_var'];

                // check if there's any function disabled by black_list
                $this->function_check( $tag );

                $extra_var = $this->var_replace( $extra_var, null, null, null, null, $loop_level );

                // check if there's an operator = in the variable tags, if there's this is an initialization so it will not output any value
                $is_init_variable = preg_match( "/^[a-z_A-Z\.\[\](\-\>)]*=[^=]*$/", $extra_var );

                //function associate to variable
                $function_var = ( $extra_var and $extra_var[0] == '|') ? substr( $extra_var, 1 ) : null;

                //variable path split array (ex. $news.title o $news[title]) or object (ex. $news->title)
                $temp = preg_split( "/\.|\[|\-\>/", $var );

                //variable name
                $var_name = $temp[ 0 ];

                //variable path
                $variable_path = substr( $var, strlen( $var_name ) );

                //parentesis transform [ e ] in [" e in "]
                $variable_path = str_replace( '[', '["', $variable_path );
                $variable_path = str_replace( ']', '"]', $variable_path );

                //transform .$variable in ["$variable"] and .variable in ["variable"]
                $variable_path = preg_replace('/\.(\${0,1}\w+)/', '["\\1"]', $variable_path );

                // if is an assignment also assign the variable to $this->var['value']
                if( $is_init_variable )
                    $extra_var = "=\$this->var['{$var_name}']{$variable_path}" . $extra_var;



                //if there's a function
                if( $function_var ){

                    // check if there's a function or a static method and separate, function by parameters
                    $function_var = str_replace("::", "@double_dot@", $function_var );


                    // get the position of the first :
                    if( $dot_position = strpos( $function_var, ":" ) ){

                        // get the function and the parameters
                        $function = substr( $function_var, 0, $dot_position );
                        $params = substr( $function_var, $dot_position+1 );

                    }
                    else{

                        //get the function
                        $function = str_replace( "@double_dot@", "::", $function_var );
                        $params = null;

                    }

                    // replace back the @double_dot@ with ::
                    $function = str_replace( "@double_dot@", "::", $function );
                    $params = str_replace( "@double_dot@", "::", $params );
                }
                else
                    $function = $params = null;

                //if it is inside a loop
                if( $loop_level ){
                    //verify the variable name
                    if( $var_name == 'key' )
                        $php_var = '$key' . $loop_level;
                    elseif( $var_name == 'value' )
                        $php_var = '$value' . $loop_level . $variable_path;
                    elseif( $var_name == 'counter' )
                        $php_var = '$counter' . $loop_level;
                    else
                        $php_var = '$' . $var_name . $variable_path;
                }else
                    $php_var = '$' . $var_name . $variable_path;

                // compile the variable for php
                if( isset( $function ) )
                    $php_var = $php_left_delimiter . ( !$is_init_variable && $echo ? 'echo ' : null ) . ( $params ? "( $function( $php_var, $params ) )" : "$function( $php_var )" ) . $php_right_delimiter;
                else
                    $php_var = $php_left_delimiter . ( !$is_init_variable && $echo ? 'echo ' : null ) . $php_var . $extra_var . $php_right_delimiter;

                $html = str_replace( $tag, $php_var, $html );


            }
        }

        return $html;
    }



    /**
     * Check if function is in black list (sandbox)
     *
     * @param string $code
     * @param string $tag
     */
    protected function function_check( $code ){

        $preg = '#(\W|\s)' . implode( '(\W|\s)|(\W|\s)', self::$black_list ) . '(\W|\s)#';

        // check if the function is in the black list (or not in white list)
        if( count(self::$black_list) && preg_match( $preg, $code, $match ) ){

            // find the line of the error
            $line = 0;
            $rows=explode("\n",$this->tpl['source']);
            while( !strpos($rows[$line],$code) )
                $line++;

            // stop the execution of the script
            $e = new RainTpl_SyntaxException('Unallowed syntax in ' . $this->tpl['tpl_filename'] . ' template');
            throw $e->setTemplateFile($this->tpl['tpl_filename'])
                ->setTag($code)
                ->setTemplateLine($line);
        }

    }

    protected function printDebug(RainTpl_Exception $e){
        if (!self::$debug) {
            throw $e;
        }
        $output = sprintf('<h2>Exception: %s</h2><h3>%s</h3><p>template: %s</p>',
            get_class($e),
            $e->getMessage(),
            $e->getTemplateFile()
        );
        if ($e instanceof RainTpl_SyntaxException) {
            if (null != $e->getTemplateLine()) {
                $output .= '<p>line: ' . $e->getTemplateLine() . '</p>';
            }
            if (null != $e->getTag()) {
                $output .= '<p>in tag: ' . htmlspecialchars($e->getTag()) . '</p>';
            }
            if (null != $e->getTemplateLine() && null != $e->getTag()) {
                $rows=explode("\n",  htmlspecialchars($this->tpl['source']));
                $rows[$e->getTemplateLine()] = '<font color=red>' . $rows[$e->getTemplateLine()] . '</font>';
                $output .= '<h3>template code</h3>' . implode('<br />', $rows) . '</pre>';
            }
        }
        $output .= sprintf('<h3>trace</h3><p>In %s on line %d</p><pre>%s</pre>',
            $e->getFile(), $e->getLine(),
            nl2br(htmlspecialchars($e->getTraceAsString()))
        );
        return $output;
    }
}


/**
 * Basic Rain tpl exception.
 */
class RainTpl_Exception extends Exception{
    /**
     * Path of template file with error.
     */
    protected $templateFile = '';

    /**
     * Returns path of template file with error.
     *
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * Sets path of template file with error.
     *
     * @param string $templateFile
     * @return RainTpl_Exception
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = (string) $templateFile;
        return $this;
    }
}

/**
 * Exception thrown when template file does not exists.
 */
class RainTpl_NotFoundException extends RainTpl_Exception{
}

/**
 * Exception thrown when syntax error occurs.
 */
class RainTpl_SyntaxException extends RainTpl_Exception{
    /**
     * Line in template file where error has occured.
     *
     * @var int | null
     */
    protected $templateLine = null;

    /**
     * Tag which caused an error.
     *
     * @var string | null
     */
    protected $tag = null;

    /**
     * Returns line in template file where error has occured
     * or null if line is not defined.
     *
     * @return int | null
     */
    public function getTemplateLine()
    {
        return $this->templateLine;
    }

    /**
     * Sets  line in template file where error has occured.
     *
     * @param int $templateLine
     * @return RainTpl_SyntaxException
     */
    public function setTemplateLine($templateLine)
    {
        $this->templateLine = (int) $templateLine;
        return $this;
    }

    /**
     * Returns tag which caused an error.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Sets tag which caused an error.
     *
     * @param string $tag
     * @return RainTpl_SyntaxException
     */
    public function setTag($tag)
    {
        $this->tag = (string) $tag;
        return $this;
    }
}

// -- end
