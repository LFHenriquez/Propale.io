<?php
if ( !defined( 'ABSPATH' ) ) exit;

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action('admin_menu', 'plugin_settings_setup_menu');
add_action('admin_enqueue_scripts', 'register_settings_page_styles');

/*----------------------------------------------
Functions
----------------------------------------------*/
function plugin_settings_setup_menu()
{
    add_submenu_page( 'options-general.php', 'Proposals', 'Proposals', 'manage_options', 'client_settings', 'client_setting_page');
}

function register_settings_page_styles($hook)
{
    $screen= get_current_screen();
    if ($screen->id == 'settings_page_client_settings') {
        wp_register_style('admin_page_client_style', PLUGIN_URL . 'css/admin-page-settings.css', array(), filemtime( PLUGIN_DIR . 'css/admin-page-settings.css'));
        wp_enqueue_style('admin_page_client_style', PLUGIN_URL . 'css/admin-page-settings.css', array(), filemtime( PLUGIN_DIR . 'css/admin-page-settings.css'));
    }
}

function client_setting_page()
{
    $screen= get_current_screen();
    if ($screen->id == 'settings_page_client_settings') {
        $modified = false;
        foreach (array('user_guest_role','enable_slack_notification','slack_room','slack_webhook') as  $value) {
            if (isset($_REQUEST[$value]) && $_REQUEST[$value] != get_option($value)) {
                update_option($value, $_REQUEST[$value]);
                $modified = true;
            }
        }
        if ($modified)
            clean_redirect_wp_admin();
        ?>
        <h2>Slides plugin</h2>
        <form method="get"><input type="hidden" name="page" value="user-tags-plugin-settings"/>
            <table class="th-left-align">
                <tr>
                    <th><label for="user_guest_role">Role</label></th>
                    <td>
                        <input type="text" name="user_guest_role" id="user_guest_role" value="<?php echo get_option('user_guest_role'); ?>" class="regular-text" /><br />
                        <p class="description">Name of the role of guests / Nom du rôle des invités</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="enable_slack_notification">Activer slack</label></th>
                    <td>
                        <input type="hidden" name="enable_slack_notification" value="false" /> 
                        <input type="checkbox" name="enable_slack_notification" id="enable_slack_notification" value="true" <?php echo (get_option('enable_slack_notification')=='true')? 'checked':''; ?> />
                    </td>
                </tr>
                <tr>
                    <th><label for="slack_room">Salle</label></th>
                    <td>
                        <input type="text" name="slack_room" id="slack_room" value="<?php echo get_option('slack_room'); ?>" class="regular-text" /><br />
                        <p class="description">Slack room or channel / Salle ou conversation slack</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="slack_webhook">Webhook</label></th>
                    <td>
                        <input type="text" name="slack_webhook" id="slack_webhook" value="<?php echo get_option('slack_webhook'); ?>" class="regular-text" /><br />
                        <p class="description">Please enter the slack webhook url / Entrez l'url du webhook de slack</p>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <input type="submit" id="submit"/>
                    </td>
                </tr>
            </table>
        </form>
<?php }
}