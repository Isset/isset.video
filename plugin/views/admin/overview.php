<?php require_once( ABSPATH . 'wp-admin/admin-header.php' ); ?>

<div id="issetVideoOverlay"></div>

<div class="video-publisher-admin">
    <h1 class="video-publisher-flex video-publisher-flex-between">
        <span>Your Videos</span>
    </h1>

    <div class="isset-video-chart-container">
        <div id="issetVideoDash" class="video-publisher-mb-2">
            <?php echo $chart; ?>
        </div>
    </div>

    <div id="isset-video-overview-container" class="isset-video-overview-container"></div>
</div>

