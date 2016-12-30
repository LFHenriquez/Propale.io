<?php

/*----------------------------------------------
Functions
----------------------------------------------*/
function client_setting_page()
{
    if ($_REQUEST['page'] == 'client-settings') {
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
        <h3>User Tags Plugin</h3>
        <form method="get"><input type="hidden" name="page" value="user-tags-plugin-settings"/>
            <table class="th-left-align">
                <tr>
                    <th><label for="user_guest_role">Role</label></th>
                    <td>
                        <input type="text" name="user_guest_role" id="user_guest_role" value="<?php echo get_option('user_guest_role'); ?>" class="regular-text" /><br />
                        <span class="description">Name of the role of guests / Nom du rôle des invités</span>
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
                        <span class="description">Slack room or channel / Salle ou conversation slack</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="slack_webhook">Webhook</label></th>
                    <td>
                        <input type="text" name="slack_webhook" id="slack_webhook" value="<?php echo get_option('slack_webhook'); ?>" class="regular-text" /><br />
                        <span class="description">Please enter guest logo (url) / Entrez l'url du logo de l'invité (en les séparants par des virgules sans espace après la virgule)</span>
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