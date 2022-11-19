<?php

/* -------------- 
 * DEFAULT SETUPS 
 * -------------- */

function r_inspect($val) {
    echo '<pre class="pre pre_red">';
    print_r($val);
    echo '</pre>';
}

function v_inspect($val) {
    echo '<pre class="pre pre_red">';
    var_dump($val);
    echo '</pre>';
}

function get_what_page() {
    global $post;
    echo print_r('<span class="athena">' . get_page($post->id)->post_type . ' <a href="' . get_bloginfo("url") . '/wp-admin/post.php?post=' . get_the_ID($post->id)  . '&action=edit">wp-admin edit</a>' . ' ' . get_the_ID($post->id) ) . '</span>';
} 
add_action('wp_head', 'get_what_page' );

/* ------------ 
 * GLOBAL HOOKS 
 * ------------ */

/* Function to remove version numbers 
 * ---------------------------------- */

function sdt_remove_ver_css_js( $src ) {
    if ( strpos($src, 'ver=') ) {
        $src = remove_query_arg( 'ver', $src );
        return $src;
    }
} 
add_filter( 'style_loader_src', 'sdt_remove_ver_css_js', 9999 ); // Remove WP Version From Styles
add_filter( 'script_loader_src', 'sdt_remove_ver_css_js', 9999 ); // Remove WP Version From Scripts

/* Body Class Props 
 * ---------------- */

function add_acf_body_class( $classes ) {
    global $post;
    if ( is_singular( 'page' ) && class_exists('ACF') ) {
        $custom_body_class = get_field( 'custom_body_class', $post->ID );
        if ( $custom_body_class ) {
            $classes[] = $custom_body_class;
        }
    }
    return $classes;
} 
add_filter('body_class', 'add_acf_body_class');

function add_slug_body_class( $classes ) {
    global $post;
    if ( isset( $post ) ) {
        $classes[] = $post->post_type . '__' . $post->post_name;
    }
    if (is_page()) {
        if ($post->post_parent) {
            $parent  = end(get_post_ancestors($current_page_id));
        } 
        else {
            $parent = $post->ID;
        }
        $post_data = get_post($parent, ARRAY_A);
        $classes[] = 'page__parent-' . $post_data['post_name'];
    }
    return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );

/* Dashboard 
 * --------- */

function create_dashboard() {
    include_once( 'custom_dashboard.php'  );
}

function register_menu() {
    add_dashboard_page( 'Dilate Digital', 'Dilate Digital', 'read', 'custom-dashboard', 'create_dashboard' );
} 
add_action('admin_menu', 'register_menu' );

function redirect_dashboard() {
    if( is_admin() ) {
        $screen = get_current_screen();
        if( $screen->base == 'dashboard' ) {
            wp_redirect( admin_url( 'index.php?page=custom-dashboard' ) );
        }
    }
} 
add_action('load-index.php', 'redirect_dashboard' );

function custom_login_logo() {
    echo '<style type="text/css"> .login div#login h1 a { background-image: url(' . get_stylesheet_directory_uri() .'/images/logo_admin.png) !important; width: 100% !important; height: 95px !important; background-size: auto !important; } </style>';
    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
    echo '<script type="text/javascript"> jQuery(document).ready(function() { jQuery(".login div#login h1 a").attr("href","http://www.dilate.com.au").attr("title","Maintained by Dilate Digital"); }); </script>';
}
add_action('login_head', 'custom_login_logo');

/* Excerpt  
 * ------- */

function get_excerpt_ex($limit, $getpostid) {
    $excerpt  = get_the_excerpt($getpostid);
    $clippedExcerpt = substr($excerpt, 0, $limit);
    $excerptLen = mb_strlen($excerpt);
    $trimmedExcerpt = ($excerptLen > $getpostid) ? $clippedExcerpt."..." : $excerpt;
    return $excerpt;
}

/* Settings Setup 
 * -------------- */

function remove_update_nags() {
    if (!current_user_can( 'administrator' )) {
        echo '<style>.update-nag, .updated, .error { display: none !important; }</style>';
    }
}   
add_action('admin_enqueue_scripts', 'remove_update_nags');
add_action('login_enqueue_scripts', 'remove_update_nags');

function update_default_time() {
    update_option( 'gmt_offset', '+8' );
    update_option( 'timezone_string', '' );
} 
add_action( 'after_switch_theme', 'update_default_time' );

function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

function my_deregister_scripts() {
    wp_dequeue_script( 'wp-embed' );
} 
add_action( 'wp_footer', 'my_deregister_scripts' );

function add_script_handle( $tag, $handle, $src ) {
    return str_replace( '<script', sprintf( '<script data-handle="%1$s"', esc_attr( $handle )), $tag );
} 
add_filter( 'script_loader_tag', 'add_script_handle', 10, 3 );

function add_defer_attribute($tag, $handle) {
    $scripts_to_defer = array('dd-childscript');
    foreach($scripts_to_defer as $defer_script) {
        if ($defer_script === $handle) {
            return str_replace(' src', ' defer="defer" src', $tag);
        }
    }
    return $tag;
}
add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);

function remove_open_sans() {
    remove_action( 'wp_enqueue_scripts', 'wpforge_google_fonts', 0);
}
add_action( 'after_setup_theme', 'remove_open_sans', 9 );

function remove_menus() {
    remove_menu_page( 'edit.php?post_type=project' );
    remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'remove_menus' );

function remove_wp_version_rss() {
    return ''; 
} 
add_filter('the_generator','remove_wp_version_rss');

remove_action('wp_head', 'wp_generator');

function new_gravatar ($avatar_defaults) {
    $myavatar = get_stylesheet_directory_uri() . '/images/avatar.png';
    $avatar_defaults[$myavatar] = "Default Gravatar";
    return $avatar_defaults;
}
add_filter( 'avatar_defaults', 'new_gravatar' );

remove_action( 'wp_enqueue_scripts', 'et_divi_replace_stylesheet', 99999998 ); // disable_cptdivi

add_filter('wp_lazy_loading_enabled', '__return_false');

/* Regenerate Thumbnails 
 * --------------------- */

add_image_size( 'prime_hero_img', 489, 334);
add_image_size( 'anchored_image', 491, 319);

add_image_size( 'partners_thumb', 150, 84.38);
add_image_size( 'timeline_img', 800, 433);

/* ------------- 
 * ENQUE SCRIPTS 
 * ------------- */

function child_style_script() {

    /* CSS 
    ---------- */

    wp_dequeue_style( 'magnific-popup' );
    wp_dequeue_style( 'dashicons' );
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'divi-style' );
    wp_dequeue_style( 'forminator-icons' );
    wp_dequeue_style( 'forminator-utilities' );

    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); // WP Child Theme Dafaults

    wp_register_style('dd-childstyle', get_stylesheet_directory_uri() . '/style.css', array(), '', false); 
    wp_enqueue_style('dd-childstyle');

    /* JS 
    ---------- */

	if( is_single() ) {
		wp_register_script('jquery-ui', get_stylesheet_directory_uri()  . '/assets/js/accordion/jquery-ui.js', array('jquery'), '1.10.2', true );
		wp_enqueue_script('jquery-ui');
        wp_register_script('jquery', get_stylesheet_directory_uri() . '/assets/js/accordion/jquery.min.js', array('jquery'), '1.9.1', true );
        wp_enqueue_script('jquery');
	}

    if( is_page('contact-us') ) {
        $map_api = get_field('google_map_api_key', 'option');
        wp_enqueue_script('googlemaps', esc_url( add_query_arg( 'key', $map_api, 'https://maps.googleapis.com/maps/api/js' )), array('jquery'), '', true );
    }

    wp_register_script('hubspot', get_stylesheet_directory_uri() . '/assets/js/hubspot.js', array('jquery'), '', true);
    wp_enqueue_script('hubspot');

    wp_register_script('dd-childscript', get_stylesheet_directory_uri() . '/script.js', array('jquery'), '', true);
    global $wp_query;
    wp_localize_script( 'dd-childscript', 'loadmoreProductListing', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', 
        'ltp_posts' => json_encode( $wp_query->query_vars ),
        'posts_count' => $wp_query->post_count,
        'ltp_current_found_posts' => $wp_query->found_posts,
        'ltp_current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
        'ltp_max_page' => $wp_query->max_num_pages,
        'ltp_termid' => get_queried_object_id(),
        'ltp_termid_name' => get_term( get_queried_object_id() )->name
    ) );
    wp_enqueue_script('dd-childscript');

    wp_deregister_script( 'magnific-popup' );

}
add_action( 'wp_enqueue_scripts', 'child_style_script');

function deque_script() {
    if(is_front_page()) {
        wp_dequeue_script('googlemaps');
    }
}
add_action( 'wp_enqueue_scripts', 'deque_script', 999);

/* ------------ 
 * CUSTOM POSTS 
 * ------------ */

$args = array(
    'labels' => array(
        'name'               => 'Products',
        'singular_name'      => 'Product',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Product',
        'edit_item'          => 'Edit Product',
        'new_item'           => 'New Product',
        'all_items'          => 'All Products',
        'view_item'          => 'View Product',
        'search_items'       => 'Search Products',
        'not_found'          => 'No testimonial found',
        'not_found_in_trash' => 'No Product found in Trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'Products'
    ),
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'industry' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => true,
    'menu_position'      => null,
    'supports'           => array( 'title', 'thumbnail', 'editor', 'excerpt' )
); 
register_post_type( 'industry', $args );

$labels = array(
    'name' => _x( 'Products', 'taxonomy general name' ),
    'singular_name' => _x( 'Product', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Products' ),
    'popular_items' => __( 'Popular Products' ),
    'all_items' => __( 'All Products' ),
    'parent_item' => __( 'Parent Product' ),
    'parent_item_colon' => __( 'Parent Product:' ),
    'edit_item' => __( 'Edit Product' ),
    'update_item' => __( 'Update Product' ),
    'add_new_item' => __( 'Add New Product' ),
    'new_item_name' => __( 'New Product Name' ),
    'menu_name' => __( 'Product Category' )
);

register_taxonomy('industry_product',array('industry'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'industry-cat' )
));

/* ----------------- 
 * CUSTOM SHORTCODES 
 * ----------------- */

/* Common Data from Options Page 
 * ----------------------------- */

function common_data($atts) {
    $name     = $atts['name'];
    $phone      = get_field('phone', 'option');
    $twitter    = get_field('twitter', 'option');
    $linkedin   = get_field('linkedin', 'option');
    $fb         = get_field('facebook', 'option');
    $gplus      = get_field('google_plus', 'option');
    $yt       = get_field('youtube', 'option');
    $pinterest  = get_field('pinterest', 'option');
    $insta    = get_field('instagram', 'option');
    if($name == "phone") {
        return $phone;
    }
    if($name == "twitter") {
        return $twitter;
    }
    if($name == "linkedin") {
        return $linkedin;
    }
    if($name == "facebook") {
        return $fb;
    }
    if($name == "google") {
        return $gplus;
    }
    if($name == "youtube") {
        return $yt;
    }
    if($name == "pinterest") {
        return $pinterest;
    }
    if($name == "instagram") {
        return $insta;
    }
} 
add_shortcode('cdata', 'common_data');

function sociallinks() {
    $fb         = get_field('facebook', 'option');
    $twitter    = get_field('twitter', 'option');
    $linkedin   = get_field('linkedin', 'option');
    $gplus      = get_field('google_plus', 'option');
    $yt       = get_field('youtube', 'option');
    $pinterest  = get_field('pinterest', 'option');
    $insta    = get_field('instagram', 'option');

    ob_start(); ?>
  <ul class="social-networks">
    <?php if( !empty($fb) ) { ?>
      <li><a href="<?=$fb?>"><span class="fa fa-facebook"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="19" viewBox="0 0 10 19"><path id="cls-fb" class="cls-1" d="M1421.42,7254.76h2.57v-3.62h-3.02v0.01c-3.65.13-4.4,2.09-4.47,4.15h-0.01v1.8h-2.48v3.54h2.48v9.5h3.76v-9.5h3.07l0.59-3.54h-3.66v-1.09A1.185,1.185,0,0,1,1421.42,7254.76Z" transform="translate(-1414 -7251.13)"/></svg></span></a></li>
    <?php } ?>

    <?php if( !empty($twitter) ) { ?>
      <li><a href="<?=$twitter?>"><span class="fa fa-twitter"></span></a></li>
    <?php } ?>

    <?php if( !empty($linkedin) ) { ?>
      <li><a href="<?=$linkedin?>"><span class="fa fa-linkedin"><svg xmlns="http://www.w3.org/2000/svg" width="15.56" height="13.94" viewBox="0 0 15.56 13.94"><path id="cls-linkedin" class="cls-1" d="M1533.98,7253.66a1.493,1.493,0,0,0-.55,1.15,1.555,1.555,0,0,0,.56,1.2,1.953,1.953,0,0,0,1.34.5,1.917,1.917,0,0,0,1.31-.49,1.486,1.486,0,0,0,.55-1.17,1.506,1.506,0,0,0-.55-1.18,1.937,1.937,0,0,0-1.33-.49A1.959,1.959,0,0,0,1533.98,7253.66Zm-0.15,13.47h2.95v-9.74h-2.95v9.74Zm4.75,0h2.95v-3.62a9.818,9.818,0,0,1,.13-2.07,2.645,2.645,0,0,1,.88-1.44,2.36,2.36,0,0,1,1.54-.53,2.031,2.031,0,0,1,1.16.32,1.621,1.621,0,0,1,.65.91,9.739,9.739,0,0,1,.19,2.44v3.99H1549v-6.27a3.345,3.345,0,0,0-1.03-2.67,4.336,4.336,0,0,0-2.96-1.06,5.048,5.048,0,0,0-1.67.28,7.332,7.332,0,0,0-1.81,1.03v-1.05h-2.95v9.74Z" transform="translate(-1533.44 -7253.19)"/></svg></span></a></li>
    <?php } ?>

    <?php if( !empty($gplus) ) { ?>
      <li><a href="<?=$gplus?>"><span class="fa fa-google"></span></a></li>
    <?php } ?>

    <?php if( !empty($insta) ) { ?>
      <li><a href="<?=$insta?>"><span class="fa fa-instagram"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path id="cls-instagram" data-name="Forma 1" class="cls-1" d="M1483.31,7252.13h-7.62a4.688,4.688,0,0,0-4.68,4.69v7.62a4.688,4.688,0,0,0,4.68,4.69h7.62a4.7,4.7,0,0,0,4.69-4.69v-7.62A4.7,4.7,0,0,0,1483.31,7252.13Zm3.18,12.31a3.19,3.19,0,0,1-3.18,3.19h-7.62a3.19,3.19,0,0,1-3.18-3.19v-7.62a3.182,3.182,0,0,1,3.18-3.18h7.62a3.182,3.182,0,0,1,3.18,3.18v7.62h0Zm-6.99-8.19a4.38,4.38,0,1,0,4.38,4.38A4.385,4.385,0,0,0,1479.5,7256.25Zm0,7.25a2.87,2.87,0,1,1,2.87-2.87A2.87,2.87,0,0,1,1479.5,7263.5Zm4.56-8.53a1.14,1.14,0,0,0-.78.32,1.123,1.123,0,0,0,0,1.57,1.14,1.14,0,0,0,.78.32,1.122,1.122,0,0,0,.78-0.32,1.1,1.1,0,0,0,0-1.57A1.122,1.122,0,0,0,1484.06,7254.97Z" transform="translate(-1471 -7252.13)"/></svg></span></a></li>
    <?php } ?>

    <?php if( !empty($yt) ) { ?>
      <li><a href="<?=$yt?>"><span class="fa fa-youtube"></span></a></li>
    <?php } ?>

    <?php if( !empty($pinterest) ) { ?>
      <li><a href="<?=$pinterest?>"><span class="fa fa-pinterest"></span></a></li>
    <?php } ?>
    </ul> <?php 
    return ob_get_clean();
}
add_shortcode('social-links', 'sociallinks');

/* Dilate Shortcode 
 * ---------------- */

function dilate_link_shortcode() {
    if( is_front_page() ){
        $output = '<a href="https://www.dilate.com.au/" target="_blank" rel="noopener noreferrer">Dilate Digital</a>';
    } else {
        $output = 'Dilate Digital';
    } 
    return $output;
} 
add_shortcode('dilate_link', 'dilate_link_shortcode');

/* Shortcode to show the module 
 * ---------------------------- */

function showmodule_shortcode( $moduleid ) {
    extract(shortcode_atts(array('id' =>'*'),$moduleid)); 
    return do_shortcode('[et_pb_section global_module="'.$id.'"][/et_pb_section]');
}
add_shortcode('showmodule', 'showmodule_shortcode');

/* --------------- 
 * PAGE SHORTCODES 
 * --------------- */

/* Commons 
 * ------- */

function the_blog_func() {
    global $post;
    $args = array( 
        'post_type' =>  'post', 
        'order' =>  'DESC', 
        'orderby' =>  'menu_order', 
        'category__in' => 1, 
        'posts_per_page' =>  3 
    );
    $query = new WP_Query($args);
    ob_start();
    if($query->have_posts()) { ?>
        <div class="blog_loop_box-main"> <?php
        while ($query->have_posts()) { 
            $query->the_post();
            $perm = get_the_permalink();
            $title = get_the_title();
            $date = get_the_date( 'F d, Y' );
            $get_single_post_term = get_the_terms( $post->ID, 'category' );
            if($get_single_post_term[1]->parent === 1) {
                $get_tag_term_name = $get_single_post_term[1]->name;
            }
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'medium'); } ?>
                <div class="blog_loop_box-main-item">
                <div class="blog_loop_box-main-item-img-con"> <img src="<?= $featured_img; ?>" alt="<?= $title; ?>"> </div>
                <div class="blog_loop_box-main-item-date"> <?= $date; ?> </div>
                <div class="blog_loop_box-main-item-title"><a href="<?= $perm; ?>"><?= $title; ?></a></div>
                <div class="blog_loop_box-main-item-desc"><?= get_excerpt_ex(300, $post->ID); ?></div>
                <div class="blog_loop_box-main-item-btn"><a class="et_pb_button" href="<?= $perm; ?>">Read more</a></div>
                </div> <?php
        } ?>
            </div>
            <a href="/blog" class="cta_btn">View all blogs</a> <?php 
    } else { ?>
        <p>Sorry, there are no posts to display</p> <?php
    }
    wp_reset_postdata();
    return ob_get_clean();
    die();
}
add_shortcode('the_blog', 'the_blog_func');

/* Header Navigation 
 * ----------------- */

function sub_menu_industry_products_feat_func($atts) {
    $atts = shortcode_atts( array( 'feat_img_slug' => ''), $atts );
    $term_id = (int)$atts["feat_img_slug"];
    $term_img_url = get_field('industry_sub_cat_page_thumb_image', 'industry_product_' . $term_id);
    $term_link = get_term_link($term_id);
    $term_name = get_term_by( 'id', $term_id, 'industry_product' )->name;
    $output;
    $output = '<div class="menu__products_thumb_img">';
    $output .= '<img src="' . $term_img_url . '"/>';
    $output .= '<div class="text">' . $term_name . '</div>';
    $output .= '<a class="btn_lnk et_pb_button" href="' . $term_link . '">View Products</a>';
    $output .= '</div>';
    return $output;
} 
add_shortcode('sub_menu_industry_products_feat', 'sub_menu_industry_products_feat_func');

function sub_menu_industry_products_func( $atts ) {
    $atts = shortcode_atts( array( 'category_slug' => ''), $atts );
    $args = array(
        'post_type' => 'industry',
        'posts_per_page'    =>  -1,
        'order'             =>  'DESC',
        'orderby'           =>  'menu_order',
        'hide_empty'        => true,                       
        'childless'         => false,
        'tax_query' => array(
            array(
                'taxonomy' => 'industry_product',
                'field' => 'slug',
                'terms' => $atts["category_slug"]
            )
        )
    );
    $query = new WP_Query($args);
    if($query->have_posts()) {
        global $post;
        $output;
        $output .= '<div class="menu__products">';
        while ($query->have_posts()) { 
            $query->the_post();
            $output .= '<div class="menu__products__wrap">';
            $output .= '<div class="menu__products__wrap-title" data-mob_hook_title="'. get_the_title() .'"><a href="'. get_the_permalink() .'">' . get_the_title() . '</a></div>';
            $output .= '<div class="menu__products__wrap-excerpt">' .  get_excerpt_ex( 43, 'content', get_the_ID() ) . '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';
    } else {
        $output .= '<p>Sorry, there are no products to display</p>';
    }
    wp_reset_postdata();
    return $output;
} 
add_shortcode('sub_menu_industry_products', 'sub_menu_industry_products_func');

function walker_nav_menu_start_el($item_output, $item) {
    if($item->classes[0] === 'has-short') {
        $item_output = do_shortcode($item->description); 
    } 
    return $item_output;
} 
add_filter('walker_nav_menu_start_el', 'walker_nav_menu_start_el', 20, 2);

/* Home  
 * ---- */

function home_partners_func() {
    $home_partners_images = get_field('home_partners_images', 'option');
    $output;
    $output .= '<div class="partners_loop_box-main">';
    foreach($home_partners_images as $home_partners_image) {
        $partner_img = wp_get_attachment_image_url($home_partners_image['image'], 'partners_thumb');
        $output .= '<div class="partners_loop_box-main-item">';
        $output .= '<img class="partners_loop_box-main-item-image" src="'.$partner_img.'"/>';
        $output .= '</div>';
    }
    $output .= '</div>';
    return $output;
} 
add_shortcode('home_partners', 'home_partners_func');

function home_industries_product_listing_func() {
    global $post;
    $args = array(
        'post_type' => 'industry',
        'posts_per_page' => 6,
        'order' => 'DESC',
        'orderby' => 'menu_order',
        'post_status' => 'publish'
    );
    $query = new WP_Query($args);
    if($query->have_posts()) {
        $output;
        $output .= '<div class="loop_box-wrapper-main">';
        while ($query->have_posts()) { 
            $query->the_post();
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'full'); } 
            $output .= '<div class="loop_box-wrapper-main-item">';
            $output .= '<a href="' . get_the_permalink() . '" class="loop_box-wrapper-main-item-absolute_btn"></a>';
            $output .= '<div class="loop_box-wrapper-main-item-img"><a href="' . get_the_permalink() . '"><img src="'.$featured_img .'" alt="' . get_the_title() . '";></a></div>';
            $output .= '<div class="loop_box-wrapper-main-item-box">';
            $output .= '<div class="loop_box-wrapper-main-item-box-text">' . get_the_title() . '</div>';
            $output .= '<a class="et_pb_button loop_box-wrapper-main-item-box-btn" href="' . get_the_permalink() . '"></a>';
            $output .= '</div>';
            $output .= '</div>';
        }
        global $wp_query;
        $output .= '</div>';
    } else {
        $output .= '<p>Sorry, there are no courses to display</p>';
    }
    wp_reset_postdata();
    return $output;
} 
add_shortcode('home_industries_product_listing', 'home_industries_product_listing_func');

function home_search_func() {
    ob_start(); ?>
    <form id="searchform" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <input type="text" class="search-field" name="s" placeholder="Search" value="<?php echo get_search_query(); ?>">
        <button type="submit" value="Search" disabled></button>
    </form> <?php
    return ob_get_clean();
}
add_shortcode('home_search', 'home_search_func');

/* Hero Slider 
 * ----------- */

function home_slider_func() {
    $image_siders = get_field('image_sider', 'option');
    $output;
    $output .= '<div class="slider__sec-wrapper img_anchor">';
    foreach($image_siders as $image_sider) {
        $anchored_image = wp_get_attachment_image_url($image_sider['anchored_image'], 'anchored_image');
        $output .= '<div class="slider__sec-wrapper-item">';
        $output .= '<img class="slider__sec-wrapper-item-img" src="' . $anchored_image . '">';
        $output .= '<div class="slider__sec-wrapper-item-tag"><p>' . $image_sider['image_price_tag'] . '</p></div>';
        $output .= '</div>';
    }
    $output .= '</div>';
    $output .= '<div class="slider__sec-wrapper img_prim">';
    foreach($image_siders as $image_sider) {
        $primary_image = wp_get_attachment_image_url($image_sider['primary_image'], 'prime_hero_img');
        $output .= '<div class="slider__sec-wrapper-item">';
        $output .= '<img class="slider__sec-wrapper-item-img" src="' . $primary_image . '">';
        $output .= '</div>';
    }
    $output .= '</div>';
    return $output;
} 
add_shortcode('home_slider', 'home_slider_func');

/* Labels
 * ------ */

/* Labels Sub Cat 
 * -------------- */

function sub_cat_industry_dynamic_title_func() {
    $term = get_queried_object();
    $industry_sub_cat_page_title = get_field('industry_sub_cat_page_title', get_queried_object());
    $output = (!empty($industry_sub_cat_page_title)) ? $industry_sub_cat_page_title : $term->name;
    return '<h1>'.$output.'</h1>';
} 
add_shortcode('sub_cat_industry_dynamic_title', 'sub_cat_industry_dynamic_title_func');

function sub_cat_industry_dynamic_desc_func() {
    return get_queried_object()->description;
} 
add_shortcode('sub_cat_industry_dynamic_desc', 'sub_cat_industry_dynamic_desc_func');

function product_search_filter_func() {
    ob_start(); ?>
    <div class="product_filter_search"> <input type="text" placeholder="Search" /> <input type="submit" value="submit" /></div> <?php
    return ob_get_clean();
}
add_shortcode('product_search_filter', 'product_search_filter_func');

function productListing_filter_func() {
    ob_start(); ?>
    <div class="product_filters_ajax_load">
        <div class="loop_box-wrapper-main load"></div>
        <div class="loop_box-wrapper-main search"></div>
        <span class="ajaxloader"></span>
    </div> <?php
    return ob_get_clean();
    die();
} 
add_shortcode('productListing_filter', 'productListing_filter_func');

function productListing_loadmore_func() {
    global $wp_query;
    if($wp_query->max_num_pages > 1) {
        $output = '<div class="et_pb_button product_loadmore">Load more products</div>'; 
    } 
    return $output;
}
add_shortcode('productListing_loadmore', 'productListing_loadmore_func');

function load_productlisting() {
    global $post;
    $args = array(
        'order' =>  'ASC',
        'orderby' => 'title',
        'post_type' => 'industry',
        'posts_per_page'  => 9,
        'paged'  => $_POST['ltp_current_page'],
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'industry_product',
                'field' => 'term_id',
                'terms' => $_POST['ltp_termid']
            )
        )
    );
    $result = new WP_Query($args);
    if($result->have_posts()) {
        while($result->have_posts()) {
            $result->the_post();
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'full'); } 
            $output;
            $output .= '<div class="loop_box-wrapper-main-item">';
            $output .= '<a href="' . get_the_permalink() . '" class="loop_box-wrapper-main-item-absolute_btn"></a>';
            $output .= '<div class="loop_box-wrapper-main-item-img"><a href="' . get_the_permalink() . '"><img src="'.$featured_img .'" alt="' . get_the_title() . '";></a></div>';
            $output .= '<div class="loop_box-wrapper-main-item-box">';
            $output .= '<div class="loop_box-wrapper-main-item-box-text">' . get_the_title() . '</div>';
            $output .= '<a class="et_pb_button loop_box-wrapper-main-item-box-btn" href="' . get_the_permalink() . '"></a>';
            $output .= '</div>';
            $output .= '</div>';
        }
    } else {
        $output .= '<div class="ajaxloader"></div>';
        $output .= '<div class="products no-results">';
        $output .= '<h4>No matching articles found</h4>';
        $output .= '</div>';
    }
    wp_reset_query();
    echo $output;
    die();
}
add_action( 'wp_ajax_load_productlisting', 'load_productlisting' );
add_action( 'wp_ajax_nopriv_load_productlisting', 'load_productlisting' );

function getProductListing() {
    global $post;
    $args = array(
        'post_type' => 'industry',
        'posts_per_page'  => -1,
        's' => $_POST['ltp_search'],
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'industry_product',
                'field' => 'term_id',
                'terms' => $_POST['ltp_termid']
            )
        )
    );
    query_posts( $args );
    if(have_posts()) {
        while(have_posts()) { 
            the_post();
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'full'); } 
            $output;
            $output .= '<div class="loop_box-wrapper-main-item">';
            $output .= '<a href="' . get_the_permalink() . '" class="loop_box-wrapper-main-item-absolute_btn"></a>';
            $output .= '<div class="loop_box-wrapper-main-item-img"><a href="' . get_the_permalink() . '"><img src="'.$featured_img .'" alt="' . get_the_title() . '";></a></div>';
            $output .= '<div class="loop_box-wrapper-main-item-box">';
            $output .= '<div class="loop_box-wrapper-main-item-box-text">' . get_the_title() . '</div>';
            $output .= '<a class="et_pb_button loop_box-wrapper-main-item-box-btn" href="' . get_the_permalink() . '"></a>';
            $output .= '</div>';
            $output .= '</div>';
        }
    } else {
        $output .= '<div class="ajaxloader"></div>';
        $output .= '<div class="products no-results">';
        $output .= '<h4>No matching articles found</h4>';
        $output .= '</div>';
    }
    wp_reset_query();
    echo $output;
    die();
}
add_action( 'wp_ajax_getProductListing', 'getProductListing' );
add_action( 'wp_ajax_nopriv_getProductListing', 'getProductListing' );

/* Labels Inner 
 * ------------ */

function estimate_redirection() {
    global $post;
    $get_single_term_link = get_the_terms( $post->ID, 'industry_product' );
    if($get_single_term_link[0]->parent === 0) {
        return '<a class="et_pb_button product-hero__sec-con-item-product-btn_1" href="' . get_term_link(  $get_single_term_link[0]->term_id, 'industry_product') . '/#call_estimate">Get an estimate</a>';
    }
}
add_shortcode('estimate_redirection', 'estimate_redirection');

function enquire_product_popup() {
    $output;
    $output = '<a class="et_pb_button product-hero__sec-con-item-product-btn_2" data-featherlight="#sing_term_id">Enquire about this product</a>';
    $output .= '<div id="sing_term_id" class="estimate_popup">
        <!--[if lte IE 8]>
        <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
            <![endif]-->
            <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2.js"></script>
            <script>
            hbspt.forms.create({
            region: "na1",
                portalId: "7096694",
                formId: "132ecedb-b859-4621-8063-0f24041f2370"
});
    </script>
</div>';
    return $output;
}
add_shortcode('enquire_product_popup', 'enquire_product_popup');

function product_slider_func() {
    global $post;
    $custom_postID = get_the_ID($post->ID);
    $product_sliders = get_field('product_sliders', $custom_postID);
    $count = count($product_sliders);
    $output;
    if(!empty($product_sliders)) {
        $output .= '<div class="slider_gallery-wrapper master">';
        foreach($product_sliders as $product_slider) {
            $get_product_slider = (!empty($product_slider['image'])) ? $product_slider['image'] : get_stylesheet_directory_uri() . '/images/dummy_img.png';
            $output .= '<div class="slider_gallery-wrapper-item">';
            $output .= '<img class="slider_gallery-wrapper-item-img" src="' . $get_product_slider . '">';
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    if(!empty($product_sliders)) {
        $hidden = ($count) <= 1 ? "hidden" : "";
        $output .= '<div class="slider_gallery-wrapper slave '.$hidden.'">';
        foreach($product_sliders as $product_slider) {
            $get_product_slider = (!empty($product_slider['image'])) ? $product_slider['image'] : get_stylesheet_directory_uri() . '/images/dummy_img.png';
            $output .= '<div class="slider_gallery-wrapper-item">';
            $output .= '<img class="slider_gallery-wrapper-item-img" src="' . $get_product_slider . '">';
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    return $output;
} 
add_shortcode('product_slider_gallery', 'product_slider_func');

/* About Us 
 * -------- */

function the_team_func() {
    $team_members = get_field('team_members', 'option');
    $output;
    $i = 0;
    $output .= '<div class="team_loop_box-wrapper">';
    foreach($team_members as $team_member) {
        $team_name_slug = str_replace( ' ', '-', strtolower( $team_member['name'] ) );
        $linkedin = $team_member['linkedin'];
        $facebook = $team_member['facebook'];
        $instagram = $team_member['instagram'];
        $twitter = $team_member['twitter'];
        $first_name =  explode( ' ',trim( $team_member['name'] ) );
        $output .= '<div class="team_loop_box-wrapper-item">';
        $output .= '<img class="team_loop_box-wrapper-item-img" src="' . $team_member['image'] . '">';
        $output .= '<div class="team_loop_box-wrapper-item-con">';
        $output .= '<div class="team_loop_box-wrapper-item-con-box">';
        $output .= '<div class="team_loop_box-wrapper-item-con-box-name"><h4>' . $team_member['name'] . '</h4></div>';
        $output .= '<div class="team_loop_box-wrapper-item-con-box-position">' . $team_member['position'] . '</div>';
        $output .= '</div>';
        $output .= '<button class="team_loop_box-wrapper-item-con-btn" id="' . 'myBtn_' . $i . '" data-featherlight="#'. $team_name_slug .'"></button>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<div class="box__popup" id="'. $team_name_slug .'">';
        $output .= '<div class="box__popup-con">';
        $output .= '<div class="box__popup-con-item left">';
        $output .= '<img class="box__popup-con-item img" src="' . $team_member['image'] . '" alt="' . $team_member['image'] . '">';
        $output .= '<div class="box__popup-con-item name">' . $team_member['name'] . '</div>';
        $output .= '<div class="box__popup-con-item position">' . $team_member['position'] . '</div>';
        $output .= '<div class="box__popup-con-item social">';
            if ( $linkedin ){ $output .= '<a href="'. $linkedin .'" rel="noopener noreferrer" target="_blank" class="social-item linkedin"></a>'; }
            if ( $facebook ){ $output .= '<a href="'. $facebook .'" rel="noopener noreferrer" target="_blank" class="social-item facebook"></a>'; }
            if ( $instagram ){ $output .= '<a href="'. $instagram .'" rel="noopener noreferrer" target="_blank" class="social-item instagram"></a>'; }
            if ( $twitter ){ $output .= '<a href="'. $twitter .'" rel="noopener noreferrer" target="_blank" class="social-item twitter"></a>'; }
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<div class="box__popup-con-item right">';
        $output .= '<div class="box__popup-con-item name">' . $team_member['about'] . '</div>';
        $output .= '<div class="box__popup-con-item description">' . $team_member['description'] . '</div>';
        if ( $team_member['phone'] ){
            $output .= '<div class="box__popup-con-item cta"><a class="et_pb_button" href="tel:'. $team_member['phone'] .'">Call '. $first_name[0] .'</a></div>';
        }
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
        $i++;
    }
    $output .= '<div class="team_loop_box-wrapper-item unique">';
    $output .= '<div class="h_caption"><h2>Have a Question?</h2></div>';
    $output .= '<div class="h_paragraph">In case you haven\'t found the answer to your question, please feel free to contact us, our customer support will be happy to help you.</div>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
} 
add_shortcode('the_team', 'the_team_func');

/* Timeline Slider
 * ---------------  */

function timeline_slider_func() {
    $timeline_sliders = get_field('timeline_slider', 'option');
    $output;
    $output .= '<div class="timeline_slider-wrapper">';
    foreach($timeline_sliders as $timeline_slider) {
        $timeline_img = wp_get_attachment_image_url($timeline_slider['image'], 'timeline_img');
        $output .= '<div class="timeline_slider-wrapper-item">';
        $output .= '<div class="timeline_slider-wrapper-item-con">';
        $output .= '<img class="timeline_slider-wrapper-item-con-img" src="' . $timeline_img . '">';
        $output .= '<div class="timeline_slider-wrapper-item-con-tag">' . $timeline_slider['tag'] . '</div>';
        $output .= '</div>';
        $output .= '<div class="timeline_slider-wrapper-item-title"><h3>' . $timeline_slider['title'] . '</h3></div>';
        $output .= '<div class="timeline_slider-wrapper-item-desc">' . $timeline_slider['description'] . '</div>';
        $output .= '<div class="timeline_slider-wrapper-item-floating_tag">' . $timeline_slider['tag'] . '</div>';
        $output .= '</div>';
    }
    $output .= '</div>';
    return $output;
} 
add_shortcode('timeline_slider', 'timeline_slider_func');

function prod_videos_func() {
    $youtube_videos = get_field('youtube_videos', 'option');
    $output;
    $i = 0;
    $output .= '<div class="prod_videos-main">';
    foreach($youtube_videos as $youtube_video) {
        $youtube_vid = str_replace( ' ', '-', strtolower( $youtube_video['title'] ) );
        $output .= '<div class="prod_videos-main-item">';
        $output .= '<div class="prod_videos-main-item-box">';
        $output .= '<img class="prod_videos-main-item-box-image" src="'.$youtube_video['image'].'"/>';
        $output .= '<button class="prod_videos-main-item-box-btn" data-featherlight="#myModal_' . $i . '"></button>';
        $output .= '</div>';
        $output .= '<div class="prod_videos-main-item-title"><h3>'.$youtube_video['title'].'</h3></div>';
        $output .= '</div>';

        $output .= '<div class="box__popup" id="myModal_' . $i . '">';
        $output .= '<div class="box__popup-con">';
        $output .= '<div class="box__popup-con-item center">';
        $output .= '<iframe class="latest_news_updates__sec-con-item-main-item-iframe" src="'. $youtube_video['link'] .'?rel=0" title="'. $youtube_video['title'] .'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';

        $i++;
    }
    $output .= '</div>';
    return $output;
} 
add_shortcode('prod_videos', 'prod_videos_func');

/* Blog
 * ---- */

function gotto_inner_product_term_func() {
    global $post;
    $get_single_post_term_link = get_the_terms( $post->ID, 'industry_product' );
    if($get_single_post_term_link[0]->parent === 0) {
        return '<a href="' . get_term_link(  $get_single_post_term_link[0]->term_id, 'industry_product') . '">View all '. $get_single_post_term_link[0]->name .'</a>';
    }
} 
add_shortcode('gotto_inner_product_term', 'gotto_inner_product_term_func');

function recent_blog_func() {
    $args = array( 
        'post_type' => 'post',
        'posts_per_page' => 1,
        'order' =>  'DESC',
        'orderby' =>  'menu_order',
        'post_status' => 'publish'
    );
    $result = new WP_Query($args); 
    ob_start(); ?>
    <div class="singlepost_blog-loop_box-main"> <?php 
    if($result->have_posts()) {
        global $post;
        while($result->have_posts()) {
            $result->the_post();
            $get_single_post_term = get_the_terms( $post->ID, 'category' );
            if($get_single_post_term[1]->parent === 1) {
                $get_tag_term_name = $get_single_post_term[1]->name;
            } 
            $tag = $get_tag_term_name . ' - ' . get_the_date('d M Y', $post->ID); ?>
        <div class="singlepost_blog-loop_box-main-item">
            <div class="singlepost_blog-loop_box-main-item left"> 
                <img class="img" src="<?= get_the_post_thumbnail_url($post->ID, 'full'); ?>"/> 
            </div>
            <div class="singlepost_blog-loop_box-main-item right">
                <div class="text"><?= $tag; ?></div> 
                <div class="text-1"><?= get_the_title(); ?></div> 
                <div class="text-2"><?= get_excerpt_ex(300, $post->ID); ?></div>
                <a class="et_pb_button btn" href="<?= get_the_permalink(); ?>">Read more</a>
            </div> 
        </div>
        </div> <?php 
        }
        wp_reset_postdata();
        return ob_get_clean();
    }
}
add_shortcode('recent_blog', 'recent_blog_func');

function blog_filter_func() {
    $args = array(
        'orderby'           => 'term_id', 
        'order'             => 'ASC',                      
        'hide_empty'        => false,                       
        'childless'         => false,
    ); 
    $terms = get_terms( 'category', $args );
    ob_start(); 
    if ( !empty( $terms ) && !is_wp_error( $terms ) ) { ?>
        <div class="blog_filterbox-listing-filter-wrapper">
            <div class="blog_filterbox-listing-filter-wrapper-item">
                <div class="filter"> <?php
                foreach ( $terms as $term ) {
                    if (in_array( $term->parent, array(0) ) ) { ?>
                        <button data-tag="<?= $term->term_id; ?>"><?= $term->name; ?></button> <?php
                    }
                    if (in_array( $term->parent, array(1) ) ) { ?>
                        <button data-tag="<?= $term->term_id; ?>"><?= $term->name; ?></button> <?php
                    }
                } ?>
                </div>
            </div>
            <div class="filter_search">
                <input type="text" placeholder="Search" /><input type="submit" value="submit" />
            </div>
        </div>
        <div class="blog_filters_ajax_load">
            <span class="ajaxloader"></span>
        </div> <?php
    }
    return ob_get_clean();
    die();
}
add_shortcode('blog_filter', 'blog_filter_func');

function getBlogFilters() {
    global $post;
    $page         = $_POST['curr_page'];
    $cat          = $_POST['cat'];
    $search       = $_POST['search'];
    $display_num  = 6;
    $args = array(
        'post_type'       => 'post',
        'posts_per_page'  => -1,
        'orderby'         => 'menu_order',
        'order'           => 'DESC',
        'category__in'    => ( $cat != "" ) ? array($cat) : array(1),
        's'               => $search,
        'post_status' 	  => 'publish',
    );
    $result =  get_posts( $args );
    $listings_found = count($result);
    $totalpage = ceil($listings_found/$display_num);
    $new_search = array_slice($result, ($page * $display_num), $display_num);
    ob_start();
    if(!empty($new_search)) { ?>
        <span class="ajaxloader"></span>
        <div class="bloglists__sec-con-item-main" data-totalcount=<?=$listings_found?>> <?php 
        foreach($new_search as $post) {
            $pid = get_the_ID();
            $title = get_the_title($pid);
            $img = get_the_post_thumbnail_url($pid);
            $date = get_the_date('d M Y', $pid);
            $perm = get_the_permalink($pid);
            $authID = get_post_field( 'post_author', $pid );
            $auth = get_the_author_meta( 'display_name', $authID );
            $get_single_post_term = get_the_terms( $pid, 'category' );
            if($get_single_post_term[1]->parent === 1) {
                $get_tag_term_name = $get_single_post_term[1]->name;
            } ?>
            <div class="bloglists__sec-con-item-main-item">
            <div class="bloglists__sec-con-item-main-item-img-con match-item-img"> <img src="<?=$img;?>" alt="<?=$title.'-image';?>"/> </div>
            <div class="bloglists__sec-con-item-main-item-tag"> 
                <div class="bloglists__sec-con-item-main-item-tag-icon"></div> 
                <div class="bloglists__sec-con-item-main-item-tag-text"> <?=$get_tag_term_name  . ' ' . '&#8212;' . ' ' . $date;?> </div>
            </div> 
                <div class="bloglists__sec-con-item-main-item-title"><?=$title;?></div>
                <div class="et_pb_button bloglists__sec-con-item-main-item-btn"><a href="<?=$perm;?>">Read more</a></div>
                </div> <?php 
        } ?>
        </div>
        <script>
            (function($) {
                $(document).ready(function() {
                    $('.bloglists__sec-con-item-main ').each(function() {
                        $(this).children('.bloglists__sec-con-item-main-item').find('.bloglists__sec-con-item-main-item').matchHeight();
                    });
                });
            })(window.$ || window.jQuery);
        </script>
        <div class="pagination">
            <button class="arrow left <?= ($page == 0) ? 'disabled' : ''; ?>" data-page=<?=($page == 0) ? 0 : $page-1;?>> </button>
            <ul class="pages">
                <?php for( $x=0; $x<$totalpage; $x++ ): ?>
                <li><button class="page <?=($page == $x) ? 'current' : ''?>" data-page=<?=$x?>><?=$x+1?></button></li>
                <?php endfor; ?>
            </ul>
            <button class="arrow right <?= ($page+1 == $totalpage) ? 'disabled' : ''; ?>" data-page=<?=($page+1 == $totalpage) ? $totalpage-1 : $page+1;?>> </button>
        </div> <?php
    } else { ?>
            <div class="ajaxloader"></div>
            <div class="products no-results">
                <h4>No matching articles found</h4>
            </div> <?php
    }
    wp_reset_postdata();
    echo ob_get_clean();
    die();
}
add_action( 'wp_ajax_getBlogFilters', 'getBlogFilters' );
add_action( 'wp_ajax_nopriv_getBlogFilters', 'getBlogFilters' );

/* Blog inner
 * ---------- */

function product_single_dynamic_title() {
    global $post;
    return get_the_title($post->ID);
}
add_shortcode('product_single_dynamic_title', 'product_single_dynamic_title');

function blog_single_hero_time_stamp_func() {
    global $post;
    $get_single_post_term = get_the_terms( $post->ID, 'category' );
    if($get_single_post_term[1]->parent === 1) {
        $get_tag_term_name = $get_single_post_term[1]->name;
    }
    return '<div class="time_stamp-text">'. $get_tag_term_name . ' ' . '&#8212;' . ' ' . get_the_date( 'F d, Y' ) .'</div>';
} 
add_shortcode('blog_single_hero_time_stamp', 'blog_single_hero_time_stamp_func');

function other_labels_product_listing_func() {
    global $post;
    $args = array(
        'post_type'       => 'industry',
        'posts_per_page'  => 3,
        'order'       => 'ASC',
        'orderby'       => 'menu_order',
        'post_status' 	  => 'publish',
        'post__not_in' => array( $post->ID )
    );
    $query = new WP_Query($args);
    if($query->have_posts()) {
        $output;
        $output .= '<div class="loop_box-wrapper-main">';
        while ($query->have_posts()) { 
            $query->the_post();
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'full'); } 
            $output .= '<div class="loop_box-wrapper-main-item">';
            $output .= '<a href="' . get_the_permalink() . '" class="loop_box-wrapper-main-item-absolute_btn"></a>';
            $output .= '<div class="loop_box-wrapper-main-item-img"><a href="' . get_the_permalink() . '"><img src="'.$featured_img .'" alt="' . get_the_title() . '";></a></div>';
            $output .= '<div class="loop_box-wrapper-main-item-box">';
            $output .= '<div class="loop_box-wrapper-main-item-box-text">' . get_the_title() . '</div>';
            $output .= '<a class="et_pb_button loop_box-wrapper-main-item-box-btn" href="' . get_the_permalink() . '"></a>';
            $output .= '</div>';
            $output .= '</div>';
        }
        global $wp_query;
        $output .= '</div>';
    } else {
        $output .= '<p>Sorry, there are no courses to display</p>';
    }
    wp_reset_postdata();
    return $output;
} 
add_shortcode('other_labels_product_listing', 'other_labels_product_listing_func');

/* Contact Us 
 * ---------- */

function contact_us_gmap_func() {
    $g_locations = get_field('map_locations', 'option');
    $output;
    $output .= '<div class="locations__data">';
    foreach($g_locations as $g_location) {
        $output .= '<input type="hidden" class="location__data" data-name="' . $g_location['name'] . '" data-addr="' . $g_location['address'] . '" data-lat="' . $g_location['latitude'] . '" data-long="' . $g_location['longitude'] . '" data-link="' . $g_location['google_map_link'] . '" />';
    }
    $output .= '</div>';
    $output .= '<div class="map__wrapper"> <div id="cohesion__map"></div> </div>';
    return $output;
} 
add_shortcode('contact_us_gmap', 'contact_us_gmap_func');

function hubspot_bloglists_func() {

    /* v_inspect(date("Ymd")); */
    /* v_inspect(current_time('timestamp')); */
    /* v_inspect(current_time('U')); */


    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.hubapi.com/cms/v3/blogs/posts?hapikey=43d36949-2a15-4bf8-90a9-13fa596a13b3&state=PUBLISHED',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "postman-token: 17d07ea3-0520-4151-07fc-0ab30600df32"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $jsonDatas = json_decode($response);
        ob_start(); ?>
        <div class="blog_loop_box-main"> <?php
        foreach ($jsonDatas->results as $jsonData) {
            $createDate = new DateTime($jsonData->publishDate);
            $strip = $createDate->format('Y-m-d'); ?>
            <div class="blog_loop_box-main-item">
            <div class="blog_loop_box-main-item-img-con"> <img src="<?= $jsonData->featuredImage; ?>" alt="<?=$jsonData->name;?>"> </div>
                <div class="blog_loop_box-main-item-date"><?= $strip; ?></div>
                <div class="blog_loop_box-main-item-title"><a href="<?= $jsonData->url; ?>"><?= $jsonData->name;?></a></div>
                <div class="blog_loop_box-main-item-desc"><?=$jsonData->metaDescription;?></div>
                <div class="blog_loop_box-main-item-btn"><a class="et_pb_button" href="<?= $jsonData->url; ?>">Read more</a></div>
                </div> <?php
        } ?>
        </div> 
        <a href="https://blog.cohesionlabels.com.au/" class="cta_btn">View all blogs</a> <?php 
        return ob_get_clean();
    } 
}
add_shortcode('hubspot_bloglists', 'hubspot_bloglists_func');

/* ----------- 
 * WOOCOMMERCE 
 * ----------- */

/* Woocommerce - Defaults 
 * ----------------------- */

function yourtheme_setup() {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_theme_support( 'woocommerce' ); 
add_action( 'after_setup_theme', 'yourtheme_setup' );

if( is_product() || is_cart() || is_account_page() || is_product_category()  ) {
    remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
} 

/* Woocommerce Registration 
 * ------------------------- */

function woo_custom_wc_registration_form_func() {
    if ( is_admin() ) return;
    if ( is_user_logged_in() ) return;
    ob_start();
    do_action( 'woocommerce_before_customer_login_form' ); ?>
        <h3> <?php _e( 'Register', 'woocommerce' ); ?> </h3>
        <h4> <?php _e( 'Please fill in the information below', 'woocommerce' ); ?> </h4>
          <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
             <?php do_action( 'woocommerce_register_form_start' ); ?>
             <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                   <label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
                   <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" placeholder="Enter your username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
                </p>
             <?php endif; ?>
             <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="reg_email"><?php esc_html_e( 'Email', 'woocommerce' ); ?> <span class="required">*</span></label>
                <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" placeholder="Enter your email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
             </p>
             <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                   <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
                   <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" placeholder="Enter your password" />
                </p>
             <?php else : ?>
                <p><?php esc_html_e( 'A password will be sent to your email address.', 'woocommerce' ); ?></p>
             <?php endif; ?>
             <?php do_action( 'woocommerce_register_form' ); ?>
             <p class="woocommerce-FormRow form-row">
                <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Create my Account', 'woocommerce' ); ?></button>
             </p>
             <?php do_action( 'woocommerce_register_form_end' ); ?>
          </form> <?php
    return ob_get_clean();
}
add_shortcode( 'woo_custom_wc_registration_form', 'woo_custom_wc_registration_form_func' ); 

function custom_woo_add_new_registration_fields() { ?>
    <p class="form-row form-row-first">
    <label for="reg_billing_first_name"><?php _e( 'First Name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name"placeholder="Enter your first name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
    <label for="reg_billing_last_name"><?php _e( 'Last Name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" placeholder="Enter your last name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
    </p>
    <p class="form-row form-row-wide">
    <label for="reg_billing_company"><?php _e( 'Company', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_company" id="reg_billing_company" placeholder="Enter your company" alue="<?php if ( ! empty( $_POST['billing_company'] ) ) esc_attr_e( $_POST['billing_company'] ); ?>" />
    </p>
    <div class="clear"></div> <?php
}
add_action( 'woocommerce_register_form_start', 'custom_woo_add_new_registration_fields' );

function custom_woo_validate_new_registration_fields( $errors, $username, $email ) {
    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
        $errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: Fyll i förnamn!', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
        $errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Fyll i efternamn!.', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_company'] ) && empty( $_POST['billing_company'] ) ) {
        $errors->add( 'billing_company_error', __( '<strong>Error</strong>: Fyll i företagsnamn!.', 'woocommerce' ) );
    }
    return $errors;
}
add_filter( 'woocommerce_registration_errors', 'custom_woo_validate_new_registration_fields', 10, 3 ); // Validate new fields for Woocommerce Registration

function save_new_registration_fields( $customer_id ) {
    if ( isset( $_POST['billing_first_name'] ) ) {
        update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
        update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
    }
    if ( isset( $_POST['billing_last_name'] ) ) {
        update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
        update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
    }
    if ( isset( $_POST['billing_company'] ) ) {
        update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
    }
}
add_action( 'woocommerce_created_customer', 'save_new_registration_fields' ); // Save new fields for Woocommerce Registration

/* Woocommerce - Sub Category
 * --------------------------- */

function add_btn_product_listing_func() {
    global $product;
    $link = $product->get_permalink();
    echo '<a href="' . $link . '" class="button addtocartbutton">View Product</a>';
}
add_action('woocommerce_after_shop_loop_item', 'add_btn_product_listing_func', 10 );

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 ); 

function display_category_title_on_sub_category_page() {
    echo '<h2 class="category-title">' . get_the_title() . '</h2>';
    echo '<div class="category-description">' . get_field('category_description') . '</div>';
}
add_action( 'woocommerce_before_subcategory_title', 'display_category_title_on_sub_category_page', 10 );

function woo_sub_cat_industry_dynamic_title_func() {
    $term = get_queried_object();
    $output = $term->name;
    return '<h1>'.$output.'</h1>';
} 
add_shortcode('woo_sub_cat_industry_dynamic_title', 'woo_sub_cat_industry_dynamic_title_func');

function woo_sub_cat_industry_dynamic_desc_func() {
    return get_queried_object()->description;
} 
add_shortcode('woo_sub_cat_industry_dynamic_desc', 'woo_sub_cat_industry_dynamic_desc_func');

/* Woocommerce - Category 
 * ----------------------------- */

function woo_productListing_filter_func() {
    ob_start(); ?>
        <div class="woo_product_filters_ajax_load">
            <div class="loop_box-wrapper-main woo_load"></div>
            <div class="loop_box-wrapper-main woo_search"></div>
            <span class="ajaxloader"></span>
            </div> <?php
    return ob_get_clean();
    die();
} 
add_shortcode('woo_productListing_filter', 'woo_productListing_filter_func');

function woo_product_search_filter_func() {
    ob_start(); ?>
    <div class="product_filter_search"> <input type="text" placeholder="Search" /> <input type="submit" value="submit" /></div> <?php
    return ob_get_clean();
}
add_shortcode('woo_product_search_filter', 'woo_product_search_filter_func');

function woo_productListing_loadmore_func() {
    global $wp_query;
    if($wp_query->max_num_pages > 1) {
        $output = '<div class="et_pb_button woo_product_loadmore">Load more products</div>'; 
    } 
    return $output;
}
add_shortcode('woo_productListing_loadmore', 'woo_productListing_loadmore_func');

function woo_load_productlisting() {
    global $post;
    $args = array(
        'order' =>  'ASC',
        'orderby' => 'title',
        'post_type' => 'product',
        'posts_per_page'  => 9,
        'paged'  => $_POST['ltp_current_page'],
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $_POST['ltp_termid']
            )
        )
    );
    $result = new WP_Query($args);
    if($result->have_posts()) {
        while($result->have_posts()) {
            $result->the_post();
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'full'); } 
            $output;
            $output .= '<div class="loop_box-wrapper-main-item">';
            $output .= '<a href="' . get_the_permalink() . '" class="loop_box-wrapper-main-item-absolute_btn"></a>';
            $output .= '<div class="loop_box-wrapper-main-item-img"><a href="' . get_the_permalink() . '"><img src="'.$featured_img .'" alt="' . get_the_title() . '";></a></div>';
            $output .= '<div class="loop_box-wrapper-main-item-box">';
            $output .= '<div class="loop_box-wrapper-main-item-box-text">' . get_the_title() . '</div>';
            $output .= '<a class="et_pb_button loop_box-wrapper-main-item-box-btn" href="' . get_the_permalink() . '"></a>';
            $output .= '</div>';
            $output .= '</div>';
        }
    } else {
        $output .= '<div class="ajaxloader"></div>';
        $output .= '<div class="products no-results">';
        $output .= '<h4>No matching articles found</h4>';
        $output .= '</div>';
    }
    wp_reset_query();
    echo $output;
    die();
}
add_action( 'wp_ajax_woo_load_productlisting', 'woo_load_productlisting' );
add_action( 'wp_ajax_nopriv_woo_load_productlisting', 'woo_load_productlisting' );

function woo_getProductListing() {
    global $post;
    $args = array(
        'post_type'       => 'product',
        'posts_per_page'  => -1,
        's'  => $_POST['ltp_search'],
        'post_status' 	  => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $_POST['ltp_termid']
            )
        )
    );
    query_posts( $args );
    if(have_posts()) {
        while(have_posts()) { 
            the_post();
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'full'); } 
            $output;
            $output .= '<div class="loop_box-wrapper-main-item">';
            $output .= '<a href="' . get_the_permalink() . '" class="loop_box-wrapper-main-item-absolute_btn"></a>';
            $output .= '<div class="loop_box-wrapper-main-item-img"><a href="' . get_the_permalink() . '"><img src="'.$featured_img .'" alt="' . get_the_title() . '";></a></div>';
            $output .= '<div class="loop_box-wrapper-main-item-box">';
            $output .= '<div class="loop_box-wrapper-main-item-box-text">' . get_the_title() . '</div>';
            $output .= '<a class="et_pb_button loop_box-wrapper-main-item-box-btn" href="' . get_the_permalink() . '"></a>';
            $output .= '</div>';
            $output .= '</div>';
        }
    } else {
        $output .= '<div class="ajaxloader"></div>';
        $output .= '<div class="products no-results">';
        $output .= '<h4>No matching articles found</h4>';
        $output .= '</div>';
    }
    wp_reset_query();
    echo $output;
    die();
}
add_action( 'wp_ajax_woo_getProductListing', 'woo_getProductListing' );
add_action( 'wp_ajax_nopriv_woo_getProductListing', 'woo_getProductListing' );

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 ); // remove permalink on product listing for sub category page
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 ); // remove ordering, notice, count 
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 ); // remove price on product listing

/* Woocommerce - Single Product
 * ----------------------------- */

function single_product_tab_description_func() {
    return get_field('description_value', get_the_ID());
}
add_shortcode('single_product_tab_description', 'single_product_tab_description_func');

function single_product_tab_material_func() {
    return get_field('material_value', get_the_ID());
}
add_shortcode('single_product_tab_material', 'single_product_tab_material_func');

function single_product_tab_faqs_func() {
    $faq = get_field('faq', get_the_ID());
    ob_start(); ?>
    <div id="accordion" class="main_faq"> <?php
        foreach($faq as $value) { ?> 
            <div class="l_h2"><h2><?php echo $value['title']; ?></h2></div>
            <div class="l_desc"><?php echo $value['text_description']; ?></div> <?php
        } ?> 
    </div> <?php 
    return ob_get_clean();
}
add_shortcode('single_product_tab_faqs', 'single_product_tab_faqs_func');

function add_div_after_price_func() {
    $listing_descriptions = get_field('listing_descriptions', get_the_ID());
    ob_start(); ?>
    <table class="variations" cellspacing="0">
    <tbody>
        <tr>
            <th class="label">Supplied</th>
            <td class="value">
               <ul>
                    <?php foreach($listing_descriptions as $value) { ?> 
                        <li><?php echo $value['description']; ?></li>
                    <?php } ?> 
               </ul> 
            </td>
        </tr>
    </tbody>
    </table> <?php
    return ob_get_clean;
}
add_action( 'woocommerce_single_product_summary', 'add_div_after_price_func', 20 );

function woo_related_product_func() {
    $get_single_post_terms = get_the_terms( $post->ID, 'product_cat' );
    $term_slug = [];
    global $post;
    if ( !empty( $get_single_post_terms ) ) {
        foreach($get_single_post_terms as $get_single_post_term) {
            $term_slug[] = $get_single_post_term->slug;
            $term_term_id[] = $get_single_post_term->term_id;
            $term_parent[] = $get_single_post_term->parent;
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => 3,
                'order' => 'ASC',
                'orderby' => 'menu_order',
                'post_status' => 'publish',
                'post__not_in' => array( $post->ID ),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => $get_single_post_term->slug
                    )
                )
            );
        }
    }
    $query = new WP_Query($args);
    if($query->have_posts()) {
        $output;
        $output .= '<div class="loop_box-wrapper-main">';
        while ($query->have_posts()) { 
            $query->the_post();
            if ( has_post_thumbnail() ) { $featured_img = get_the_post_thumbnail_url($post->ID, 'full'); } 
            $output .= '<div class="loop_box-wrapper-main-item">';
            $output .= '<a href="' . get_the_permalink() . '" class="loop_box-wrapper-main-item-absolute_btn"></a>';
            $output .= '<div class="loop_box-wrapper-main-item-img"><a href="' . get_the_permalink() . '"><img src="'.$featured_img .'" alt="' . get_the_title() . '";></a></div>';
            $output .= '<div class="loop_box-wrapper-main-item-box">';
            $output .= '<div class="loop_box-wrapper-main-item-box-text">' . get_the_title() . '</div>';
            $output .= '<a class="et_pb_button loop_box-wrapper-main-item-box-btn" href="' . get_the_permalink() . '"></a>';
            $output .= '</div>';
            $output .= '</div>';
        }
        global $wp_query;
        $output .= '</div>';
    } else {
        $output .= '<p>Sorry, there are no courses to display</p>';
    }
    wp_reset_postdata();
    return $output;
}
add_shortcode('woo_related_product', 'woo_related_product_func');

function my_add_to_cart_function( $message, $product_id ) { 
    $message = '<div class="notice_title">' . sprintf(esc_html__('%s has been added by to your cart.','woocommerce') . '</div>' . ' ' . '<div class="sidebarBtn"><a href="#" class="et_pb_button">View Cart</a></div>', get_the_title( $product_id ) ); 
    return $message; 
}
add_filter( 'wc_add_to_cart_message', 'my_add_to_cart_function', 10, 2 ); 

function woo_gotto_inner_product_term_func() {
    global $post;
    $get_single_post_term_link = get_the_terms( $post->ID, 'product_cat' );
    if($get_single_post_term_link[0]->parent !== 0) {
        return '<a href="' . get_term_link(  $get_single_post_term_link[0]->term_id, 'product_cat') . '">View all ' . $get_single_post_term_link[0]->name .'</a>';
    }
}
add_shortcode('woo_gotto_inner_product_term', 'woo_gotto_inner_product_term_func');

/* Redirections */

function custom_registration_redirect_after_registration_func() {
    wp_redirect(home_url('/my-account'));
    exit;
}
add_action('woocommerce_registration_redirect', 'custom_registration_redirect_after_registration_func', 2);

function woo_custom_redirect_after_purchase() {
    global $wp;
    if( is_checkout() && !empty( $wp->query_vars['order-received'] ) ) {
        wp_redirect( home_url('/thank-you') );
        exit;
    }
    if( is_shop() ) {
        wp_redirect(home_url());
        exit;
    }
}
add_action( 'template_redirect', 'woo_custom_redirect_after_purchase' );

/* Popup Thank you */

function thank_you_popup_func() {
    $customer_id = get_current_user_id();
    $order = wc_get_customer_last_order( $customer_id ); 
    ob_start(); ?>
    <div class="floating_thankyou_popup-box">
        <div class="floating_thankyou_popup-box_close">
            <button class="floating_thankyou_popup-box_close-btn"></button>
        </div>
        <h1>Order Successful!</h1> 
        <div class="floating_thankyou_popup-box_top"> 
            <ul>
                <li>
                    <div class="left">Amount Paid</div>
                    <div class="right"><?php echo $order->get_total(); ?></div>
                </li>
                <li>
                    <div class="left">Order Number</div>
                    <div class="right"><?php echo $order->get_order_number(); ?></div>
                </li>
            </ul>
        </div> 
        <div class="floating_thankyou_popup-box_bottom">
            <p>Thank you for placing your order. You will receive an email confirmation shortly with the expected delivery date for your item.</p>
            <a class="et_pb_button shopping_btn" href="/">Continue Shopping</a>
            <a class="et_pb_button view_btn" href="/my-account/orders/">View My Orders</a>
        </div> 
    </div> <?php
    return ob_get_clean();
}
add_shortcode('thank_you_popup', 'thank_you_popup_func');

/* Sidebar cart */

function cohesion_ajax_update_qty() {
    // Set item key as the hash found in input.qty's name
    $cart_item_key = $_POST['hash'];
    // Get the array of values owned by the product we're updating
    $product_values = WC()->cart->get_cart_item( $cart_item_key );
    // Get the quantity of the item in the cart
    $product_quantity = apply_filters( 'woocommerce_stock_amount_cart_item', apply_filters( 'woocommerce_stock_amount', preg_replace( "/[^0-9\.]/", '', filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT)) ), $cart_item_key );
    // Update cart validation
    $passed_validation  = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $product_values, $product_quantity );
    // Update the quantity of the item in the cart
    if ( $passed_validation ) {
      WC()->cart->set_quantity( $cart_item_key, $product_quantity, true );
    }
    // Fragments and mini cart are returned
    $data = array(
      'total' => WC()->cart->cart_contents_total,
      'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() )
    );
    wp_send_json( $data );
    /* die(); */
}
add_action( 'wp_ajax_update_qty', 'cohesion_ajax_update_qty' );
add_action( 'wp_ajax_nopriv_update_qty', 'cohesion_ajax_update_qty' );

function cohesion_ajax_product_remove() {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
      if($cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key'] ) {
        WC()->cart->remove_cart_item($cart_item_key);
      }
    }
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies(); // Fragments and mini cart are returned
    $data = array(
        'total' => WC()->cart->cart_contents_total,
        'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() )
    );
    wp_send_json( $data );
    exit();
}
add_action( 'wp_ajax_product_remove', 'cohesion_ajax_product_remove' );
add_action( 'wp_ajax_nopriv_product_remove', 'cohesion_ajax_product_remove' );

function cohesion_display_quantity_plus() {
    echo '<div class="label" >Quantity</div>';
    echo '<button type="button" class="qtyButtons minus">-</button>';
}
add_action( 'woocommerce_before_quantity_input_field', 'cohesion_display_quantity_plus' );

function cohesion_display_quantity_minus() {
    echo '<button type="button" class="qtyButtons plus">+</button>';
}
add_action( 'woocommerce_after_quantity_input_field', 'cohesion_display_quantity_minus' );

function woo_add_cart_quantity_plus_minus_func() { ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('form.cart .qtyButtons').unbind('click').bind( 'click', function() {
                var qty = $( this ).closest( '.quantity' ).find( '.qty' );
                var val   = parseFloat(qty.val());
                var max = parseFloat(qty.attr( 'max' ));
                var min = parseFloat(qty.attr( 'min' ));
                var step = parseFloat(qty.attr( 'step' ));
                if ( $( this ).is( '.plus' ) ) {
                    if ( max && ( max <= val ) ) {
                        qty.val( max );
                    } else {
                        qty.val( val + step );
                    }
                    qty.trigger("change");
                } else {
                    if ( min && ( min >= val ) ) {
                        qty.val( min );
                    } else if ( val > 1 ) {
                        qty.val( val - step );
                    }
                    qty.trigger("change");
                }
            });
        });
    </script> <?php
}
add_action( 'wp_footer', 'woo_add_cart_quantity_plus_minus_func' );

function woo_update_cart_quantity_func() { ?>
    <script type="text/javascript">
        var timeout;
        jQuery(document).ready(function($) {
            $(document.body).on('updated_cart_totals', function(e) {
                $('form.cart .qtyButtons').unbind('click').bind( 'click', function() {
                    var qty = $( this ).closest( '.quantity' ).find( '.qty' );
                    var val   = parseFloat(qty.val());
                    var max = parseFloat(qty.attr( 'max' ));
                    var min = parseFloat(qty.attr( 'min' ));
                    var step = parseFloat(qty.attr( 'step' ));
                    if ( $( this ).is( '.plus' ) ) {
                        if ( max && ( max <= val ) ) {
                            qty.val( max );
                        } else {
                            qty.val( val + step );
                        }
                        qty.trigger("change");
                    } else {
                        if ( min && ( min >= val ) ) {
                            qty.val( min );
                        } else if ( val > 1 ) {
                            qty.val( val - step );
                        }
                        qty.trigger("change");
                    }
                });
                $('.woocommerce').on('change', 'input.qty', function() {
                    if ( timeout !== undefined ) {
                        clearTimeout( timeout );
                    }
                    timeout = setTimeout(function() {
                        $("[name='update_cart']").trigger("click");
                    }, 500 );
                });
            });
        });
    </script> <?php
}
add_action( 'wp_footer', 'woo_update_cart_quantity_func' );

function force_clear_cart_func() {
	if ( is_page( array( 'thank-you', 'order-received' ) ) && isset( $_GET['order-received'] ) ) {
		WC()->cart->empty_cart();
	}
}
add_action( 'wp_head', 'force_clear_cart_func' );

function remove_my_account_tabs($items) {
    unset($items['downloads']);
    return $items;
}
add_filter('woocommerce_account_menu_items', 'remove_my_account_tabs', 999);

function add_reply_to_wc_admin_new_order($header = '', $id = '', $order) {
    $wc_email = new WC_Email(); //instantiate wc meail
    if ($id == 'new_order') {
        $reply_to_email = $order->billing_email;
        $header = 'Content-Type: ' . $wc_email->get_content_type() . "\r\n"; 
    }
    return $header;
}
add_filter('woocommerce_email_headers', 'add_reply_to_wc_admin_new_order', 10, 3);
