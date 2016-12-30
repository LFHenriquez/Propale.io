<?php

/*----------------------------------------------
Including Files
----------------------------------------------*/
include(PLUGIN_DIR . 'includes/admin-table.php');       // class to create admin table
include(PLUGIN_DIR . 'includes/screen-options.php');    // screen options
include(PLUGIN_DIR . 'includes/settings.php');          // settings page for the extension

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
    $hook = add_menu_page( 'Clients', 'Clients', 'manage_options', 'client', 'client_page', 'dashicons-admin-users', 30);
    add_submenu_page( 'options-general.php', 'Proposals', 'Proposals', 'manage_options', 'client-settings', 'client_setting_page');
 
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
    wp_register_style('client_style', PLUGIN_URL . 'css/style.css', array(), filemtime( PLUGIN_DIR . 'css/style.css'));
    wp_enqueue_style('client_style', PLUGIN_URL . 'css/style.css', array(), filemtime( PLUGIN_DIR . 'css/style.css'));
}

function client_page()
{
    if ($_REQUEST['page'] == 'client') { ?>
        <div class="wrap"><h1>Etiquettes des invités
        <a href="<?php echo admin_url('user-new.php'); ?>" class="action-button">Ajouter</a></h1>
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
