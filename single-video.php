<?php
/*
Template Name: Single Video
*/

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) :
            the_post();

            $video_id = get_post_meta(get_the_ID(), 'video_id', true);
            $video_title = get_the_title();
            $video_thumbnail = get_post_meta(get_the_ID(), 'video_thumbnail', true);

            if (!empty($video_id)) {
        ?>
                <div class="youtube-video-single">
                    <iframe src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <h2><?php echo esc_html($video_title); ?></h2>
                    <p><img src="<?php echo esc_url($video_thumbnail); ?>" alt="<?php echo esc_attr($video_title); ?>"></p>
                </div>
        <?php
            }
        endwhile;
        ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
