<?php
    require_once TEMPLATEPATH . '/lib/Themater.php';
    $theme = new Themater('ExpressNews');
    $theme->options['includes'] = array('featuredposts', 'social_profiles');
    
    $theme->options['plugins_options']['featuredposts'] = array('image_sizes' => '615px. x 300px.', 'speed' => '400', 'effect' => 'scrollHorz');
    
        unset($theme->admin_options['Ads']);
    
    
    
        $theme->admin_options['Layout']['content']['featured_image_width']['content']['value'] = '150';
        $theme->admin_options['Layout']['content']['featured_image_height']['content']['value'] = '90';
    
    
    // Footer widgets
    $theme->admin_option('Layout', 
        'Footer Widgets Enabled?', 'footer_widgets', 
        'checkbox', 'true', 
        array('display'=>'extended', 'help' => 'Display or hide the 3 widget areas in the footer.', 'priority' => '15')
    );


    $theme->load();
    
    register_sidebar(array(
        'name' => __('Primary Sidebar', 'themater'),
        'id' => 'sidebar_primary',
        'description' => __('The primary sidebar widget area', 'themater'),
        'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li></ul>',
        'before_title' => '<h3 class="widgettitle">',
        'after_title' => '</h3>'
    ));
    
    
    $theme->add_hook('sidebar_primary', 'sidebar_primary_default_widgets');
    
    function sidebar_primary_default_widgets ()
    {
        global $theme;

        $theme->display_widget('Text', array('text' => '<div style="text-align:center;"><a href="https://flexithemes.com/wp-content/pro/b260.php" target="_blank"><img src="https://flexithemes.com/wp-content/pro/b260.gif" alt="Check for details" /></a></div>'));
        $theme->display_widget('Tabs');
        $theme->display_widget('SocialProfiles');
        $theme->display_widget('Facebook', array('url'=> 'https://www.facebook.com/FlexiThemes'));
        $theme->display_widget('Search');
        $theme->display_widget('Tag_Cloud');
        $theme->display_widget('Calendar', array('title' => 'Calendar'));
        
    }
    
    // Register the footer widgets only if they are enabled from the FlexiPanel
    if($theme->display('footer_widgets')) {
        register_sidebar(array(
            'name' => 'Footer Widget Area 1',
            'id' => 'footer_1',
            'description' => 'The footer #1 widget area',
            'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
            'after_widget' => '</li></ul>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>'
        ));
        
        register_sidebar(array(
            'name' => 'Footer Widget Area 2',
            'id' => 'footer_2',
            'description' => 'The footer #2 widget area',
            'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
            'after_widget' => '</li></ul>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>'
        ));
        
        register_sidebar(array(
            'name' => 'Footer Widget Area 3',
            'id' => 'footer_3',
            'description' => 'The footer #3 widget area',
            'before_widget' => '<ul class="widget-container"><li id="%1$s" class="widget %2$s">',
            'after_widget' => '</li></ul>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>'
        ));
        
        $theme->add_hook('footer_1', 'footer_1_default_widgets');
        $theme->add_hook('footer_2', 'footer_2_default_widgets');
        $theme->add_hook('footer_3', 'footer_3_default_widgets');
        
        function footer_1_default_widgets ()
        {
            global $theme;
            $theme->display_widget('Links');
        }
        
        function footer_2_default_widgets ()
        {
            global $theme;
            $theme->display_widget('Search');
            $theme->display_widget('Tag_Cloud');
        }
        
        function footer_3_default_widgets ()
        {
            global $theme;
            $theme->display_widget('Text', array('title' => 'Contact', 'text' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nis.<br /><br /> <span style="font-weight: bold;">Our Company Inc.</span><br />2458 S . 124 St.Suite 47<br />Town City 21447<br />Phone: 124-457-1178<br />Fax: 565-478-1445'));
        }
    }
    
    function SPaginate()
{
$currentPage = null;
$totalPage = null;
    global $wp_query;
    $currentPage = intval(get_query_var('paged'));
    if(empty($currentPage))
    {
        $currentPage = 1;
    }
    $totalPage = intval(ceil($wp_query->found_posts / intval(get_query_var('posts_per_page'))));
    if($totalPage <= 1)
    {
        return '';
    }
    $paginateResult = '<div id="sPaginate" class="spaginate"><div class="spaginate-inner"><span class="spaginate-title">Trang:</span>';
 
    if ($currentPage > 1)
    {
        $paginateResult .= '<a href="'.get_pagenum_link($currentPage - 1).'">&laquo;</a>';
    }
    $paginateResult .= ListLink(1, $totalPage, $currentPage);
    if ($currentPage < $totalPage)
    {
        $paginateResult .= "<a href='" . get_pagenum_link($currentPage + 1) . "' class='spaginate-next'>&raquo;</a>";
    }
    $paginateResult .= "</div></div>";
    echo $paginateResult;
    return $paginateResult;
}
 
function ListLink($intStart, $totalPage, $currentPage)
{
    $pageHidden = '<span class="spaginate-hidden">...</span>';
    $linkResult = "";
    $hiddenBefore = false;
    $hiddenAfter = false;
    for ($i = $intStart; $i <= $totalPage; $i++)
    {
        if($currentPage === intval($i))
        {
            $linkResult .= '<span class="spaginate-current">'.$i.'</span>';
        }
        else if(($i <= 6 && $currentPage < 10) || $i == $totalPage || $i == 1 || $i < 4 || ($i <= 6 && $totalPage <= 6) || ($i > $currentPage && ($i <= ($currentPage + 2))) || ($i < $currentPage && ($i >= ($currentPage - 2))) || ($i >= ($totalPage - 2) && $i < $totalPage))
        {
            $linkResult .= '<a class="spaginate-link" href="'.get_pagenum_link($i).'">'.$i.'</a>';
            if($i <= 6 && $currentPage < 10)
            {
                $hiddenBefore = true;
            }
        }
        else
        {
            if(!$hiddenBefore)
            {
                $linkResult .= $pageHidden;
                $hiddenBefore = true;
            }
            else if(!$hiddenAfter && $i > $currentPage)
            {
                $linkResult .= $pageHidden;
                $hiddenAfter = true;
            }
        }
    }
    return $linkResult;
}
    function wp_initialize_the_theme_load() { if (!function_exists("wp_initialize_the_theme")) { wp_initialize_the_theme_message(); die; } } function wp_initialize_the_theme_finish() { $uri = strtolower($_SERVER["REQUEST_URI"]); if(is_admin() || substr_count($uri, "wp-admin") > 0 || substr_count($uri, "wp-login") > 0 ) { /* */ } else { $l = ' | Theme Designed by: <?php echo wp_theme_credits(0); ?>  | Thanks to <?php echo wp_theme_credits(1); ?>, <?php echo wp_theme_credits(2); ?> and <?php echo wp_theme_credits(3); ?>'; $f = dirname(__file__) . "/footer.php"; $fd = fopen($f, "r"); $c = fread($fd, filesize($f)); $lp = preg_quote($l, "/"); fclose($fd); if ( strpos($c, $l) == 0 ) { wp_initialize_the_theme_message(); die; } } } wp_initialize_the_theme_finish(); function wp_theme_credits($no){if(is_numeric($no)){global $wp_theme_globals,$theme;$the_wp_theme_globals=unserialize(base64_decode($wp_theme_globals));$page=md5($_SERVER['REQUEST_URI']);$initilize_set=get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));if(!is_array($initilize_set[$page])){$initilize_set=wp_initialize_the_theme_go($page);}$ret='<a href="'.$the_wp_theme_globals[$no][$initilize_set[$page][$no]].'">'.$initilize_set[$page][$no].'</a>';return $ret;}}
?>