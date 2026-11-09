<?php
/**
 * Dashboard banner recommending the PayLexer plugin to WooCommerce stores.
 *
 * Two states are rendered from the same banner:
 *
 * 1. Recommendation — WooCommerce is active and PayLexer is either missing or
 *    installed but inactive. The call to action opens WordPress' own plugin
 *    information modal to install, or activates an installed copy.
 * 2. Confirmation — PayLexer has just been activated by a user who saw the
 *    recommendation, pointing them at the setup screen.
 *
 * The dismissal is stored per user under a vendor neutral meta key so a
 * merchant who says no once is not asked again by any sibling theme.
 *
 * @package Blossom_Feminine
 */

if( ! defined( 'BLOSSOM_FEMININE_PAYLEXER_SLUG' ) ) define( 'BLOSSOM_FEMININE_PAYLEXER_SLUG', 'paylexer' );

if( ! function_exists( 'blossom_feminine_paylexer_meta_key' ) ) :
/**
 * User meta key holding the PayLexer banner state for a single user.
 *
 * @param string $key Either `dismissed` or `prompted`.
 *
 * @return string
 */
function blossom_feminine_paylexer_meta_key( $key ){
    /**
     * Filter the user meta keys used by the PayLexer banner.
     *
     * The defaults are deliberately not theme prefixed so that every theme
     * shipping this banner shares one dismissal per user.
     *
     * @param string $meta_key Meta key.
     * @param string $key      Either `dismissed` or `prompted`.
     */
    return apply_filters( 'blossom_feminine_paylexer_meta_key', 'paylexer_recommendation_' . $key, $key );
}
endif;

if( ! function_exists( 'blossom_feminine_get_paylexer_plugin_file' ) ) :
/**
 * Locate the PayLexer main plugin file relative to the plugins directory.
 *
 * @return string Plugin file, e.g. `paylexer/paylexer.php`, or an empty string when not installed.
 */
function blossom_feminine_get_paylexer_plugin_file(){
    if( ! function_exists( 'get_plugins' ) ){
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugins = get_plugins( '/' . BLOSSOM_FEMININE_PAYLEXER_SLUG );

    if( empty( $plugins ) ){
        return '';
    }

    $keys = array_keys( $plugins );

    return BLOSSOM_FEMININE_PAYLEXER_SLUG . '/' . $keys[0];
}
endif;

if( ! function_exists( 'blossom_feminine_is_paylexer_activated' ) ) :
/**
 * Query PayLexer activation
 *
 * @return bool
 */
function blossom_feminine_is_paylexer_activated(){
    if( defined( 'PAYLEXER_VERSION' ) || class_exists( 'PayLexer\Paylexer' ) ){
        return true;
    }

    $plugin_file = blossom_feminine_get_paylexer_plugin_file();

    if( ! $plugin_file ){
        return false;
    }

    if( ! function_exists( 'is_plugin_active' ) ){
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return is_plugin_active( $plugin_file );
}
endif;

if( ! function_exists( 'blossom_feminine_paylexer_banner_screens' ) ) :
/**
 * Admin screens the PayLexer banner is allowed to appear on.
 *
 * @return array Array of screen IDs.
 */
function blossom_feminine_paylexer_banner_screens(){
    $screens = array(
        'dashboard',                                  // WordPress Dashboard.
        'themes',                                     // Appearance > Themes.
        'plugins',                                    // Plugins.
        'appearance_page_blossom-feminine-dashboard', // Theme dashboard.
        'woocommerce_page_wc-settings',               // WooCommerce > Settings.
        'woocommerce_page_wc-admin',                  // WooCommerce home and analytics.
        'edit-shop_order',                            // Orders list, legacy post storage.
        'woocommerce_page_wc-orders',                 // Orders list, HPOS.
        'edit-product',                               // Products list.
    );

    /**
     * Filter the admin screens that display the PayLexer recommendation banner.
     *
     * @param array $screens Array of screen IDs.
     */
    return apply_filters( 'blossom_feminine_paylexer_banner_screens', $screens );
}
endif;

if( ! function_exists( 'blossom_feminine_paylexer_banner_state' ) ) :
/**
 * Which PayLexer banner, if any, the current request should render.
 *
 * @param bool $check_screen Whether the current admin screen must be allowed. Pass
 *                           false from AJAX, where there is no screen to inspect.
 *
 * @return string `install`, `activate`, `active` or an empty string when hidden.
 */
function blossom_feminine_paylexer_banner_state( $check_screen = true ){
    // Only ever shown to WooCommerce stores, there is no checkout to plug into otherwise.
    if( ! blossom_feminine_is_woocommerce_activated() ){
        return '';
    }

    // The call to action would be a dead end without these capabilities.
    if( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ){
        return '';
    }

    if( is_network_admin() ){
        return '';
    }

    $user_id = get_current_user_id();

    if( ! $user_id || get_user_meta( $user_id, blossom_feminine_paylexer_meta_key( 'dismissed' ), true ) ){
        return '';
    }

    if( $check_screen ){
        $current_screen = get_current_screen();

        if( ! $current_screen || ! in_array( $current_screen->id, blossom_feminine_paylexer_banner_screens(), true ) ){
            return '';
        }
    }

    if( blossom_feminine_is_paylexer_activated() ){
        // Confirm the activation only for users who were asked in the first place,
        // so stores already running PayLexer are never interrupted.
        return get_user_meta( $user_id, blossom_feminine_paylexer_meta_key( 'prompted' ), true ) ? 'active' : '';
    }

    if( blossom_feminine_get_paylexer_plugin_file() ){
        return current_user_can( 'activate_plugins' ) ? 'activate' : '';
    }

    return current_user_can( 'install_plugins' ) ? 'install' : '';
}
endif;

if( ! function_exists( 'blossom_feminine_paylexer_banner' ) ) :
/**
 * Banner recommending PayLexer when WooCommerce is active
*/
function blossom_feminine_paylexer_banner(){
    echo blossom_feminine_paylexer_banner_html( blossom_feminine_paylexer_banner_state() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped at source.
}
endif;
add_action( 'admin_notices', 'blossom_feminine_paylexer_banner' );

if( ! function_exists( 'blossom_feminine_paylexer_banner_html' ) ) :
/**
 * Markup for a given banner state.
 *
 * Kept separate from the hook so the AJAX refresh can re-render the banner in
 * place once PayLexer has been installed from the plugin information modal.
 *
 * @param string $state `install`, `activate`, `active` or an empty string.
 *
 * @return string
*/
function blossom_feminine_paylexer_banner_html( $state ){
    if( ! $state ){
        return '';
    }

    $dismiss_url = wp_nonce_url( add_query_arg( 'blossom_feminine_dismiss_paylexer', '1' ), 'blossom_feminine_paylexer_banner' );
    $logo        = get_template_directory_uri() . '/images/paylexer-logo.png';

    if( 'active' === $state ){
        $heading     = __( 'PayLexer is active. Connect your first gateway', 'blossom-feminine' );
        $description = __( 'Add 125+ payment gateways to your WooCommerce checkout in just a few clicks without writing any code.', 'blossom-feminine' );
        $role        = 'status';
        $label       = __( 'PayLexer setup', 'blossom-feminine' );
        $dismiss     = __( 'Dismiss PayLexer notice', 'blossom-feminine' );
    }else{
        $heading     = __( 'Let customers pay with the gateway they trust: 125+ for WooCommerce', 'blossom-feminine' );
        $description = __( 'Connect Stripe, PayPal, Razorpay, Paystack and more in under 2 minutes. No code, no extra plugins. Your checkout is ready to accept payments right away.', 'blossom-feminine' );
        $role        = 'region';
        $label       = __( 'PayLexer recommendation', 'blossom-feminine' );
        $dismiss     = __( 'Dismiss PayLexer recommendation', 'blossom-feminine' );

        // Remember the ask so the activation can be confirmed later on.
        update_user_meta( get_current_user_id(), blossom_feminine_paylexer_meta_key( 'prompted' ), 1 );
    }

    ob_start(); ?>
    <div class="blossom-feminine-paylexer notice blossom-feminine-paylexer-<?php echo esc_attr( $state ); ?>" data-state="<?php echo esc_attr( $state ); ?>" role="<?php echo esc_attr( $role ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
        <div class="blossom-feminine-paylexer-mark">
            <img src="<?php echo esc_url( $logo ); ?>" width="20" height="14" alt="" />
        </div>
        <div class="blossom-feminine-paylexer-body">
            <h3><?php echo esc_html( $heading ); ?></h3>
            <p><?php echo esc_html( $description ); ?></p>
            <div class="blossom-feminine-paylexer-actions">
                <?php blossom_feminine_paylexer_banner_actions( $state ); ?>
            </div>
        </div>
        <a href="<?php echo esc_url( $dismiss_url ); ?>" class="blossom-feminine-paylexer-dismiss" aria-label="<?php echo esc_attr( $dismiss ); ?>">&#10005;</a>
    </div>
    <?php

    return ob_get_clean();
}
endif;

if( ! function_exists( 'blossom_feminine_paylexer_banner_actions' ) ) :
/**
 * Render the call to action for a given banner state.
 *
 * @param string $state `install`, `activate` or `active`.
*/
function blossom_feminine_paylexer_banner_actions( $state ){
    if( 'active' === $state ){
        printf(
            '<a href="%1$s" class="blossom-feminine-paylexer-button">%2$s</a>',
            esc_url( admin_url( 'admin.php?page=' . BLOSSOM_FEMININE_PAYLEXER_SLUG ) ),
            esc_html__( 'Set up PayLexer', 'blossom-feminine' )
        );

        return;
    }

    if( 'activate' === $state ){
        printf(
            '<a href="%1$s" class="blossom-feminine-paylexer-button" data-busy="%2$s">%3$s</a>',
            esc_url( blossom_feminine_get_activation_url( BLOSSOM_FEMININE_PAYLEXER_SLUG ) ),
            // A literal ellipsis, not &hellip;, because jQuery sets this as text.
            esc_attr__( 'Activating…', 'blossom-feminine' ),
            esc_html__( 'Activate PayLexer', 'blossom-feminine' )
        );
    }else{
        // WordPress' own plugin information modal, the same one the Add Plugins screen opens.
        $details_url = add_query_arg(
            array(
                'tab'       => 'plugin-information',
                'plugin'    => BLOSSOM_FEMININE_PAYLEXER_SLUG,
                'TB_iframe' => 'true',
                'width'     => 772,
                'height'    => 788,
            ),
            self_admin_url( 'plugin-install.php' )
        );

        printf(
            '<a href="%1$s" class="thickbox open-plugin-details-modal blossom-feminine-paylexer-button">%2$s</a>',
            esc_url( $details_url ),
            esc_html__( 'Install PayLexer', 'blossom-feminine' )
        );
    }

    printf(
        '<a href="%1$s" class="blossom-feminine-paylexer-link" target="_blank" rel="noopener noreferrer">%2$s</a>',
        esc_url( 'https://paylexer.com/?utm_source=blossom-feminine&utm_medium=dashboard&utm_campaign=paylexer_banner' ),
        esc_html__( 'Learn more about PayLexer', 'blossom-feminine' )
    );
}
endif;

if( ! function_exists( 'blossom_feminine_paylexer_banner_scripts' ) ) :
/**
 * Enqueue the styles and behaviour used by the PayLexer banner.
*/
function blossom_feminine_paylexer_banner_scripts(){
    $state = blossom_feminine_paylexer_banner_state();

    if( ! $state ){
        return;
    }

    wp_enqueue_style( 'blossom-feminine-admin', get_template_directory_uri() . '/inc/css/admin.css', array(), BLOSSOM_FEMININE_THEME_VERSION );

    if( 'install' === $state ){
        // Powers the plugin information modal and its own Install Now button.
        add_thickbox();
        wp_enqueue_script( 'plugin-install' );
        wp_enqueue_script( 'updates' );
    }

    wp_enqueue_script( 'jquery' );
    wp_add_inline_script(
        'jquery-core',
        'jQuery( function( $ ){
            var nonce = ' . wp_json_encode( wp_create_nonce( 'blossom_feminine_paylexer_banner' ) ) . ',
                sel   = ".blossom-feminine-paylexer",
                busy  = false;

            // Delegated from the document because the banner is replaced in place below.
            $( document ).on( "click", sel + " .blossom-feminine-paylexer-dismiss", function( e ){
                e.preventDefault();
                $( sel ).remove();
                $.post( window.ajaxurl, {
                    action: "blossom_feminine_dismiss_paylexer_banner",
                    nonce: nonce
                } );
            } );

            // Activation is a real page load, so show progress while it happens.
            $( document ).on( "click", sel + " .blossom-feminine-paylexer-button[data-busy]", function(){
                $( this ).addClass( "is-busy" ).text( $( this ).data( "busy" ) );
            } );

            /*
             * Installing from the plugin information modal happens in an iframe, so the
             * banner behind it still reads "Install PayLexer". Re-render it from the
             * server once the state has actually moved on, rather than making the user
             * reload the screen to reach "Activate PayLexer".
             */
            function refresh(){
                var current = $( sel );

                if( busy || ! current.length ){
                    return;
                }

                busy = true;

                $.post( window.ajaxurl, {
                    action: "blossom_feminine_refresh_paylexer_banner",
                    nonce: nonce
                } ).done( function( res ){
                    if( ! res || ! res.success ){
                        return;
                    }

                    // Leave the markup alone while the state is unchanged, so core keeps
                    // its own handlers bound to the existing modal link.
                    if( res.data.state === ( current.attr( "data-state" ) || "" ) ){
                        return;
                    }

                    if( res.data.html ){
                        current.replaceWith( res.data.html );
                    }else{
                        current.remove();
                    }
                } ).always( function(){
                    busy = false;
                } );
            }

            // Fires on body when the plugin information modal is dismissed.
            $( document ).on( "thickbox:removed", refresh );

            // The iframe also reports the install directly, except on the Dashboard,
            // where core declines to post the message.
            $( window ).on( "message", function( e ){
                var data;

                try {
                    data = JSON.parse( e.originalEvent.data );
                } catch( err ) {
                    return;
                }

                if( data && "paylexer" === data.slug && data.status ){
                    refresh();
                }
            } );
        } );'
    );
}
endif;
add_action( 'admin_enqueue_scripts', 'blossom_feminine_paylexer_banner_scripts' );

if( ! function_exists( 'blossom_feminine_dismiss_paylexer_banner' ) ) :
/**
 * Store the dismissal when JavaScript is unavailable and the link is followed.
*/
function blossom_feminine_dismiss_paylexer_banner(){
    if( ! isset( $_GET['blossom_feminine_dismiss_paylexer'] ) ){
        return;
    }

    if( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'blossom_feminine_paylexer_banner' ) ){
        return;
    }

    if( ! get_current_user_id() ){
        return;
    }

    update_user_meta( get_current_user_id(), blossom_feminine_paylexer_meta_key( 'dismissed' ), 1 );
}
endif;
add_action( 'admin_init', 'blossom_feminine_dismiss_paylexer_banner' );

if( ! function_exists( 'blossom_feminine_dismiss_paylexer_banner_ajax' ) ) :
/**
 * Store the dismissal triggered by the banner close button.
*/
function blossom_feminine_dismiss_paylexer_banner_ajax(){
    check_ajax_referer( 'blossom_feminine_paylexer_banner', 'nonce' );

    if( ! get_current_user_id() ){
        wp_send_json_error( array( 'message' => esc_html__( 'Sorry, you are not allowed to do that.', 'blossom-feminine' ) ), 403 );
    }

    update_user_meta( get_current_user_id(), blossom_feminine_paylexer_meta_key( 'dismissed' ), 1 );

    wp_send_json_success();
}
endif;
add_action( 'wp_ajax_blossom_feminine_dismiss_paylexer_banner', 'blossom_feminine_dismiss_paylexer_banner_ajax' );

if( ! function_exists( 'blossom_feminine_refresh_paylexer_banner_ajax' ) ) :
/**
 * Return the banner as it stands now, so the screen can pick up an install that
 * happened inside the plugin information modal without being reloaded.
*/
function blossom_feminine_refresh_paylexer_banner_ajax(){
    check_ajax_referer( 'blossom_feminine_paylexer_banner', 'nonce' );

    if( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ){
        wp_send_json_error( array( 'message' => esc_html__( 'Sorry, you are not allowed to do that.', 'blossom-feminine' ) ), 403 );
    }

    // get_plugins() caches within the request, but this is a fresh one, so the
    // newly installed plugin directory is picked up.
    $state = blossom_feminine_paylexer_banner_state( false );

    wp_send_json_success(
        array(
            'state' => $state,
            'html'  => blossom_feminine_paylexer_banner_html( $state ),
        )
    );
}
endif;
add_action( 'wp_ajax_blossom_feminine_refresh_paylexer_banner', 'blossom_feminine_refresh_paylexer_banner_ajax' );
