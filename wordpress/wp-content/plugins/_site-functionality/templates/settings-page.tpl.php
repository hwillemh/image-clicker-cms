<?php
if (!current_user_can('administrator')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
?>

<div class="wrap">
hello
    <?php if (isset($_GET['message'])) : ?>
        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
            <p><strong><?= $_GET['message'] ?></strong>
            </p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>

        </div>



    <?php endif; ?>
    <?php if (isset($_GET['error'])) : ?>
        <div id="setting-error-settings_updated" class="notice-error settings-error notice is-dismissible">
            <p><strong><?= $_GET['error'] ?></strong>
            </p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>

        </div>



    <?php endif; ?>

    
</div>