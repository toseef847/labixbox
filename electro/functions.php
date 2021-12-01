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
  $custom_attrs = array(
    "colors" => $colors,
    "sizes" => $sizes,
  );
  $_SESSION["custom_attrs"] = $custom_attrs;

?>
  <div class="wrap-variation-custom-fields">
    <div class="custom-variation-fields">
      <?php
      foreach ($colors as $key => $color) : ?>
        <div class="wrap-variation-field">
          <label for="variation-color-<?php echo trim($color) ?>"><?php _e($color, 'iconic'); ?></label>
          <input class="input-text" type="number" inputmode="numeric" step="1" min="0" max="12" <?php echo $key == 0 ? 'required' : null; ?> id="variation-color-<?php echo trim($color) ?>" name="variation-color-<?php echo trim($color) ?>" placeholder="<?php _e("Enter quantity for {$color}", 'iconic'); ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="custom-variation-fields">
      <?php
      foreach ($sizes as $key => $size) : ?>
        <div class="wrap-variation-field">
          <label for="variation-size-<?php echo trim($size) ?>"><?php _e($size, 'iconic'); ?></label>
          <input class="input-text" type="number" inputmode="numeric" step="1" min="0" max="12" id="variation-size-<?php echo trim($size) ?>" name="variation-size-<?php echo trim($size) ?>" placeholder="<?php _e("Enter quantity for {$size}", 'iconic'); ?>">
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
  $colors = $_SESSION["custom_attrs"]["colors"];
  $sizes = $_SESSION["custom_attrs"]["sizes"];
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
  $colors = $_SESSION["custom_attrs"]["colors"];
  $sizes = $_SESSION["custom_attrs"]["sizes"];

  foreach ($colors as $color) {
    $post_input = "variation-color-" . trim($color);
    if (array_key_exists($post_input, $cart_item)) {
      $item_data[] = array(
        'key'     => __(trim($color), 'iconic'),
        'value'   => wc_clean($cart_item[$post_input]),
        'display' => '',
      );
    }
  }
  foreach ($sizes as $size) {
    $post_input = "variation-size-" . trim($size);
    if (array_key_exists($post_input, $cart_item)) {
      $item_data[] = array(
        'key'     => __(trim($size), 'iconic'),
        'value'   => wc_clean($cart_item[$post_input]),
        'display' => '',
      );
    }
  }

  return $item_data;
}

add_filter('woocommerce_get_item_data', 'iconic_display_engraving_text_cart', 10, 2);
