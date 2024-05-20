<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class SMG_Settings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu_settings_pages'));
        add_action('admin_action_import_ticketmaster', [$this, "admin_action_import_ticketmaster"]);
        /*add_action('admin_action_import_resources', array($this, 'import_resources'));
        add_action('admin_action_process_resources', array($this, 'process_resources'));
        add_action('admin_action_process_collections', array($this, 'process_collections'));
        add_action('admin_action_process_artists', array($this, 'process_artists'));
        add_filter('upload_mimes', array($this, 'mime_types'), 10, 1);
        add_action('admin_action_add_resources_tax', array($this, 'add_resources_tax'));
        add_action('admin_action_clear_survey_entries', [$this, "admin_action_clear_survey_entries"]);
        add_action('admin_action_clear_survey_cache', [$this, "admin_action_clear_survey_cache"]);
        add_action('admin_action_complete_surveys', [$this, "admin_action_complete_surveys"]);
        */
        add_action('admin_init', [$this, 'remove_default_admin_stylesheets']);
    }
    function remove_default_admin_stylesheets()
    {
        wp_deregister_style('load-styles');
    }


    function mime_types($mime_types)
    {
        $mime_types['csv'] = 'text/csv';
        // d($mime_types);
        return $mime_types;
    }
    function admin_menu_settings_pages()
    {
        add_menu_page('Import from Ticketmaster', 'TownHall Settings', 'manage_options', 'theme-options', [$this, 'readme_page']);
        //add_submenu_page('theme-options', 'Import Resources', 'Import Resources', 'administrator', 'resources-import', array($this, 'resources_import_display'));
        //add_submenu_page('theme-options', 'Process Resources', 'Process Resources', 'administrator', 'resources-process', array($this, 'resources_process_display'));
        //add_submenu_page('theme-options', 'Process Artists', 'Process Artists', 'administrator', 'process-artists', array($this, 'process_artists_display'));
        //add_submenu_page('theme-options', 'Survey', 'Survey', 'administrator', 'add_survey_page', array($this, 'add_survey_page'));
        //add_submenu_page('theme-options', 'Add Resources Taxonomy', 'Add Resources Taxonomy', 'administrator', 'add_resources_tax', array($this, 'add_resources_tax_page'));
    }
    function readme_page()
    {
?>
        <div class="readme">
            <h1>Read Me</h1>
            <h2><strong>Pages, Menu and templates</strong></h2>
            <h3>Navigation</h3>
            <ul>
                <li>For pages to appear in the menu, they need to be added to the Wordpress Menu</li>
                <li>This is also where the images for the Main Menu Buckets can be changed.</li>
            </ul>
            <h3>Regular Pages</h3>
            <ul>
                <li>Newly added pages will pick up the default template.</li>
                <li>There are a few custom templates. Content for these pages can be changed within the confines of the current template.</li>

                <ul>
                    <li>All event pages.</li>
                    <li>History</li>
                    <li>Leadership & Staff</li>
                    <li>Contact us</li>
                    <li>Education Overview Page (sub pages can be added here)</li>
                    <li>FAQs</li>
                    <li>Directions & Parking</li>
                    <li>Neighborhood Partners</li>
                    <li>Major Sponsors</li>
                    <li>Membership & Benefits</li>
                    <li>Galas & Special Events Overview (sub pages can be added here)</li>
                </ul>

            </ul>

            <h3>Event collection pages</h3>
            <p>(series) can be added as follows:</p>
            <ul>
                <li>A series category needs to be added</li>
                <li>Create a new page, create a new series category, associate the page with the category</li>
                <li>These will pick up the same template as Salomon Series: Deck text, intro text, then the events organized by season</li>
            </ul>

            <h3>Event Page</h3>
            <ul>
                <li>Content for these pages can be managed with the form.</li>
                <li>Additional text and buttons can be added in the sidebar</li>
                <li>Additional content can be added below the main content:</li>
                <ul>
                    <li>Text</li>
                    <li>Image(s)</li>
                    <li>Video (use vimeo or youtube)</li>
                    <li>Accordion</li>
                </ul>

            </ul>

            </div=iv>
        <?php
    }
    function import_from_tm_page()
    {
        ob_start();
        include SMG_DIR . "/templates/settings-page.tpl.php";
        ob_get_flush();
        ?>
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <br>
            <br>
            <form action='<?php echo admin_url('admin.php'); ?>' method='post' enctype="multipart/form-data">
                <input type='hidden' name='action' value='import_ticketmaster' />
                <div class='form-item'>
                    <?php submit_button("Import from Ticketmaster", 'primary', 'import_ticketmaster'); ?>
                </div>
            </form>
        <?php
    }
    function admin_action_import_ticketmaster()
    {
        $events = SMG_TicketmasterAPI::get_events();
        $i = 0;
        foreach ($events->_embedded->events as $tmEvent) {
            $images = array_filter($tmEvent->images, function ($obj) {
                return strpos($obj->url, "TABLET_LANDSCAPE_LARGE_16_9");
            });
            $altImages = array_filter($tmEvent->images, function ($obj) {
                return strpos($obj->url, "TABLET_LANDSCAPE_3_2");
            });
            //debug_log( end($images)->url );
            //SMG_Image_Upload::upload_image(end($images)->url, 0);
            $data = [
                "ticketmasterId" => $tmEvent->id,
                "tmEventData" => $tmEvent,
                "image" => ["imageUrl" => end($images)->url],
                "altImage" => ["imageUrl" => end($altImages)->url,  "displayType" => "cropped",],
                "title" => $tmEvent->name,
                "eventType" => "ticketmaster",

            ];
            $content = sprintf('<!-- wp:tth/event-form %s /-->', json_encode($data));
            $event = [
                "post_type" => "event",
                "post_title" => $tmEvent->name,
                "post_date" => $tmEvent->dates->start->localDate . "T" . $tmEvent->dates->start->localTime,
                "post_status" => "published",
                "post_content" => $content,
            ];
            wp_insert_post($event);
            //break;
        }
        $redirect_url = $_SERVER['HTTP_REFERER'];
        if ($events == false) {
            $redirect_url = add_query_arg(array('error' => urlencode("An error occured.")), $redirect_url);
        } else {
            $redirect_url = add_query_arg(array('message' => urlencode(count($events->_embedded->events) . " Events were found.")), $redirect_url);
        }
        wp_redirect($redirect_url);
        exit;
    }
    function wps_theme_func()
    {
    }
    function resources_import_display()
    {
        if (!current_user_can('administrator')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
            <div class="wrap">
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
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <br>
                <br>
                <form action='<?php echo admin_url('admin.php'); ?>' method='post' enctype="multipart/form-data">
                    <input type='hidden' name='action' value='import_resources' />
                    <div class='form-item'>
                        <label>Upload an excel file</label><br><br>
                        <input type='file' name='excel_file_upload' id='excel_file_upload' />
                    </div>
                    <div class='form-item'>
                        <?php submit_button("Import", 'primary', 'import_resources'); ?>
                    </div>
                </form>
            </div>
        <?php
    }
    function resources_process_display()
    {
        if (!current_user_can('administrator')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
            <div class="wrap">
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
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <br>
                <br>
                <form action='<?php echo admin_url('admin.php'); ?>' method='post' enctype="multipart/form-data">
                    <input type='hidden' name='action' value='process_resources' />
                    <div class='form-item'>
                        <?php submit_button("Process Resources", 'primary', 'process_resources'); ?>
                    </div>
                </form>
                <form action='<?php echo admin_url('admin.php'); ?>' method='post' enctype="multipart/form-data">
                    <input type='hidden' name='action' value='process_collections' />
                    <div class='form-item'>
                        <?php submit_button("Process Collections", 'primary', 'process_collections'); ?>
                    </div>
                </form>
            </div>
        <?php
    }
    function process_artists_display()
    {
        if (!current_user_can('administrator')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
            <div class="wrap">
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
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <br>
                <br>
                <form action='<?php echo admin_url('admin.php'); ?>' method='post' enctype="multipart/form-data">
                    <input type='hidden' name='action' value='process_artists' />
                    <div class='form-item'>
                        <?php submit_button("Process Artists", 'primary', 'process_artists'); ?>
                    </div>
                </form>
            </div>
        <?php
    }
    function add_resources_tax_page()
    {
        if (!current_user_can('administrator')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
            <div class="wrap">
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
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <br>
                <br>
                <form action='<?php echo admin_url('admin.php'); ?>' method='post' enctype="multipart/form-data">
                    <input type='hidden' name='action' value='add_resources_tax' />
                    <div class='form-item'>
                        <?php submit_button("Add Taxonomy to Resources", 'primary', 'add_resources_tax'); ?>
                    </div>
                </form>
            </div>
    <?php
    }
}
