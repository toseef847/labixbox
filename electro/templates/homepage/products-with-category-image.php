<?php
/**
 * Products with category and image block
 *
 * @package Electro/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$section_class = empty( $section_class ) ? 'products-with-category-image' : 'products-with-category-image ' . $section_class;
if ( ! empty( $animation ) ) {
    $section_class .= ' animate-in-view';
}

$show_nav = ! empty( $categories ) && ! is_wp_error( $categories );
?>
<section class="<?php echo esc_attr( $section_class ); ?>" <?php if ( !empty( $animation ) ) : ?>data-animation="<?php echo esc_attr( $animation );?>"<?php endif; ?>>
    <header <?php if( $show_nav ): ?>class="show-nav"<?php endif; ?>>
        <h2 class="h1"><?php echo esc_html( $section_title ); ?></h2>
        <?php if ( true === $show_nav ) : ?>
            <ul class="nav nav-inline">
                <?php if ( ! empty( $categories_title ) ) : ?>
                    <li class="nav-item active">
                        <span class="nav-link"><?php echo esc_html( $categories_title ); ?></span>
                    </li>
                <?php endif; ?>
                <?php foreach( $categories as $category ) : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </header>
    <div class="products-with-category-image-inner row g-0">
        <?php if ( ! empty( $vcategories ) && ! is_wp_error( $vcategories ) ) : ?>
        <div class="categories-menu-list">
            <ul class="nav">
                <?php foreach( $vcategories as $vcategory ) : ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo esc_url( get_term_link( $vcategory ) ); ?>"><?php echo esc_html( $vcategory->name ); ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $image[0] ) ) : ?>
        <div class="image-block">
            <a href="<?php echo esc_attr( $img_action_link ); ?>" class="action-link">
                <img src="<?php echo esc_url( $image[0] ); ?>" alt="">
            </a>
        </div>
        <?php endif; ?>

        <div class="products-block">
            <?php

                if ( electro_is_wide_enabled() ) {
                    $default_atts['columns_wide']   = intval( $args['columns_wide'] );
                    $shortcode_atts                 = wp_parse_args( $shortcode_atts, $default_atts );
                }

            echo electro_do_shortcode( $shortcode_tag,  $shortcode_atts );

            ?>
        </div>
    </div>
</section>
