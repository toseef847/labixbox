<?php

/**
 * electro engine room
 *
 * @package electro
 */

/**
 * Initialize all the things.
 */
require get_template_directory() . '/inc/init.php';

/**
 * Note: Do not add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * http://codex.wordpress.org/Child_Themes
 */

/**
 * Enqueue custom stylesheets and scripts.
 */
function add_custom_scripts()
{
  wp_enqueue_style('labisbox-css', get_template_directory_uri() . '/assets/css/custom.css');
  // wp_enqueue_script('script-name', get_template_directory_uri() . '/js/example.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'add_custom_scripts');


/**
 * Add attributes fields on product page
 */
function iconic_output_engraving_field()
{
  global $product;
  $terms = get_the_terms($product->get_id(), 'product_cat');
  $categories = array();
  foreach ($terms as $term) {
    array_push($categories, $term->name);
  }
  if (!in_array('Clothing', $categories)) {
    return;
  }
  $colors = explode(",", $product->get_attribute('pa_color'));
  $sizes = explode("|", $product->get_attribute('size'));

  // $colors = wc_get_product_terms($product->id, 'pa_color', array('fields' => 'names'));
  // $sizes = wc_get_product_terms($product->id, 'size', array('fields' => 'names'));
  // foreach ($product->get_attributes() as $taxonomy => $attribute_obj) {
  // Get the attribute label
  // echo "<pre>";
  // var_dump($sizes);
  // echo "</pre>";
  // $attribute_label_name = wc_attribute_label($taxonomy);

  // Display attribute labe name
  // echo '<p>' . $attribute_label_name . '</p>';
  // }

?>
  <div class="wrap-variation-custom-fields">
    <div class="custom-variation-fields">
      <?php
      foreach ($colors as $key => $color) : ?>
        <div class="wrap-variation-field">
          <label for="variation-color-<?php echo trim($color) ?>"><?php _e($color, 'iconic'); ?></label>
          <input class="input-text" type="number" inputmode="numeric" step="1" min="1" max="12" id="variation-color-<?php echo trim($color) ?>" name="variation-color-<?php echo $color ?>" placeholder="<?php _e("Enter quantity for {$color}", 'iconic'); ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="custom-variation-fields">
      <?php
      foreach ($sizes as $key => $size) : ?>
        <div class="wrap-variation-field">
          <label for="variation-size-<?php echo trim($size) ?>"><?php _e($size, 'iconic'); ?></label>
          <input class="input-text" type="number" inputmode="numeric" step="1" min="1" max="12" id="variation-size-<?php echo trim($size) ?>" name="variation-size-<?php echo $size ?>" placeholder="<?php _e("Enter quantity for {$size}", 'iconic'); ?>">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php
}

add_action('woocommerce_before_add_to_cart_button', 'iconic_output_engraving_field', 10);


/**
 * Add custom fields' data with `add to cart` session
 */
function iconic_add_engraving_text_to_cart_item($cart_item_data, $product_id, $variation_id)
{
  // global $product;
  $colors = explode(",", get_post_meta($product_id, 'pa_color', true));
  $sizes = explode(",", get_post_meta($product_id, 'size', true));
  // $sizes = explode("|", $product->get_attribute('size'));
  foreach ($colors as $color) {
    $post_input = "variation-color-" . trim($color);
    $color_quantity = filter_input(INPUT_POST, $post_input);
    if (!empty($color_quantity)) {
      $cart_item_data[$post_input] = $color_quantity;
    }
    // return $cart_item_data;
  }
  foreach ($sizes as $size) {
    $post_input = "variation-size-" . trim($size);
    $size_quantity = filter_input(INPUT_POST, $post_input);
    if (!empty($size_quantity)) {
      $cart_item_data[$post_input] = $size_quantity;
    }
    // return $cart_item_data;
  }

  // if (empty($engraving_text)) {
  //   return $cart_item_data;
  // }

  // $cart_item_data['iconic-engraving'] = $engraving_text;

  return $cart_item_data;
}

add_filter('woocommerce_add_cart_item_data', 'iconic_add_engraving_text_to_cart_item', 10, 3);


/**
 * Display fields data on `cart` page.
 */
function iconic_display_engraving_text_cart($item_data, $cart_item)
{
  $product_id = $cart_item["product_id"];
  $colors = explode(",", get_post_meta($product_id, 'pa_color', true));
  $sizes = explode(",", get_post_meta($product_id, 'size', true));

  foreach ($colors as $color) {
    $post_input = "variation-color-" . trim($color);
    if (in_array($post_input, $cart_item)) {
      $item_data[] = array(
        'key'     => __(trim($color), 'iconic'),
        'value'   => wc_clean($cart_item[$post_input]),
        'display' => '',
      );
    }
    // return $cart_item_data;
  }
  foreach ($sizes as $size) {
    $post_input = "variation-size-" . trim($size);
    if (in_array($post_input, $cart_item)) {
      $item_data[] = array(
        'key'     => __(trim($size), 'iconic'),
        'value'   => wc_clean($cart_item[$post_input]),
        'display' => '',
      );
    }
    // return $cart_item_data;
  }
  // if (empty($cart_item['iconic-engraving'])) {
  //   return $item_data;
  // }

  // $item_data[] = array(
  //   'key'     => __('Engravings', 'iconic'),
  //   'value'   => wc_clean($cart_item['iconic-engraving']),
  //   'display' => '',
  // );

  return $item_data;
}

// add_filter('woocommerce_get_item_data', 'iconic_display_engraving_text_cart', 10, 2);
