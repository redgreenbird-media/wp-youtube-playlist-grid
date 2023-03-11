<?php
/*
 * Plugin Name: YouTube Playlist Grid
 * Plugin URI: https://redgreenbird.com
 * Description: A plugin to display a YouTube playlist in a 3-column grid.
 * Version: 1.0
 * Author: redgreenbird GmbH
 * Author URI: https://redgreenbird.com
 * License: GPL3
 */

require_once('custom-post-type.php');
require_once('auto-puller.php');

add_shortcode('youtube_playlist_grid', 'youtube_playlist_grid_shortcode');

function youtube_playlist_grid_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'playlist_id' => '',
        ),
        $atts
    );

    $playlist_id = $atts['playlist_id'];
    if (empty($playlist_id)) {
        return 'Please provide a playlist ID.';
    }

    $api_key = get_option('youtube_playlist_grid_api_key');
    if (empty($api_key)) {
        return 'Please enter a YouTube API key in the plugin settings.';
    }

    $response = wp_remote_get('https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId=' . $playlist_id . '&key=' . get_option('youtube_playlist_grid_api_key'));

    if (is_wp_error($response)) {
        return 'Error retrieving data from YouTube API.';
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($data['items'])) {
        return 'No data found for this playlist.';
    }

    $output = '<div class="youtube-playlist-grid">';
    $count = 0;
    foreach ($data['items'] as $item) {
        $video_id = $item['snippet']['resourceId']['videoId'];
        $video_title = $item['snippet']['title'];
        $video_thumbnail = $item['snippet']['thumbnails']['medium']['url'];

        if ($count % 3 == 0) {
            $output .= '<div class="row">';
        }

        $output .= '<div class="col-4">';
        $output .= '<div class="youtube-video">';
        $output .= '<a href="https://www.youtube.com/watch?v=' . $video_id . '" target="_blank">';
        $output .= '<img src="' . $video_thumbnail . '" alt="' . $video_title . '">';
        $output .= '<p>' . $video_title . '</p>';
        $output .= '</a>';
        $output .= '</div>';
        $output .= '</div>';

        if (
            $count % 3 == 2 || $count == count($data['items']) - 1
        ) {
            $output .= '</div>';
        }
        $count++;
    }
    $output .= '</div>';

    return $output;
}



function youtube_playlist_grid_register_settings()
{
    add_options_page('YouTube Playlist Grid Settings', 'YouTube Playlist Grid', 'manage_options', 'youtube-playlist-grid', 'youtube_playlist_grid_settings_page');
}
add_action('admin_menu', 'youtube_playlist_grid_register_settings');

function youtube_playlist_grid_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['youtube_playlist_grid_api_key']) && !empty($_POST['youtube_playlist_grid_api_key'])) {
        update_option('youtube_playlist_grid_api_key', sanitize_text_field($_POST['youtube_playlist_grid_api_key']));
        $message = 'API Key updated successfully.';
    }

    $api_key = get_option('youtube_playlist_grid_api_key');
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php if (isset($message)) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">API Key</th>
                    <td>
                        <input type="text" name="youtube_playlist_grid_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <p class="description">Enter your YouTube API key.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save'); ?>
            <input type="submit" name="pull" value="Pull" class="button-primary">
        </form>
    </div>

<?php
}




// add_action('wp_enqueue_scripts', 'youtube_playlist_grid_style');

/* function youtube_playlist_grid_style()
{
wp_enqueue_style('youtube-playlist-grid-style', plugin_dir_url(FILE) . 'youtube-playlist-grid.css');
} */