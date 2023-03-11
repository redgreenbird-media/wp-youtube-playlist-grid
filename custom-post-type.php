<?php

// Register custom post type for videos
function register_video_post_type()
{
    $labels = array(
        'name' => _x('Videos', 'Post Type General Name', 'text_domain'),
        'singular_name' => _x('Video', 'Post Type Singular Name', 'text_domain'),
        'menu_name' => __('Videos', 'text_domain'),
        'name_admin_bar' => __('Video', 'text_domain'),
        'archives' => __('Video Archives', 'text_domain'),
        'attributes' => __('Video Attributes', 'text_domain'),
        'parent_item_colon' => __('Parent Video:', 'text_domain'),
        'all_items' => __('All Videos', 'text_domain'),
        'add_new_item' => __('Add New Video', 'text_domain'),
        'add_new' => __('Add New', 'text_domain'),
        'new_item' => __('New Video', 'text_domain'),
        'edit_item' => __('Edit Video', 'text_domain'),
        'update_item' => __('Update Video', 'text_domain'),
        'view_item' => __('View Video', 'text_domain'),
        'view_items' => __('View Videos', 'text_domain'),
        'search_items' => __('Search Video', 'text_domain'),
        'not_found' => __('Not found', 'text_domain'),
        'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
        'featured_image' => __('Featured Image', 'text_domain'),
        'set_featured_image' => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image' => __('Use as featured image', 'text_domain'),
        'insert_into_item' => __('Insert into video', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this video', 'text_domain'),
        'items_list' => __('Videos list', 'text_domain'),
        'items_list_navigation' => __('Videos list navigation', 'text_domain'),
        'filter_items_list' => __('Filter videos list', 'text_domain'),
    );
    $args = array(
        'label' => __('Video', 'text_domain'),
        'description' => __('A custom post type for YouTube videos', 'text_domain'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'taxonomies' => array('category', 'post_tag'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-video-alt3',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'page',
    );
    register_post_type('video', $args);
}
add_action('init', 'register_video_post_type', 0);

// Add custom fields to the video post type
function add_video_meta_boxes()
{
    add_meta_box(
        'video_meta_box',
        __('Video Information', 'text_domain'),
        'video_meta_box_callback',
        'video',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_video_meta_boxes');

// Display the video information meta box
function video_meta_box_callback($post)
{
    wp_nonce_field('save_video_data', 'video_meta_box_nonce');
    $video_id = get_post_meta($post->ID, 'video_id', true);
    $video_title = get_post_meta($post->ID, 'video_title', true);
    $video_thumbnail = get_post_meta($post->ID, 'video_thumbnail', true);
    $video_description = get_post_meta($post->ID, 'video_description', true);

    echo '<p>';
    echo '<label for="video_id">' . __('Video ID', 'text_domain') . '</label>';
    echo '<input type="text" id="video_id" name="video_id" value="' . esc_attr($video_id) . '" size="25">';
    echo '</p>';

    echo '<p>';
    echo '<label for="video_title">' . __('Video Title', 'text_domain') . '</label>';
    echo '<input type="text" id="video_title" name="video_title" value="' . esc_attr($video_title) . '" size="25">';
    echo '</p>';

    echo '<p>';
    echo '<label for="video_thumbnail">' . __('Video Thumbnail', 'text_domain') . '</label>';
    echo '<input type="text" id="video_thumbnail" name="video_thumbnail" value="' . esc_attr($video_thumbnail) . '" size="25">';
    echo '</p>';

    echo '<p>';
    echo '<label for="video_description">' . __('Video Description', 'text_domain') . '</label>';
    echo '<textarea id="video_description" name="video_description" rows="5" cols="30">' . esc_textarea($video_description) . '</textarea>';
    echo '</p>';

    echo '<p class="submit">';
    echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">';
    echo '</p>';

    echo '</form>';
    echo '</div>';
}


// Register Video Thumbnail Meta Box
function add_video_thumbnail_meta_box()
{
    add_meta_box(
        'video_thumbnail',
        __('Video Thumbnail', 'text_domain'),
        'video_thumbnail_callback',
        'video',
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'add_video_thumbnail_meta_box'); // Video Thumbnail Meta Box Callback
function video_thumbnail_callback($post)
{

    // Use nonce for verification
    wp_nonce_field('save_video_thumbnail', 'video_thumbnail_nonce');

    $value = get_post_meta($post->ID, '_video_thumbnail', true);

    echo '<label for="video_thumbnail">';
    _e('Enter a URL for the video thumbnail', 'text_domain');
    echo '</label> ';
    echo '<input type="text" id="video_thumbnail" name="video_thumbnail" value="' . esc_attr($value) . '" size="25">';
}

// Save Video Thumbnail Meta Box Data
function save_video_thumbnail_data($post_id)
{

    // Check if nonce is set
    if (!isset($_POST['video_thumbnail_nonce'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['video_thumbnail_nonce'], 'save_video_thumbnail')) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize user input.
    $video_thumbnail = sanitize_text_field($_POST['video_thumbnail']);

    // Update the meta field in the database.
    update_post_meta($post_id, '_video_thumbnail', $video_thumbnail);
}
add_action('save_post', 'save_video_thumbnail_data');
