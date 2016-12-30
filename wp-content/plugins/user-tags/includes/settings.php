<?php

/*----------------------------------------------
Including Files
----------------------------------------------*/
include(PLUGIN_DIR . 'includes/admin-table.php');       // class to create admin table
include(PLUGIN_DIR . 'includes/screen-options.php');     // screen options

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action('admin_menu', 'user_tags_plugin_setup_menu');
add_action('admin_enqueue_scripts', 'register_user_tags_styles');

/*----------------------------------------------
Functions
----------------------------------------------*/
function user_tags_plugin_setup_menu()
{
    $hook = add_menu_page('Etiquettes', 'Etiquettes', 'manage_options', 'user-tags-plugin', 'user_tags_page');
    add_submenu_page( 'user-tags-plugin', 'Etiquettes', 'Etiquettes', 'manage_options', 'user-tags-plugin', 'user_tags_page');
    add_submenu_page( 'user-tags-plugin', 'Préférences', 'Préférences', 'manage_options', 'user-tags-plugin-settings', 'user_tags_setting_page');
 
    add_screen_options_panel(
        'column-settings',                  //Panel ID
        'Options d\'affichage',             //Panel title. 
        'columns_default_settings_panel',   //The function that generates panel contents.
        array($hook),                       //Pages/screens where the panel is displayed. 
        null,        //The function that gets triggered when settings are submitted/saved.
        true                                //Auto-submit settings (via AJAX) when they change. 
    );
}

function register_user_tags_styles($hook)
{
    wp_register_style('user_tags_style', PLUGIN_URL . 'css/style.css', array(), filemtime( PLUGIN_DIR . 'css/style.css'));
    wp_enqueue_style('user_tags_style', PLUGIN_URL . 'css/style.css', array(), filemtime( PLUGIN_DIR . 'css/style.css'));
}

function user_tags_page()
{
    if ($_REQUEST['page'] == 'user-tags-plugin') { ?>
        <div class="wrap"><h1>Etiquettes des invités
        <a href="<?php admin_url('user-new.php'); ?>" class="action-button">Ajouter</a></h1>
        <form id="user_tags_form" method="get"><input type="hidden" name="page" value="user-tags-plugin"/>
        <p><strong>Liste des étiquettes à appliquer</strong> :
    	<?php
        $tags = get_terms(array(
            'taxonomy' => 'post_tag',
            'hide_empty' => false
        ));
        if ($tags)
            foreach ($tags as $key => $tag) {
                $checked = (in_array($tag->slug, explode(',', $_REQUEST['tags_checked']))) ? 'checked' : '';
                echo "<input type='checkbox' name='tags[]' value='" . $tag->slug . "' " . $checked . ">" . $tag->slug;
            }
        $user_tags_table = new User_Tags_Table;
        $user_tags_table->prepare_items();
        echo "</p>";
        foreach (array('orderby','order','paged','per_page') as $value) {
            if ($_REQUEST[$value])
                echo '<input type="hidden" name="' . $value . '" value="' . $_REQUEST[$value] . '"/>';
        }
        $user_tags_table->display();
    	echo "</form></div>";
    }
}

function user_tags_setting_page()
{
    if ($_REQUEST['page'] == 'user-tags-plugin-settings') {
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
 
function get_columns_default_settings() {
    $columns_names = array_keys(User_Tags_Table::columns());
    $columns_hidden = User_Tags_Table::columns_hidden();
    $column_to_display = array();
    foreach ($columns_names as $column_name) {
        $column_to_display[$column_name] = ! in_array($column_name, $columns_hidden);
    }
    return $column_to_display;
}

function columns_default_settings_panel(){
    $defaults = get_columns_default_settings();
     
    //Output checkboxes 
    $fields = User_Tags_Table::columns();      
    $output = '';
    foreach($fields as $field => $legend){
        $esc_field = esc_attr($field);
        $output .= sprintf(
            '<label for="column-%s" style="line-height: 20px;">
                <input type="checkbox" name="column-%s" id="column-%s"%s>
                %s
            </label>',
            $esc_field,
            $esc_field,
            $esc_field,
            ($defaults[$field]?' checked="checked"':''),
            $legend
        );
    }
     
    return $output;
}

function save_new_columns_defaults($params){
    //Get current defaults
    $defaults = get_columns_default_settings();
     
    //Read new values from the submitted form
    foreach($defaults as $field => $old_value){
        if ( isset($params['column-'.$field]) && ($params['column-'.$field] == 'on') ){
            $defaults[$field] = true;
        } else {
            $defaults[$field] = false;
        }
    }
     
    //Store the new defaults
    wp_slides_set_default_settings($defaults);
}
