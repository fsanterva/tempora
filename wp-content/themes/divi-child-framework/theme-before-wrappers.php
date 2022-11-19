<?php
/**
 * Template partial used to add content to the page in Theme Builder.
 * Duplicates partial content from header.php in order to maintain
 * backwards compatibility with child themes.
 */

$product_tour_enabled = et_builder_is_product_tour_enabled();
$page_container_style = $product_tour_enabled ? ' style="padding-top: 0px;"' : '';

if(is_page('thank-you') && is_user_logged_in() ) { ?>
    <div class="floating_thankyou_popup">
        <?php echo do_shortcode('[thank_you_popup]'); ?>
    </div> <?php
} ?>

<div class="floating_cart_btn_wrapper">
    <div class="floating_cart_btn_wrapper-item">
        <div class="floating_cart_btn_wrapper-item-btn swapable_cart sidebarBtn">
            <a href="#">
                <span class="et_pb_icon_wrap "><span class="et-pb-icon"></span></span>
            </a>
        </div>
    </div>
</div>
<div class="sidebar__cart_wrapper">

  <div class="sidebar__cart">

    <div class="ajaxcart__loader"></div>

    <div class="sidebar__head">
      <h4>Your Cart</h4>
      <button class="closeSidebarCart"></button>
    </div>

    <?php if(WC()->cart->cart_contents_count > 0) { ?>

      <div class="cart__items">

        <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

          $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
          $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key ); ?>

        <?php if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) { ?>

          <div class="item" data-prodid="<?=$cart_item['product_id']?>" data-cartitemkey="<?=$cart_item_key?>">

            <div class="image__thumb"> <?php
              $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
              $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
              if ( ! $product_permalink ) {
                echo $thumbnail;
              } else {
                printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
              } ?>
            </div>

            <div class="details">

              <h4> <?php
                if ( ! $product_permalink ) {
                  echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
                } else {
                  echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                } ?>
              </h4>

              <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>

            <div class="price"> 
                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.  ?> 
            </div>

              <div class="meta">
                <div class="quantity__price"> <?php
                if ( $_product->is_sold_individually() ) {
                    $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                } else {
                    $product_quantity = woocommerce_quantity_input(
                        array(
                        'input_name'   => "cart[{$cart_item_key}][qty]",
                        'input_value'  => $cart_item['quantity'],
                        'max_value'    => $_product->get_max_purchase_quantity(),
                        'min_value'    => '0',
                        'product_name' => $_product->get_name(),
                        ),
                        $_product, false
                    );
                }
                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); ?>
                </div>
              </div>

              <div class="remove"> <?php
                  echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg enable-background="new 0 0 32 32" height="512" viewBox="0 0 32 32" width="512" xmlns="http://www.w3.org/2000/svg"><path id="XMLID_237_" d="m6 12v15c0 1.654 1.346 3 3 3h14c1.654 0 3-1.346 3-3v-15zm6 13c0 .552-.448 1-1 1s-1-.448-1-1v-9c0-.552.448-1 1-1s1 .448 1 1zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-9c0-.552.448-1 1-1s1 .448 1 1zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-9c0-.552.448-1 1-1s1 .448 1 1z"/><path id="XMLID_243_" d="m27 6h-6v-1c0-1.654-1.346-3-3-3h-4c-1.654 0-3 1.346-3 3v1h-6c-1.103 0-2 .897-2 2v1c0 .552.448 1 1 1h24c.552 0 1-.448 1-1v-1c0-1.103-.897-2-2-2zm-14-1c0-.551.449-1 1-1h4c.551 0 1 .449 1 1v1h-6z"/></svg></a>', esc_url( wc_get_cart_remove_url( $cart_item_key ) ), esc_html__( 'Remove this item', 'woocommerce' ), esc_attr( $product_id ), esc_attr( $_product->get_sku() )), $cart_item_key ); ?>
              </div>

            </div>

          </div>

        <?php } ?>

        <?php } ?>
      </div>

      <div class="sidebar__foot">
        <?php $total = WC()->cart->cart_contents_total; ?>
        <div class="total">
          <label>Subtotal</label>
          <span class="total__price">$<?= number_format((float)$total, 2, '.', ''); ?></span>
        </div>
        <a href="/checkout" class="et_pb_button checkout"><?php echo 'Checkout'; ?></a>
      </div>

    <?php } else { ?>

      <div class="cart__items">
        <label class="emptycart">Your cart is currently empty</label>
      </div>

    <?php } ?>

  </div>

</div>

<div id="page-container"<?php echo et_core_intentionally_unescaped( $page_container_style, 'fixed_string' ); ?>>
