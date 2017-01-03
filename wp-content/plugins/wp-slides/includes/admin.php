<?php
if ( !defined( 'ABSPATH' ) ) exit;

/*----------------------------------------------
Including Files
----------------------------------------------*/
include(PLUGIN_DIR . 'includes/admin-table.php');       // class to create admin table
include(PLUGIN_DIR . 'includes/screen-options.php');    // screen options

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action('admin_menu', 'client_plugin_setup_menu');
add_action('admin_enqueue_scripts', 'register_client_styles');

/*----------------------------------------------
Functions
----------------------------------------------*/
function client_plugin_setup_menu()
{
    $hook = add_menu_page( 'Clients', 'Clients', 'manage_options', 'clients', 'client_page', 'dashicons-admin-users', 30);
    add_submenu_page('clients', 'Clients', 'Clients', 'manage_options', 'clients', 'client_page');
    add_submenu_page('clients', 'New Client', 'New Client', 'manage_options', 'new_client', 'new_client_page');
 
    add_screen_options_panel(
        'column-settings',                  //Panel ID
        'Options d\'affichage',             //Panel title. 
        'columns_default_settings_panel',   //The function that generates panel contents.
        array($hook),                       //Pages/screens where the panel is displayed. 
        null,        //The function that gets triggered when settings are submitted/saved.
        true                                //Auto-submit settings (via AJAX) when they change. 
    );
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

function client_page()
{
    $screen = get_current_screen();
    if ($screen->id == 'toplevel_page_clients') { ?>
        <div class="wrap"><h1>Clients
        <a href="<?php echo admin_url('admin.php?page=new_client'); ?>" class="action-button">Ajouter</a></h1>
        <form id="client_form" method="get"><input type="hidden" name="page" value="client"/>
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
        $client_table = new Client_Table;
        $client_table->prepare_items();
        echo "</p>";
        foreach (array('orderby','order','paged','per_page') as $value) {
            if ($_REQUEST[$value])
                echo '<input type="hidden" name="' . $value . '" value="' . $_REQUEST[$value] . '"/>';
        }
        $client_table->display();
    	echo "</form></div>";
    }
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

function columns_default_settings_panel(){
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

function create_new_client() {
    global $admin_notification;
    if (isset($_REQUEST['action']) &&
        $_REQUEST['action'] == 'new_client' &&
        isset($_REQUEST['name']) &&
        isset($_REQUEST['email']) )
    {
        $user = Client::create($_REQUEST['name'], $_REQUEST['email'], $_REQUEST);
        if (!is_wp_error($user)) {
            clean_redirect_wp_admin(array('user_id' => $user->id), 'user-edit.php');
        }
        else {
            $admin_notification = $user->get_error_message();
            do_action('display_admin_notification', $admin_notification);
        }
    }
}
add_action('init', 'create_new_client');

function display_admin_notification()
{
    global $admin_notification;
    if (isset($admin_notification))
        echo '<br><div id="message" class="error"><p>' . $admin_notification . '</p></div>';
}
add_action( 'admin_notices', 'display_admin_notification');

function new_client_page() {
    global $client_info;
    $screen = get_current_screen();
        $client_info =
            array(
                array(
                    'name' => 'name',
                    'class' => 'typeahead',
                    'label' => 'Client name',
                    'description' => 'Name of the client / Nom du client'
                ),
                array(
                    'name' => 'email',
                    'label' => 'Email',
                    'description' => 'Email address / Adresse email'
                ),
                array(
                    'name' => 'phone',
                    'label' => 'Phone',
                    'description' => 'Phone number / Numéro de téléphone'
                ),
                array(
                    'name' => 'logo_url',
                    'label' => 'Logo',
                    'description' => 'Url of the client\'s logo / Url du logo du client'
                ),
                array(
                    'name' => 'slides',
                    'label' => 'Slides',
                    'description' => 'Slides to display / Slides à afficher'
                )
            );
        ?>
        <h1>Create new user</h1>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
            <input type="hidden" name="action" value="new_client"/>
            <table class="th-left-align">
                <?php foreach ($client_info as $item) { ?>
                    <tr>
                        <th><label for="<?php echo $item['name']; ?>"><?php echo $item['label']; ?></label></th>
                        <td>
                            <input type="text" name="<?php echo $item['name']; ?>" id="<?php echo $item['name']; ?>" value="<?php echo get_option($item['name']); ?>" class="regular-text <?php if (isset($item['class'])) echo $item['class']?>" /><br />
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

/**
* 
*/
class Client extends WP_user
{
    private static $fields =
            array(
                array(
                    'name' => 'name',
                    'class' => 'typeahead',
                    'label' => 'Client name',
                    'description' => 'Name of the client / Nom du client'
                ),
                array(
                    'name' => 'email',
                    'label' => 'Email',
                    'description' => 'Email address / Adresse email'
                ),
                array(
                    'name' => 'phone',
                    'label' => 'Phone',
                    'description' => 'Phone number / Numéro de téléphone',
                    'meta' => true
                ),
                array(
                    'name' => 'logo_url',
                    'label' => 'Logo',
                    'description' => 'Url of the client\'s logo / Url du logo du client',
                    'meta' => true
                ),
                array(
                    'name' => 'slides',
                    'label' => 'Slides',
                    'description' => 'Slides to display / Slides à afficher',
                    'meta' => true
                )
            );
    
    function __construct($id)
    {
        parent::__construct($id);
    }

    public static function create($username, $email, $values_to_create = array())
    {
        $login = strtolower(sanitize_user($username));
        $email = sanitize_email($email);
        $password = wp_generate_password( 12, false );
        
        $result = wp_create_user( $login, $password, $email );

        if (! is_wp_error($result))
        {
            $user_id = $result;
        
            wp_update_user( array(
                'ID' => $user_id,
                'display_name' => $username
                ));
            $instance = new self($user_id);
            $user_guest_role = get_option('user_guest_role', 'guest');
            $instance->set_role($user_guest_role);

            $items = array();
            if (self::$fields)
                foreach (self::$fields as $item) {
                    if (isset($item['meta']) && $item['meta'] == true)
                        $items[] = $item['name'];
                }
        
            $values = array();
            if ($values_to_create)
                foreach ($values_to_create as $key => $value) {
                    if (in_array($key, $items))
                        update_user_meta($user_id, $key, $value);
                }

            return $instance;
        }
        else
            return $result;
    }

    public static function get_fields()
    {
        return $this->fields;
    }
}