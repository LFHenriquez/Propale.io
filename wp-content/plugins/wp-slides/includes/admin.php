<?php
if ( !defined( 'ABSPATH' ) ) exit;

/*----------------------------------------------
Including Files
----------------------------------------------*/
include(PLUGIN_DIR . 'includes/client.php');            // class to create client
include(PLUGIN_DIR . 'includes/admin-table.php');       // class to create admin table
include(PLUGIN_DIR . 'includes/screen-options.php');    // screen options

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action('admin_menu', 'client_plugin_setup_menu');
add_action('admin_enqueue_scripts', 'register_client_styles');
add_action('admin_init', 'bulk_actions_client_page');
add_action('admin_notices', 'display_admin_notification');
add_action('init', 'create_new_client');
add_action('init', 'update_client');

/*----------------------------------------------
Functions
----------------------------------------------*/
function client_plugin_setup_menu()
{
    add_menu_page( 'Clients', 'Clients', 'manage_options', 'clients', 'clients_page', 'dashicons-admin-users', 30);
    $hook = add_submenu_page('clients', 'Clients', 'Clients', 'manage_options', 'clients', 'clients_page');
    add_submenu_page('clients', 'New Client', 'New Client', 'manage_options', 'new_client', 'client_page');
    add_submenu_page(null, 'Client', 'Client', 'manage_options', 'client', 'client_page');
 
    add_screen_options_panel(
        'column-settings',                  //Panel ID
        'Options d\'affichage',             //Panel title. 
        'columns_default_settings_panel',   //The function that generates panel contents.
        array($hook),                       //Pages/screens where the panel is displayed. 
        null,                               //The function that gets triggered when settings are submitted/saved.
        false                               //Auto-submit settings (via AJAX) when they change. 
    );
    add_filter('screen_settings', 'test', 10, 2);
}

function test ($current, $screen)
{
    //$test = $this->get_panels_for_screen($screen->id, $hook_suffix);
    //var_dump($test);
    $current .= columns_default_settings_panel();
    //$current .= $screen->id;
    return  $current;
}

function register_client_styles($hook)
{
    global $clients_screens;
    $screen = get_current_screen();

    $styles = false;
    $scripts = false;

    switch ($screen->id) {
        case 'toplevel_page_clients':
            $styles[] = 'admin-page-client';
            break;

        case 'admin_page_client':
            $styles[] = 'admin-page-settings';
            break;
        
        case 'clients_page_new_client':
            $styles[] = 'admin-page-settings';
            $styles[] = 'auto-complete';
            $scripts[] = 'typeahead.bundle.min';
            $scripts[] = 'typeahead.jquery.min';
            $scripts[] = 'ajax-auto-complete';
            break;
            
        case 'edit-slides-group':
            $styles[] = 'admin-page-slides';
            break;
        
        default:
            break;
    }

    if ($styles)
        foreach ($styles as $css) {
            $url = PLUGIN_URL.'css/'.$css.'.css';
            $path = PLUGIN_DIR.'css/'.$css.'.css';
            wp_register_style($css, $url, false, filemtime($path));
            wp_enqueue_style($css, $url, false, filemtime($path));
        }

    if ($scripts)
        foreach ($scripts as $js) {
            $url = PLUGIN_URL.'js/'.$js.'.js';
            $path = PLUGIN_DIR.'js/'.$js.'.js';
            wp_register_script($js, $url, array('jquery'), filemtime($path));
            wp_enqueue_script($js, $url, array('jquery'), filemtime($path));
        }
}

function bulk_actions_client_page()
{
    if (isset($_REQUEST['page']) &&
        isset($_REQUEST['action']) &&
        isset($_REQUEST['user_id']) &&
        $_REQUEST['page'] == 'clients' &&
        $_REQUEST['user_id'])
        Client_Table::process_bulk_action(); 
}

function clients_page()
{
    $screen = get_current_screen();
    if ($screen->id == 'toplevel_page_clients') { ?>
        <div class="wrap"><h1>Clients
        <a href="<?php echo admin_url('admin.php?page=new_client'); ?>" class="action-button">Ajouter</a></h1>
        <form id="client_form" method="get"><input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
        <p><strong>Liste des étiquettes à appliquer</strong> :
        <?php
        $args = array('hide_empty' => false);
        $terms = get_terms('slides-group', $args);
        if ($terms)
            foreach ($terms as $key => $term) {
                if (isset($_REQUEST['groups_of_slides_checked']))
                    $checked = (in_array($term->slug, explode(',', $_REQUEST['groups_of_slides_checked']))) ? 'checked' : '';
                else
                    $checked = '';
                echo "<input type='checkbox' name='tags[]' value='" . $term->slug . "' " . $checked . ">" . $term->slug;
            }
        $client_table = new Client_Table;
        $client_table->prepare_items();
        echo "</p>";
        foreach (array('orderby','order','paged','per_page') as $value) {
            if (isset($_REQUEST[$value]))
                echo '<input type="hidden" name="' . $value . '" value="' . $_REQUEST[$value] . '"/>';
        }
        $client_table->display();
        echo "</form></div>";
    }
}

function create_new_client() {
    global $admin_notification;
    if (isset($_REQUEST['action']) &&
        $_REQUEST['action'] == 'new_client' &&
        isset($_REQUEST['name']) &&
        isset($_REQUEST['email']) )
    {
        $client = Client::create($_REQUEST['name'], $_REQUEST['email'], $_REQUEST);
        if (!is_wp_error($client)) {
            clean_redirect_wp_admin(array('user_id' => $client->id), 'user-edit.php');
        }
        else {
            $admin_notification = $client->get_error_message();
            do_action('display_admin_notification', $admin_notification);
        }
    }
}

function update_client() {
    global $admin_notification;
    if (isset($_REQUEST['action']) &&
        $_REQUEST['action'] == 'update_client' &&
        isset($_REQUEST['client_id']) )
    {
        $client = new Client($_REQUEST['client_id']);
        if (!is_wp_error($client)) {
            $fields = Client::fields_names();
            $fields_to_modify = array_intersect_key($_REQUEST , array_flip($fields));
            foreach ($fields_to_modify as $item => $value) {
                $client->set_item($item, $value);
            }
            clean_redirect_wp_admin(array('page' => 'client', 'client_id' => $client->id));
        }
        else {
            $admin_notification = $client->get_error_message();
            do_action('display_admin_notification', $admin_notification);
        }
    }
}

function client_page() {
    global $client_info, $wp_query;
    $screen = get_current_screen();
    $client_info = Client::get_fields();
    if (isset($_GET['client_id'])) {
        $client = new Client($_GET['client_id']);
        echo "<h1>Modify user</h1>";
    }
    else {
        $client = false;
        echo "<h1>Create new user</h1>";
    }
    ?>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
            <input type="hidden" name="action" value="<?php echo ($client)? 'update_client': 'new_client';?>"/>
            <?php if($client) echo '<input type="hidden" name="client_id" value="'.$_GET['client_id'].'"/>' ; ?>
            <table class="th-left-align">
                <?php foreach ($client_info as $item) { ?>
                    <tr>
                        <th><label for="<?php echo $item['name']; ?>"><?php echo $item['label']; ?></label></th>
                        <td>
                            <input type="text" name="<?php echo $item['name']; ?>" id="slides_<?php echo $item['name']; ?>" value="<?php if($client) echo $client->get_item($item['name']); ?>" class="regular-text <?php if (isset($item['class'])) echo $item['class']?>" /><br />
                            <p class="description"><?php echo $item['description']; ?></p>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th></th>
                    <td>
                        <input type="submit" id="submit"/>
                    </td>
                </tr>
            </table>
        </form>
<?php
}
 
function get_columns_default_settings() {
    $columns_names = array_keys(Client_Table::columns());
    $columns_hidden = Client_Table::columns_hidden();
    $column_to_display = array();
    foreach ($columns_names as $column_name) {
        $column_to_display[$column_name] = ! in_array($column_name, $columns_hidden);
    }
    return $column_to_display;
}

function columns_default_settings_panel() {
    $defaults = get_columns_default_settings();
    //Output checkboxes 
    $fields = Client_Table::columns();      
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

function display_admin_notification()
{
    global $admin_notification;
    if (isset($admin_notification))
        echo '<br><div id="message" class="error"><p>' . $admin_notification . '</p></div>';
}
