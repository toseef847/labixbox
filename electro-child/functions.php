<?php

/**
 * Electro Child
 *
 * @package electro-child
 */

/**
 * Include all your custom code here
 */

add_action('woocommerce_single_product_summary', 'woocommerce_total_product_price', 31);
function woocommerce_total_product_price()
{
    global $woocommerce, $product;
    $terms = get_the_terms($product->get_id(), 'product_cat');
    $categories = array();
    foreach ($terms as $term) {
        array_push($categories, $term->name);
    }
    if (in_array('Clothing', $categories)) {
        //     let's setup our divs
        echo sprintf('<div id="product_total_price" style="margin-bottom:20px;">%s %s</div>', __('Product Total:', 'woocommerce'), '<span class="price">' . $product->get_price() . '</span>');
    ?>
        <script>
            jQuery(function($) {
                var price = <?php echo $product->get_price(); ?>,
                    currency = '<?php echo get_woocommerce_currency_symbol(); ?>';

                $('[name=quantity]').change(function() {
                    if (!(this.value < 1)) {

                        var product_total = parseFloat(price * this.value);

                        $('#product_total_price .price').html(currency + product_total.toFixed(2));

                    }
                });
            });
        </script>
<?php
    }
}
