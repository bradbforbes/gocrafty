<?php
/*
Plugin Name: Go Crafty
Plugin URI: http://akismet.com/
Description: A slideshow plugin that actually works
Version: 1.0.0
Author: Brad Forbes
Author URI: http://
License: GPL 2.0
*/

global $wpdb;

/**
 * ------------------------------------------------------
 * Plugin Constants
 * ------------------------------------------------------
 *
 * Define all of our constants here, no exceptions.
 *
 * ------------------------------------------------------
 */
// The application path to our directory.
define('GOC_PATH', dirname(__FILE__) . '/');

// The absolute URL to our directory.
define('GOC_URL', plugins_url() . '/gocrafty/');

// Database table name containing our Slideshows.
define('GOC_TABLES_SLIDESHOWS', $wpdb->prefix . 'goc_slideshows');

// Database table name containing our Slides.
define('GOC_TABLES_SLIDES', $wpdb->prefix . 'goc_slides');



/**
 * ---------------------------------------------------------
 * File Requires
 * ---------------------------------------------------------
 *
 * If admin, go ahead and include all our files,
 * because most of the time, we'll need almost all of them.
 *
 * For public-facing pages, wait until the shortcode is
 * called to require what we need.
 *
 * ---------------------------------------------------------
 */
if (is_admin()) {
    require_once dirname( __FILE__ ) . '/models/admin-pages/AdminPage.php';
    require_once dirname( __FILE__ ) . '/models/admin-pages/ManageSlideshowAdminPage.php';
    require_once dirname( __FILE__ ) . '/models/admin-pages/ManageSlideshowOrderAdminPage.php';
    require_once dirname( __FILE__ ) . '/models/admin-pages/SlideshowsAdminPage.php';
    require_once dirname( __FILE__ ) . '/models/Slideshow.php';
    require_once dirname( __FILE__ ) . '/models/Slide.php';
}




/**
 * ---------------------------------------------------------
 * Admin Pages
 * ---------------------------------------------------------
 *
 * We have two Admin Pages:
 * 1) Slideshows
 * 2) Manage Slideshow
 *
 * ---------------------------------------------------------
 */
if (is_admin()) {
    $slideshowsPage = new SlideshowsAdminPage('Go Crafty', 'goc-slideshows', null, null, null, GOC_URL . 'assets/icon.png');
    add_action('admin_menu', array($slideshowsPage, 'activate'));
    add_action('init', array($slideshowsPage, 'process'));

    $manageSlideshowPage = new ManageSlideshowAdminPage('Manage Slideshow', 'goc-manage-slideshow', null, null, -1);
    add_action('admin_menu', array($manageSlideshowPage, 'activate'));
    add_action('init', array($manageSlideshowPage, 'process'));

    $manageSlideshowOrderPage = new ManageSlideshowOrderAdminPage('Manage Order', 'goc-manage-order', null, null, -1);
    add_action('admin_menu', array($manageSlideshowOrderPage, 'activate'));
    add_action('init', array($manageSlideshowOrderPage, 'process'));
}


/**
 * ------------------------------------------------------
 * Register assets
 * ------------------------------------------------------
 *
 * For admin panel, we have a admin.css and admin.js
 * to load into the queue.
 *
 * For public-facing pages, we only need to ensure
 * that jQuery is in the queue.
 *
 * ------------------------------------------------------
 */
// Define callback function to register all of our assets.
function goc_register_assets() {
    if (is_admin()) {
        // Register and enqueue CSS.
        wp_register_style('goc-admin', GOC_URL . 'assets/css/admin.css');
        wp_enqueue_style('goc-admin', GOC_URL . 'assets/css/admin.css');

        // Register and enqueue JS.
        wp_register_script('goc-admin', GOC_URL . 'assets/js/admin.js');
        wp_enqueue_script('goc-admin');
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-mouse' );
        wp_enqueue_script( 'jquery-ui-sortable' );
    } else {
        wp_enqueue_script('jquery');
    }
}

// Add hook to register assets at the appropriate time.
add_action('init', 'goc_register_assets');


/**
 * ------------------------------------------------------
 * Register Shortcode
 * ------------------------------------------------------
 *
 *
 *
 * ------------------------------------------------------
 */
/**
 * Displays a Crafty Slideshow, including of all the required
 * CSS and JS logic.
 *
 * @param $atts
 * @return bool
 */
function goc_display($atts){
    global $wpdb;

    // Require our classes.
    require_once dirname( __FILE__ ) . '/models/Slideshow.php';
    require_once dirname( __FILE__ ) . '/models/Slide.php';

    /**
     * ---------------------------------------------------------
     * Configure our Crafty options.
     * ---------------------------------------------------------
     *
     *
     *
     * ---------------------------------------------------------
     */
    $slideshowId = $atts['id'];
    (isset($atts['width'])) ? $width = $atts['width'] : $width = 600;
    (isset($atts['height'])) ? $height = $atts['height'] : $height = 350;
    (isset($atts['pagination']) && ($atts['pagination'] == 'true' || $atts['pagination'] == '1')) ? $pagination = 'true' : $pagination = 'false';
    (isset($atts['fadetime'])) ? $fadetime = $atts['fadetime'] : $fadetime = 550;
    (isset($atts['delay'])) ? $delay = $atts['delay'] : $delay = 5000;
    (isset($atts['border'])) ? $border = $atts['border'] : $border = 0;


    /**
     * ---------------------------------------------------------
     * Attempt to find a Slideshow record from provided ID.
     * ---------------------------------------------------------
     *
     * First, we attempt to find a matching integer ID.
     * Second, we attempt to find an exact Title match.
     * Third, we attempt to find a similar Title match.
     *
     * ---------------------------------------------------------
     */
    // Initialize search.
    $foundId = false;

    // First.
    $row = $wpdb->get_row("SELECT * FROM " . GOC_TABLES_SLIDESHOWS . " WHERE id='$slideshowId'");
    if ($row->id) {
        $foundId = $row->id;
    }

    // Second.
    if (!$foundId) {
        $row = $wpdb->get_row("SELECT * FROM " . GOC_TABLES_SLIDESHOWS . " WHERE title = '$slideshowId'");
        if ($row->id) {
            $foundId = $row->id;
        }
    }

    // Third.
    if (!$foundId) {
        $row = $wpdb->get_row("SELECT * FROM " . GOC_TABLES_SLIDESHOWS . " WHERE title = '%$slideshowId%'");
        if ($row->id) {
            $foundId = $row->id;
        }
    }

    // Do not display a Slideshow if no record could be found.
    if (!$foundId)
        return false;


    /**
     * ---------------------------------------------------------
     * Load our Slideshow
     * ---------------------------------------------------------
     *
     *
     *
     * ---------------------------------------------------------
     */
    $slideshow = new Slideshow($foundId);
    $slideshow->load();


    /**
     * ---------------------------------------------------------
     * Spit out Crafty.
     * ---------------------------------------------------------
     *
     *
     *
     * ---------------------------------------------------------
     */
    ?>
    <!-- Include Crafty CSS -->
    <style type="text/css">#goc-slideshow-<?php echo $slideshow->getId(); ?>{margin:0;padding:0;position:relative;border:<?php echo $border; ?>px solid #fff;-webkit-box-shadow:0 3px 5px #999;-moz-box-shadow:0 3px 5px #999;box-shadow:0 3px 5px #999}#goc-slideshow-<?php echo $slideshow->getId(); ?> ul{position:relative;overflow:hidden;margin:0;padding:0}#goc-slideshow-<?php echo $slideshow->getId(); ?> ul li{position:absolute;top:0;left:0;margin:0;padding:0;list-style:none}#pagination{clear:both;width:75px;margin:25px auto 0;padding:0}#pagination li{list-style:none;float:left;margin:0 2px}#pagination li a{display:block;width:10px;height:10px;text-indent:-10000px;background:url(<?php echo GOC_URL; ?>crafty/images/pagination.png)}#pagination li a.active{background-position:0 10px}.caption{display: none;width:100%;margin:0;padding:10px;position:absolute;left:0;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:lighter;color:#fff;border-top:1px solid #000;background:rgba(0,0,0,0.6)}</style>

    <!-- Include Crafty JS -->
    <script type="text/javascript">(function($){$.fn.craftyslide=function(options){var defaults={"width":600,"height":300,"pagination":true,"fadetime":350,"delay":5000};var options=$.extend(defaults,options);return this.each(function(){var $this=$(this);var $slides=$this.find("ul li");$slides.not(':first').hide();function paginate(){$this.append("<ol id='pagination' />");var i=1;$slides.each(function(){$(this).attr("id","slide"+i);$("#pagination").append("<li><a href='#slide"+i+"'>"+i+"</a></li>");i++;});$("#pagination li a:first").addClass("active");}function captions(){$slides.each(function(){$caption=$(this).find("img").attr("title");if($caption!==undefined){$(this).prepend("<p class='caption'>"+$caption+"</p>");}$slides.filter(":first").find(".caption").css("bottom",0);});}function manual(){var $pagination=$("#pagination li a");$pagination.click(function(e){e.preventDefault();var $current=$(this.hash);if($current.is(":hidden")){$slides.fadeOut(options.fadetime);$current.fadeIn(options.fadetime);$pagination.removeClass("active");$(this).addClass("active");$(".caption").css("bottom","-37px");$current.find(".caption").delay(300).animate({bottom:0},300);}});}function auto(){setInterval(function(){$slides.filter(":first-child").fadeOut(options.fadetime).next("li").fadeIn(options.fadetime).end().appendTo("#goc-slideshow-<?php echo $slideshow->getId(); ?> ul");$slides.each(function(){if($slides.is(":visible")){$(".caption").css("bottom","-37px");$(this).find(".caption").delay(300).animate({bottom:0},300);}});},options.delay);}$this.width(options.width);$this.find("ul, li img").width(options.width);$this.height(options.height);$this.find("ul, li").height(options.height);if(options.pagination===true){paginate();}else{auto();}captions();manual();});};})(jQuery);</script>

    <!-- Initialize Crafty -->
    <script>
        jQuery(document).ready(function($) {
            $("#goc-slideshow-<?php echo $slideshow->getId(); ?>").craftyslide({
                'width': <?php echo $width; ?>,
                'height': <?php echo $height; ?>,
                'pagination': <?php echo $pagination; ?>,
                'fadetime': <?php echo $fadetime; ?>,
                'delay': <?php echo $delay; ?>
            });
        });
    </script>

    <!-- Display Crafty -->
    <div id="goc-slideshow-<?php echo $slideshow->getId(); ?>">
        <ul><?php foreach ($slideshow->getSlides() as $slide): ?><?php $image = wp_get_attachment_image_src($slide->getAttachmentId(), 'large'); $src = $image[0]; ?>
            <li><img src="<?php echo $src; ?>" alt="" title="<?php echo get_the_title($slide->getAttachmentId()); ?>" style="max-width: 100%;" /></li>
        <?php endforeach; ?></ul>
    </div>
    <?php
}
add_shortcode( 'goc_display', 'goc_display' );


/**
 * ------------------------------------------------------
 * Activation logic
 * ------------------------------------------------------
 *
 * Create two new DB tables for this plugin.
 * 1) slideshows
 * 2) slides
 *
 * ------------------------------------------------------
 */
// Define activation callback.
function goc_install() {
    global $wpdb;

    // Require appropriate file containing dbDelta.
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Define structure of Slideshows table.
    $sql = "CREATE TABLE " . GOC_TABLES_SLIDESHOWS . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title VARCHAR(140) NOT NULL,
        UNIQUE KEY id (id)
    );";

    // Use dbDelta, as we should.
    dbDelta($sql);

    // Define structure of Slides table.
    $sql = "CREATE TABLE " . GOC_TABLES_SLIDES . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        attachment_id mediumint(9) NOT NULL,
        slideshow_id mediumint(9) NOT NULL,
        position mediumint(9) NOT NULL,
        UNIQUE KEY id (id)
    );";

    // Use dbDelta, as we should.
    dbDelta($sql);
}

// Register activation hook.
register_activation_hook(__FILE__, 'goc_install');


function trail_off($string, $length) {
    if (strlen($string) <= $length)
        return $string;

    $string = substr($string, 0, $length) . '...';
    return $string;
}



