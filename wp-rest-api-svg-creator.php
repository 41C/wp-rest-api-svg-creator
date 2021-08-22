<?php
/**
 * Plugin Name:       WP REST API SVG Creator
 * Description:       Adds a custom endpoint that allows authenticated users to add SVG's to WordPress' Media Library by posting SVG markup.
 * Version:           1.0.0
 * Author:            Joseph Fusco
 * Author URI:        https://josephfus.co/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       svg-rest-api
 * Domain Path:       /languages
 */

add_action('rest_api_init', function () {
    register_rest_route('svg/v1', '/media/', [
        'methods' => 'POST',
        'callback' => function() {
            $upload_dir = wp_upload_dir();
            $filename = $_POST['title']
                ? time() . '-' . sanitize_title(wp_unslash($_POST['title'])) . '.svg'
                : time() . '-symbol.svg';

            $file = trailingslashit($upload_dir['path']) . $filename;

            $markup = wp_unslash($_POST['markup']);
            $markup = str_replace("&quot;", "'", $markup);
            $markup = preg_replace("/\r|\n/", "", $markup);

            // Specify namespace for SVG so that browsers will render image and not XML.
            $svg = str_replace("<svg ", "<svg xmlns='http://www.w3.org/2000/svg' ", $markup);

            // Create our SVG within the uploads directory.
            file_put_contents($file, $svg);

            $wp_filetype = wp_check_filetype($file, null );

            $attachment = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name($filename),
                'post_content'   => '',
                'post_status'    => 'inherit'
            ];

            $attach_id = wp_insert_attachment($attachment, $file);
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);

            // Handle Alt text.
            $alt_text = $_POST['alt'] ? $_POST['alt'] : '';
            update_post_meta($attach_id, '_wp_attachment_image_alt', $alt_text);

            return $attach_id;
        },
        'args' => [
            'markup' => [
                'required' => true,
                'type' => 'string',
                'description' => __('The SVG markup.', 'svg-rest-api'),
                'validate_callback' => function($param, $request, $key) {
                    // Validate XML
                    libxml_use_internal_errors(true);
                    $sxe = simplexml_load_string($param);

                    return $sxe;
                }
            ],
            'title' => [
                'required' => false,
                'type' => 'string',
                'description' => __('Used to create the filename for an SVG.', 'svg-rest-api'),
            ],
            'alt' => [
                'required' => false,
                'type' => 'string',
                'description' => __('Used to create the filename for an SVG.', 'svg-rest-api'),
            ]
        ]
    ]);
});
