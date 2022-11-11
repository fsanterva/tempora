<?php
/**
 * The template for displaying Woocommerce Product
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WP_Bootstrap_Starter
 */

get_header(); ?>
	<div class="container custom_index">
		<div id="content-area" class="clearfix">
			<div id="left-area"> <?php 
                if ( is_singular( 'product' ) ) {
                    while ( have_posts() ) :
                        the_post();
                        wc_get_template_part( 'content', 'single-product' );
                    endwhile;
                } else { ?>
                    <div class="woo-top__sec">
                    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
                        <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
                    <?php endif; ?>
                    <?php do_action( 'woocommerce_archive_description' ); ?>
                    </div>
                    <div class="woo-bot__sec">
                    <?php if ( woocommerce_product_loop() ) : ?>
                        <?php do_action( 'woocommerce_before_shop_loop' ); ?>
                        <?php woocommerce_product_loop_start(); ?>
                        <?php if ( wc_get_loop_prop( 'total' ) ) : ?>
                            <?php while ( have_posts() ) : ?>
                                <?php the_post(); ?>
                                <?php wc_get_template_part( 'content', 'product' ); ?>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        <?php woocommerce_product_loop_end(); ?>
                        <?php do_action( 'woocommerce_after_shop_loop' ); ?>
                        <?php
                    else :
                        do_action( 'woocommerce_no_products_found' );
                    endif; ?>
                    </div><?php
                } ?>
            </div>
		</div>
	</div>
<?php get_footer(); ?>
