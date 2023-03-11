<?php

if (isset($_POST['pull'])) {
    // code to handle the pull button here
    youtube_playlist_grid_pull_videos();
}

function youtube_playlist_grid_pull_videos()
{
    // Check if the form has been submitted and the button was clicked
    if (!isset($_POST['youtube_playlist_grid_pull_videos'])) {
        return;
    }

    $playlist_id = get_option('youtube_playlist_grid_playlist_id');
    if (empty($playlist_id)) {
        return 'Please provide a playlist ID in the plugin settings.';
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

    // Loop through each video in the playlist
    foreach ($data['items'] as $item) {
        $video_id = $item['snippet']['resourceId']['videoId'];
        $video_title = $item['snippet']['title'];
        $video_description = $item['snippet']['description'];
        $video_thumbnail = $item['snippet']['thumbnails']['medium']['url'];

        // Check if a post with the same title already exists
        $existing_post = get_page_by_title($video_title, OBJECT, 'post');
        if ($existing_post) {
            continue;
        }

        // Create a new post for the video
        $post_data = array(
            'post_title' => $video_title,
            'post_content' => '<p><img src="' . $video_thumbnail . '" alt="' . $video_title . '"></p>
<p>' . $video_description . '</p>
<p><a href="https://www.youtube.com/watch?v=' . $video_id . '" target="_blank">Watch on YouTube</a></p>',
            'post_status' => 'publish',
            'post_type' => 'post',
        );
        wp_insert_post($post_data);
    }
}
add_action('admin_init', 'youtube_playlist_grid_pull_videos');
