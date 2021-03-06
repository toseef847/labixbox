<?php
/**
 * Electro Helper Class for WooCommerce
 */

class Electro_WC_Helper {

    public static function init() {
        
        add_action( 'wp_ajax_woocommerce_json_search_simple_products',  array( 'Electro_WC_Helper', 'json_search_simple_products' ) );

        // Accessories Ajax Add to Cart for Variable Products
        add_action( 'wp_ajax_nopriv_electro_variable_add_to_cart',      array( 'Electro_WC_Helper', 'add_to_cart' ) );
        add_action( 'wp_ajax_electro_variable_add_to_cart',             array( 'Electro_WC_Helper', 'add_to_cart' ) );

        // Accessories Ajax Total Price Update
        add_action( 'wp_ajax_nopriv_electro_accessories_total_price',   array( 'Electro_WC_Helper', 'accessory_checked_total_price' ) );
        add_action( 'wp_ajax_electro_accessories_total_price',          array( 'Electro_WC_Helper', 'accessory_checked_total_price' ) );

        // Add options on General Tab
        add_action( 'woocommerce_product_options_general_product_data', array( 'Electro_WC_Helper', 'product_options_general_product_data' ) );

        // Add options on Inventry Tab
        add_action( 'woocommerce_product_options_inventory_product_data',   array( 'Electro_WC_Helper', 'product_options_inventory_product_data' ) );

        // Add Accessories Tab
        add_action( 'woocommerce_product_write_panel_tabs', array( 'Electro_WC_Helper', 'add_product_accessories_panel_tab' ) );
        add_action( 'woocommerce_product_data_panels',      array( 'Electro_WC_Helper', 'add_product_accessories_panel_data' ) );

        // Add Specification Tab
        add_action( 'woocommerce_product_write_panel_tabs', array( 'Electro_WC_Helper', 'add_product_specification_panel_tab' ) );
        add_action( 'woocommerce_product_data_panels',      array( 'Electro_WC_Helper', 'add_product_specification_panel_data' ) );
        
        foreach ( wc_get_product_types() as $value => $label ) {
            // Save options on General Tab
            add_action( 'woocommerce_process_product_meta_' . $value, array( 'Electro_WC_Helper', 'save_product_style_to_product_options' ) );    

            // Save options on Inventory Tab
            add_action( 'woocommerce_process_product_meta_' . $value, array( 'Electro_WC_Helper', 'save_total_stock_quantity_to_product_options' ) );

             // Save Accessories Tab
            add_action( 'woocommerce_process_product_meta_' . $value, array( 'Electro_WC_Helper', 'save_product_accessories_panel_data' ) );

            // Save Specification Tab
            add_action( 'woocommerce_process_product_meta_' . $value, array( 'Electro_WC_Helper', 'save_product_specification_panel_data' ) );
        }
    }

    public static function get_ratings_counts( $product ) {
        global $wpdb;
        
        $product_id = electro_wc_get_product_id( $product );
        $counts     = array();
        $raw_counts = $wpdb->get_results( $wpdb->prepare("
                SELECT meta_value, COUNT( * ) as meta_value_count FROM $wpdb->commentmeta
                LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
                WHERE meta_key = 'rating'
                AND comment_post_ID = %d
                AND comment_approved = '1'
                AND meta_value > 0
                GROUP BY meta_value
            ", $product_id ) );
        
        foreach ( $raw_counts as $count ) {
            $counts[ $count->meta_value ] = $count->meta_value_count;
        }
        
        return $counts;
    }

    /**
     * Search for products and echo json.
     *
     * @param string $term (default: '')
     * @param string $post_types (default: array('product'))
     */
    public static function json_search_simple_products( $term = '', $post_types = array( 'product' ) ) {
        global $wpdb;

        ob_start();

        check_ajax_referer( 'search-products', 'security' );

        if ( empty( $term ) ) {
            $term = wc_clean( stripslashes( $_GET['term'] ) );
        } else {
            $term = wc_clean( $term );
        }

        if ( empty( $term ) ) {
            die();
        }

        $like_term = '%' . $wpdb->esc_like( $term ) . '%';

        if ( is_numeric( $term ) ) {
            $query = $wpdb->prepare( "
                SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
                WHERE posts.post_status = 'publish'
                AND (
                    posts.post_parent = %s
                    OR posts.ID = %s
                    OR posts.post_title LIKE %s
                    OR (
                        postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
                    )
                )
            ", $term, $term, $term, $like_term );
        } else {
            $query = $wpdb->prepare( "
                SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
                WHERE posts.post_status = 'publish'
                AND (
                    posts.post_title LIKE %s
                    or posts.post_content LIKE %s
                    OR (
                        postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
                    )
                )
            ", $like_term, $like_term, $like_term );
        }

        $query .= " AND posts.post_type IN ('" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "')";

        if ( ! empty( $_GET['exclude'] ) ) {
            $query .= " AND posts.ID NOT IN (" . implode( ',', array_map( 'intval', explode( ',', $_GET['exclude'] ) ) ) . ")";
        }

        if ( ! empty( $_GET['include'] ) ) {
            $query .= " AND posts.ID IN (" . implode( ',', array_map( 'intval', explode( ',', $_GET['include'] ) ) ) . ")";
        }

        if ( ! empty( $_GET['limit'] ) ) {
            $query .= " LIMIT " . intval( $_GET['limit'] );
        }

        $posts          = array_unique( $wpdb->get_col( $query ) );
        $found_products = array();

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $post ) {
                $product = wc_get_product( $post );

                if ( ! current_user_can( 'read_product', $post ) ) {
                    continue;
                }

                if ( ! $product || ( $product->is_type( 'variation' ) && empty( $product->parent ) ) ) {
                    continue;
                }

                if ( ! $product->is_type( 'simple' ) ) {
                    continue;
                }

                $found_products[ $post ] = rawurldecode( $product->get_formatted_name() );
            }
        }

        $found_products = apply_filters( 'woocommerce_json_search_found_products', $found_products );

        wp_send_json( $found_products );
    }

    public static function product_options_general_product_data() {
        echo '<div class="options_group">';
            woocommerce_wp_select( array(
                'id' => '_product_layout',
                'label' => esc_html__( 'Product Layout', 'electro' ),
                'options' => array(
                    ''                    => esc_html__( 'Default', 'electro' ),
                    'full-width'          => esc_html__( 'Full Width', 'electro' ),
                    'left-sidebar'        => esc_html__( 'Left Sidebar', 'electro' ),
                    'right-sidebar'       => esc_html__( 'Right Sidebar', 'electro' ),
                ),
                'desc_tip' => true,
                'description' => esc_html__( 'Select product layout to display on product page.', 'electro' )
            ) );
            woocommerce_wp_select( array(
                'id' => '_product_style',
                'label' => esc_html__( 'Product Style', 'electro' ),
                'options' => array(
                    ''         => esc_html__( 'Default', 'electro' ),
                    'normal'   => esc_html__( 'Normal', 'electro' ),
                    'extended' => esc_html__( 'Extended', 'electro' )
                ),
                'desc_tip' => true,
                'description' => esc_html__( 'Select product style to display on product page.', 'electro' )
            ) );
        echo '</div>';
    }

    public static function save_product_style_to_product_options( $post_id ) {
        $product_layout = isset( $_POST['_product_layout'] ) ? wc_clean( $_POST['_product_layout'] ) : '' ;
        $product_style = isset( $_POST['_product_style'] ) ? wc_clean( $_POST['_product_style'] ) : '' ;
        update_post_meta( $post_id, '_product_layout', $product_layout );
        update_post_meta( $post_id, '_product_style', $product_style );
    }

    public static function product_options_inventory_product_data() {
        echo '<div class="options_group">';
            woocommerce_wp_text_input(  array( 
                'id' => '_total_stock_quantity',
                'label' => esc_html__( 'Total Stock Quantity', 'electro' ),
                'desc_tip' => 'true',
                'description' => esc_html__( 'Total Stock Quantity Available. This will be used to calculate prograss bar in onsale element.', 'electro' ),
                'type' => 'text'
            ) );
        echo '</div>';
    }

    public static function save_total_stock_quantity_to_product_options( $post_id ) {
        $current_stock = isset( $_POST['_stock'] ) ? wc_clean( $_POST['_stock'] ) : get_post_meta( $post_id, '_stock', true ) ;
        $stock_quantity = isset( $_POST['_total_stock_quantity'] ) ? wc_clean( $_POST['_total_stock_quantity'] ) : '' ;
        if( empty( $stock_quantity ) && ! empty( $current_stock ) && round( $current_stock ) > 0 ) {
            $stock_quantity = round( $current_stock );
        }
        update_post_meta( $post_id, '_total_stock_quantity', $stock_quantity );
    }

    public static function add_product_accessories_panel_tab() {
        ?>
        <li class="accessories_options accessories_tab show_if_simple show_if_variable">
            <a href="#accessories_product_data"><span><?php echo esc_html__( 'Accessories', 'electro' ); ?></span></a>
        </li>
        <?php
    }

    public static function add_product_accessories_panel_data() {
        global $post;
        ?>
        <div id="accessories_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <p class="form-field">
                    <label for="accessory_ids"><?php _e( 'Accessories', 'electro' ); ?></label>
                    <?php if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7', '<' ) ) : ?>
                        <input type="hidden" class="wc-product-search" style="width: 50%;" id="accessory_ids" name="accessory_ids" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'electro' ); ?>" data-action="woocommerce_json_search_simple_products" data-multiple="true" data-exclude="<?php echo intval( $post->ID ); ?>" data-selected="<?php
                            $product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_accessory_ids', true ) ) );
                            $json_ids    = array();
                            foreach ( $product_ids as $product_id ) {
                                $product = wc_get_product( $product_id );
                                if ( is_object( $product ) ) {
                                    $json_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
                                }
                            }
                            echo esc_attr( json_encode( $json_ids ) );
                        ?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />

                    <?php else : ?>

                        <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="accessory_ids" name="accessory_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'electro' ); ?>" data-action="woocommerce_json_search_simple_products" data-exclude="<?php echo intval( $post->ID ); ?>">
                            <?php
                                $product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_accessory_ids', true ) ) );

                                foreach ( $product_ids as $product_id ) {
                                    $product = wc_get_product( $product_id );
                                    if ( is_object( $product ) ) {
                                        echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                                    }
                                }
                            ?>
                        </select>
                    <?php endif; ?>

                    <?php echo wc_help_tip( __( 'Accessories are products which you recommend to be bought along with this product. Only simple products can be added as accessories.', 'electro' ) ); ?>
                </p>
            </div>
        </div>
        <?php
    }

    public static function save_product_accessories_panel_data( $post_id ) {
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7', '<' ) ) {
            $accessories = isset( $_POST['accessory_ids'] ) ? array_filter( array_map( 'intval', explode( ',', $_POST['accessory_ids'] ) ) ) : array();
        } else {
            $accessories = isset( $_POST['accessory_ids'] ) ? array_map( 'intval', (array) $_POST['accessory_ids'] ) : array();
        }
        update_post_meta( $post_id, '_accessory_ids', $accessories );
    }

    public static function get_accessories( $product ) {
        $product_id = electro_wc_get_product_id( $product );
        $accessory_ids = get_post_meta( $product_id, '_accessory_ids', true );
        return apply_filters( 'woocommerce_product_accessory_ids', (array) maybe_unserialize( $accessory_ids ), $product );
    }

    public static function add_product_specification_panel_tab() {
        ?>
        <li class="specification_options specification_tab">
            <a href="#specification_product_data"><span><?php echo esc_html__( 'Specifications', 'electro' ); ?></span></a>
        </li>
        <?php
    }

    public static function add_product_specification_panel_data() {
        global $post;
        ?>
        <div id="specification_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                    $display_attributes = get_post_meta( $post->ID, '_specifications_display_attributes', true );
                    woocommerce_wp_checkbox( array(
                        'id' => '_specifications_display_attributes',
                        'label' => esc_html__( 'Display Attributes', 'electro' ),
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Display Attributes for products in specifications tab.', 'electro' ),
                        'value' => $display_attributes ? $display_attributes : 'yes'
                    ) );

                    woocommerce_wp_text_input(  array( 
                        'id' => '_specifications_attributes_title',
                        'label' => esc_html__( 'Attributes Title', 'electro' ),
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Attributes Title for products in specifications tab.', 'electro' ),
                        'type' => 'text'
                    ) );
                ?>
            </div>
            <div class="options_group">
                <?php
                    $specifications = get_post_meta( $post->ID, '_specifications', true );
                    wp_editor( htmlspecialchars_decode( $specifications ), '_specifications', array() );
                ?>
            </div>
        </div>
        <?php
    }

    public static function save_product_specification_panel_data( $post_id ) {
        $display_attributes = isset( $_POST['_specifications_display_attributes'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_specifications_display_attributes', $display_attributes );

        $attributes_title = isset( $_POST['_specifications_attributes_title'] ) ? $_POST['_specifications_attributes_title'] : '';
        update_post_meta( $post_id, '_specifications_attributes_title', $attributes_title );

        $specifications = isset( $_POST['_specifications'] ) ? $_POST['_specifications'] : '';
        update_post_meta( $post_id, '_specifications', $specifications );
    }
    
    public static function get_products( $args ) {
        $defaults = array(
            'limit'         => 5,
            'show'          => '',
            'orderby'       => 'date',
            'order'         => 'desc',
            'hide_free'     => 0,
            'show_hidden'   => 0,
            'category'      => '',
            'operator'      => 'IN'
        );

        $args = wp_parse_args( $args, $defaults );

        $number     = absint( $args['limit'] );
        $show       = sanitize_title( $args['show'] );
        $orderby    = sanitize_title( $args['orderby'] );
        $order      = sanitize_title( $args['order'] );
        $category   = sanitize_title( $args['category'] );
        $operator   = sanitize_title( $args['operator'] );

        $query_args = array(
            'posts_per_page' => $number,
            'post_status'    => 'publish',
            'post_type'      => 'product',
            'no_found_rows'  => 1,
            'order'          => $order,
            'meta_query'     => array()
        );

        if ( empty( $args['show_hidden'] ) ) {
            $query_args['meta_query'][] = WC()->query->visibility_meta_query();
            $query_args['post_parent']  = 0;
        }

        if ( ! empty( $args['hide_free'] ) ) {
            $query_args['meta_query'][] = array(
                'key'     => '_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'DECIMAL',
            );
        }

        if( ! empty( $args['post__in'] ) ) {
            $query_args['post__in'] = $args['post__in'];
        }

        $query_args['meta_query'][] = WC()->query->stock_status_meta_query();
        $query_args['meta_query']   = array_filter( $query_args['meta_query'] );

        switch ( $show ) {
            case 'featured' :
                $query_args['meta_query'][] = array(
                    'key'   => '_featured',
                    'value' => 'yes'
                );
                break;
            case 'onsale' :
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                if( ! empty( $args['post__in'] ) ) {
                    $query_args['post__in'] = $args['post__in'];
                } else {
                    $query_args['post__in'] = $product_ids_on_sale;
                }
                break;
        }

        switch ( $orderby ) {
            case 'price' :
                $query_args['meta_key'] = '_price';
                $query_args['orderby']  = 'meta_value_num';
                break;
            case 'rand' :
                $query_args['orderby']  = 'rand';
                break;
            case 'sales' :
                $query_args['meta_key'] = 'total_sales';
                $query_args['orderby']  = 'meta_value_num';
                break;
            default :
                $query_args['orderby']  = 'date';
        }

        if ( ! empty( $category ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'terms'    => array_map( 'sanitize_title', explode( ',', $category ) ),
                    'field'    => 'slug',
                    'operator' => $operator
                )
            );
        }

        return new WP_Query( apply_filters( 'electro_get_products_query_args', $query_args ) );
    }

    public static function get_sale_products( $args ) {

        $args = wp_parse_args( array( 'show' => 'onsale' ), $args );

        return self::get_products( $args );
    }

    public static function get_best_selling_products( $args ) {

        $args = wp_parse_args( array( 'orderby' => 'sales', 'order' => 'desc' ), $args );

        return self::get_products( $args );
    }

    public static function get_top_rated_products( $args ) {
        $defaults = array(
            'per_page' => '12',
            'columns'  => '4',
            'orderby'  => 'title',
            'order'    => 'asc',
            'category' => '',  // Slugs
            'operator' => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
        );

        $args = wp_parse_args( $args, $defaults );

        $query_args = array(
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
            'orderby'             => $args['orderby'],
            'order'               => $args['order'],
            'posts_per_page'      => $args['per_page'],
            'meta_query'          => WC()->query->get_meta_query()
        );

        $category = sanitize_title( $args['category'] );
        $operator = sanitize_title( $args['operator'] );

        if ( ! empty( $category ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'terms'    => array_map( 'sanitize_title', explode( ',', $category ) ),
                    'field'    => 'slug',
                    'operator' => $operator
                )
            );
        }

        add_filter( 'posts_clauses', array( __CLASS__, 'order_by_rating_post_clauses' ) );
        $products = new WP_Query( apply_filters( 'electro_get_top_rated_products_query_args', $query_args ) );
        remove_filter( 'posts_clauses', array( __CLASS__, 'order_by_rating_post_clauses' ) );

        return $products;
    }

    public static function order_by_rating_post_clauses( $args ) {
        global $wpdb;

        $args['where']   .= " AND $wpdb->commentmeta.meta_key = 'rating' ";
        $args['join']    .= "LEFT JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID) LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)";
        $args['orderby'] = "$wpdb->commentmeta.meta_value DESC";
        $args['groupby'] = "$wpdb->posts.ID";
        
        return $args;
    }

    public static function product_card_loop( $products, $columns = 2, $rows = 2, $columns_wide = 3 ) {

        electro_reset_woocommerce_loop();

        $columns            = intval( $columns ) ? intval( $columns ) : 5;
        $columns_wide       = intval( $columns_wide ) ? intval( $columns_wide ) : 3;
        $rows               = intval( $rows ) ? intval( $rows ) : 2;
        $products_per_view  = $rows * $columns_wide;
        $products_count     = 0;

        wc_set_loop_prop( 'columns', $columns );
        wc_set_loop_prop( 'columns_wide', $columns_wide );

        ob_start();

        if ( $products->have_posts() ) {

            do_action( 'electro_before_product_card_loop' );

            woocommerce_product_loop_start();

            while ( $products->have_posts() ) : $products->the_post();

                if ( $products_count != 0 && $products_per_view != 0 && ( $products_count % $products_per_view ) == 0 ) {
                    woocommerce_product_loop_end();
                    woocommerce_product_loop_start();
                }

                wc_get_template_part( 'templates/contents/content', 'product-card-view' );

                $products_count++;

            endwhile;

            woocommerce_product_loop_end();

            do_action( 'electro_after_product_card_loop' );
        }

        woocommerce_reset_loop();
        wp_reset_postdata();

        return '<div class="woocommerce columns-' . $columns . ' product-cards-carousel owl-carousel">' . ob_get_clean() . '</div>';
    }

    public static function product_loop_rows( $products, $columns = 5, $rows = 2 ) {

        electro_reset_woocommerce_loop();

        $columns            = intval( $columns );
        $rows               = intval( $rows );
        $products_per_view  = $rows * $columns;
        $products_count     = 0;

        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '<' ) ) {
            global $woocommerce_loop;
            $woocommerce_loop['columns'] = $columns;
        } else {
            wc_set_loop_prop( 'columns', $columns );
        }

        ob_start();

        if ( $products->have_posts() ) {

            do_action( 'electro_before_product_loop_rows' );

            woocommerce_product_loop_start();

            while ( $products->have_posts() ) : $products->the_post();

                if ( $products_count != 0 && ( $products_count % $products_per_view ) == 0 ) {
                    woocommerce_product_loop_end();
                    woocommerce_product_loop_start();
                }

                // wc_get_template_part( 'templates/contents/content', 'product' );
                wc_get_template_part( 'content', 'product' );

                $products_count++;

            endwhile;

            woocommerce_product_loop_end();

            do_action( 'electro_after_product_loop_rows' );
        }

        woocommerce_reset_loop();
        wp_reset_postdata();

        return '<div class="woocommerce columns-' . $columns . ' product product-carousel-rows owl-carousel">' . ob_get_clean() . '</div>';
    }

    public static function get_savings_on_sale( $product, $in = 'amount' ) {
        return electro_get_savings_on_sale( $product, $in );
    }

    /**
     * AJAX add to cart.
     */
    public static function add_to_cart() {
        $product_id        = apply_filters( 'electro_add_to_cart_product_id', absint( $_POST['product_id'] ) );
        $quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
        $variation_id      = empty( $_POST['variation_id'] ) ? 0 : $_POST['variation_id'];
        $variation         = empty( $_POST['variation'] ) ? array() : $_POST['variation'];
        $passed_validation = apply_filters( 'electro_add_to_cart_validation', true, $product_id, $quantity );
        $product_status    = get_post_status( $product_id );
        
        if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {

            do_action( 'woocommerce_ajax_added_to_cart', $product_id );

            if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
                wc_add_to_cart_message( $product_id );
            }

            // Return fragments
            WC_AJAX::get_refreshed_fragments();

        } else {

            // If there was an error adding to the cart, redirect to the product page to show any errors
            $data = array(
                'error'       => true,
                'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
            );

            wp_send_json( $data );

        }

        die();
    }

    /**
     * AJAX total price display.
     */
    public static function accessory_checked_total_price() {
        global $woocommerce;
        $price = empty( $_POST['price'] ) ? 0 : $_POST['price'];
        $price_suffix = empty( $_POST['price_suffix'] ) ? 0 : $_POST['price_suffix'];

        if( $price ) {
            $price_html = wc_price( $price );
            if( $price_suffix ) {
                $suffix = get_option( 'woocommerce_price_display_suffix' );
                if ( strpos( $suffix, '{price_excluding_tax}' ) !== false ) {
                    $price_html .= str_replace( '{price_excluding_tax}', wc_price( $price_suffix ), ' <small class="woocommerce-price-suffix">' . wp_kses_post( $suffix ) . '</small>' );
                } elseif ( strpos( $suffix, '{price_including_tax}' ) !== false ) {
                    $price_html .= str_replace( '{price_including_tax}', wc_price( $price_suffix ), ' <small class="woocommerce-price-suffix">' . wp_kses_post( $suffix ) . '</small>' );
                }
            }
            echo wp_kses_post( $price_html );
        }

        die();
    }
}

Electro_WC_Helper::init();