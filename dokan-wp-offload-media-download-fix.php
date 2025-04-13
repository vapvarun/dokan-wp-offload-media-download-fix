<?php
/**
 * Plugin Name: Dokan S3 Download Fix
 * Description: Fixes downloadable product links with WP Offload Media and Dokan
 * Version: 1.0
 * Author: vapvarun
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
     * 
     * @return WC_Product
     */
    protected function save_downloadable_files( $product, $downloads ) {
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

/**
 * Replace the default Dokan ProductController with our custom one
 */
function dokan_s3_init() {
    // Only run if both Dokan and WP Offload Media are active
    if ( ! class_exists( 'WeDevs\Dokan\REST\ProductController' ) ) {
        return;
    }
    
    // Hook into Dokan's class mapping filter
    add_filter( 'dokan_rest_api_class_map', function( $classes ) {
        $classes['WeDevs\Dokan\REST\ProductController'] = 'Dokan_S3_Product_Controller';
        return $classes;
    }, 20 );
}
add_action( 'plugins_loaded', 'dokan_s3_init', 15 );
