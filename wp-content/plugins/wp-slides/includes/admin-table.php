<?php

/*----------------------------------------------
Including Files
----------------------------------------------*/
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/*----------------------------------------------
Class
----------------------------------------------*/
class Client_Table extends WP_List_Table
{
    
    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'user', //Singular label
            'plural' => 'users', //plural label, also this well be one of the table css class
            'ajax' => false //We won't support Ajax for this table
        ));
    }
    
    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    public function extra_tablenav($which)
    {
        if ($which == "top") {
            // echo "<div id='filter-div' class='alignright'><input type='text' name='search-group-of-slide' placeholder='Groupe de Slides'></div>";
            //The code that goes before the table is here
        }
        if ($which == "bottom") {
            //The code that goes after the table is there
        }
    }
    
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case "user_id":
            case "user_email":
            case "user_phone":
            case "groups_of_slides":
            case "mail_sent":
            case "mail_opened":
            case "last_login":
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }
    
    public function column_user_name($item)
    {
        //Build row actions
        $actions = array(
            'edit' => sprintf('<a href="%sadmin.php?page=client&client_id=%s">Modifier</a>', admin_url(), $item['user_id']),
            'delete' => sprintf('<a href="%susers.php?action=remove&user=%s&_wpnonce=%s">Supprimer</a>', admin_url(), $item['user_id'], wp_create_nonce('bulk-users'))
        );
        
        $logo = ($item['user_logo']) ? $item['user_logo'] : plugins_url("../img/avatar.png", __FILE__);
        //Return the title contents
        return sprintf('<img alt="" src="%1$s" srcset="%2$s" class="avatar avatar-32 photo" height="32" width="32">
                <strong>%3$s</strong>%4$s', $logo, $logo, $item['user_name'], $this->row_actions($actions));
    }
    
    public function column_user_login($item)
    {
        return sprintf('<a href=%1$s>lien</a>', create_autologin_link($item['user_id']));
    }
    
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="user_id[]" value="%1$s" />', $item['user_id'] //The value of the checkbox should be the record's id
            );
    }
    
    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public static function columns()
    {
    	return array(
            'user_id' 		=> 'ID',
            'user_name' 	=> 'Nom',
            'user_email' 	=> 'Email',
            'user_login' 	=> 'Lien',
            'groups_of_slides' 	=> 'Groupe de Slides',
            'mail_sent'     => 'Mail envoyé',
            'mail_opened' 	=> 'Mail ouvert',
            'last_login' 	=> 'Dernière connexion',
            'user_phone' 	=> 'Téléphone'
        ); 
    }

    public function get_columns()
    {
        return array('cb' => '<input type="checkbox" />') + self::columns();
    }

    public static function columns_hidden()
    {
    	return array(
            'user_id'
        );
    }

    public function get_hidden_columns()
    {
    	return self::columns_hidden();
    }
    
    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns()
    {
        return $sortable = array(
            'user_id' => array(
                'ID',
                false
            ),
            'user_name' => array(
                'display_name',
                false
            ),
            'user_email' => array(
                'user_email',
                false
            ),
            'mail_sent' => array(
                'mail_sent',
                false
            ),
            'mail_opened' => array(
                'mail_opened',
                false
            ),
            'last_login' => array(
                'last_login',
                false
            )
        );
    }
    
    public function get_bulk_actions()
    {
        $actions = array(
            'get_groups_of_slides' => 'Groupe de Slides',
            'add_groups_of_slides' => 'Ajouter groupe de slides',
            'copy_groups_of_slides' => 'Répliquer groupe de slides',
            'delete_groups_of_slides' => 'Supprimer groupe de slides',
            'send_mail' => 'Envoyer mail(s)'
        );
        return $actions;
    }
    
    public static function process_bulk_action($action, $users_id, $groups_of_slides = null)
    {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
            
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            
            if (!wp_verify_nonce($nonce, $action))
                wp_die('Nope! Security check failed!');
        }

        switch ($action) {
            
            case 'get_groups_of_slides':
                $get_groups_of_slides = array();
                if ($users_id)
                {
                    foreach ($users_id as $user_id) {
                        $client = new Client($user_id);
                        $current_groups_of_slides_temp = $client->get_groups_of_slides();
                        $current_groups_of_slides = explode(',', $current_groups_of_slides_temp);
                        foreach ($current_groups_of_slides as $current_group_of_slide) {
                            if (array_search($current_group_of_slide, $get_groups_of_slides) === false) {
                                $get_groups_of_slides[] = $current_group_of_slide;
                            }
                        }
                    }
                    $groups_of_slides_to_check = implode(',', $get_groups_of_slides);
                    return $groups_of_slides_to_check;
                }
                else
                    return false;
                break;
            
            case 'add_groups_of_slides':
                if ($users_id)
                    foreach ($users_id as $user_id) {
                        $client = new Client($user_id);
                        $client->add_groups_of_slides($groups_of_slides);
                    }
                return true;
                break;
            
            case 'copy_groups_of_slides':
                if ($users_id)
                    foreach ($users_id as $user_id) {
                        $client = new Client($user_id);
                        $client->copy_groups_of_slides($groups_of_slides);
                    }
                return true;
                break;
            
            case 'delete_groups_of_slides':
                if ($users_id)
                    foreach ($users_id as $user_id) {
                        $client = new Client($user_id);
                        $client->delete_groups_of_slides($groups_of_slides);
                    }
                return true;
                break;

            case 'send_mail':
                $success = true;
                if ($users_id)
                    foreach ($users_id as $user_id) {
                        $client = new Client($user_id);
                        $result = $client->send_mail();
                        if (!$result)
                            $success = false;
                    }
                return $success;
                break;
            
            default:
                // do nothing or something else
                return false;
                break;
        }
    }
    
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    public function prepare_items()
    {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
        
        /* -- Preparing your query -- */
        $prefix          = $wpdb->get_blog_prefix();
        $user_guest_role = get_option('user_guest_role', 'guest');
        $regexp          = esc_sql('s:' . strlen($user_guest_role) . ':"' . $user_guest_role . '";');
        $query           = "
            SELECT ID as user_id, display_name AS user_name, user_email, user_phone, user_logo, groups_of_slides, mail_sent, mail_opened, last_login FROM ".$prefix."users
            LEFT JOIN (SELECT * FROM wp_proposal) as wp1 ON ".$prefix."users.ID = wp1.client_id
            WHERE ID IN (SELECT distinct(user_id) FROM wp_usermeta where (meta_key = 'wp_capabilities' and meta_value REGEXP '" . $regexp . "'))";
        
        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'ASC';
        $order   = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : '';
        if (!empty($orderby) & !empty($order)) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }
        
        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows
        //How many to display per page?
        $perpage    = !empty($_GET["perpage"]) ? esc_sql($_GET["perpage"]) : 100;
        //Which page is this?
        $paged      = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';
        //Page Number
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems / $perpage);
        //adjust the query to take pagination into account
        if (!empty($paged) && !empty($perpage)) {
            $offset = ($paged - 1) * $perpage;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }
        
        /* -- Register the pagination -- */
        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage
        ));
        //The pagination links are automatically built according to those parameters
        
        /* -- Register the Columns -- */
        $columns               = $this->get_columns();
        $hidden                = $this->get_hidden_columns();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );
        
        /* -- Fetch the items -- */
        $this->items = $wpdb->get_results($query, ARRAY_A);
    }
}
