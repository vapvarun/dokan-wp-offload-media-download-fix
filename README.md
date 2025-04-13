# Dokan WP Offload Media Download Fix

A simple WordPress plugin that fixes compatibility issues between Dokan Multivendor Marketplace and WP Offload Media when handling downloadable products.

## Problem

When using Dokan with WP Offload Media for Amazon S3 storage, vendors may experience issues with downloadable product files:
- Download links may lead to error pages after some time
- URLs for downloadable files may not refresh properly
- S3 file URLs may not be handled correctly in the Dokan REST API

This happens because Dokan's REST API ProductController doesn't properly integrate with WP Offload Media's URL handling system for downloadable products.

## Solution

This plugin overrides Dokan's `save_downloadable_files` method to ensure it's fully compatible with WP Offload Media by:

1. Preventing any modification of the original file URLs
2. Allowing WP Offload Media to handle all S3-related URL transformations 
3. Ensuring proper integration with WP Offload Media's URL signing system

## Installation

1. Upload the `dokan-wp-offload-media-download-fix.php` file to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. No configuration needed - it works automatically with your existing setup

## Requirements

- WordPress 4.5+
- WooCommerce 3.0+
- Dokan 3.0+
- WP Offload Media (free or pro)

## How It Works

The plugin hooks into Dokan's class mapping system to override the `ProductController` class with a custom version that properly handles downloadable files with WP Offload Media.

When a vendor uploads or manages downloadable products, this plugin ensures that:
- Original file URLs are preserved during save operations
- WP Offload Media can properly generate signed S3 URLs when needed
- Download links remain functional for the duration of their validity period

## Support

If you encounter any issues, please open an issue on the plugin's GitHub repository.

## Author

Created by [vapvarun](https://github.com/vapvarun)
