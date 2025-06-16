<?php
/**
 * Plugin Name: Dokan S3 Download Fix
 * Description: Fixes downloadable product links with WP Offload Media and Dokan
 * Version: 1.1
 * Author: vapvarun
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Initialize the plugin only after all plugins are loaded
 */
add_action( 'plugins_loaded', function() {
    // Check if Dokan is active and the required class exists
    if ( ! class_exists( 'WeDevs\Dokan\REST\ProductController' ) ) {
        // Optionally add an admin notice
        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e( 'Dokan S3 Download Fix requires Dokan plugin to be installed and activated.', 'dokan-s3-fix' ); ?></p>
            </div>
            <?php
        });
        return;
    }

    // Only declare the class if Dokan is available
    if ( ! class_exists( 'Dokan_S3_Product_Controller' ) ) {
        /**
         * Class to override Dokan's ProductController
         */
        class Dokan_S3_Product_Controller extends WeDevs\Dokan\REST\ProductController {
            
            /**
             * Save downloadable files for a product - keeps URLs untouched
             * so WP Offload Media can process them correctly
             *
             * @param WC_Product $product    Product instance
             * @param array      $downloads  Downloads data
             * @param int        $deprecated Deprecated since 3.0
             * 
             * @return WC_Product
             */
            protected function save_downloadable_files( $product, $downloads, $deprecated = 0 ) {
                $files = [];
                
                foreach ( $downloads as $key => $file ) {
                    if ( empty( $file['file'] ) ) {
                        continue;
                    }
                    
                    $download = new WC_Product_Download();
                    $download->set_id( $key );
                    
                    // Set name from provided data or get from URL
                    if ( ! empty( $file['name'] ) ) {
                        $download->set_name( $file['name'] );
                    } else {
                        $download->set_name( wc_get_filename_from_url( $file['file'] ) );
                    }
                    
                    // Store the original file URL without modification
                    // WP Offload Media's filter will handle conversion to S3 URL during download
                    $download->set_file( $file['file'] );
                    
                    $files[] = $download;
                }
                
                $product->set_downloads( $files );
                
                return $product;
            }
        }
    }

    // Hook into Dokan's class mapping filter
    add_filter( 'dokan_rest_api_class_map', function( $classes ) {
        // Double-check that our class exists before adding it
        if ( class_exists( 'Dokan_S3_Product_Controller' ) ) {
            $classes['WeDevs\Dokan\REST\ProductController'] = 'Dokan_S3_Product_Controller';
        }
        return $classes;
    }, 20 );
    
}, 10 );

/**
 * Additional safety check for WooCommerce dependency
 */
add_action( 'admin_init', function() {
    if ( ! class_exists( 'WooCommerce' ) && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php _e( 'Dokan S3 Download Fix requires WooCommerce to be installed and activated.', 'dokan-s3-fix' ); ?></p>
            </div>
            <?php
        });
    }
});

/**
 * Check dependencies on activation
 */
register_activation_hook( __FILE__, function() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        wp_die( __( 'Dokan S3 Download Fix requires WooCommerce to be installed and activated.', 'dokan-s3-fix' ) );
    }
});
