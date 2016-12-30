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
            echo "<div id='filter-div' class='alignright'><input type='text' name='search-tag' placeholder='Etiquette(s)'></div>";
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
            case "user_tags":
            case "mail_sent":
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
            'edit' => sprintf('<a href="%suser-edit.php?user_id=%s">Modifier</a>', admin_url(), $item['user_id']),
            'delete' => sprintf('<a href="%susers.php?action=remove&user=%s&_wpnonce=%s">Supprimer</a>', admin_url(), $item['user_id'], wp_create_nonce('bulk-users'))
        );
        
        $logo = ($item['user_logo']) ? $item['user_logo'] : plugins_url("/img/avatar.png", __FILE__);
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
            'user_tags' 	=> 'Etiquette',
            'mail_sent' 	=> 'Mail envoyé',
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
            'last_login' => array(
                'last_login',
                false
            )
        );
    }
    
    public function get_bulk_actions()
    {
        $actions = array(
            'get_tag' => 'Etiquette(s)',
            'add_tag' => 'Ajouter étiquettes(s)',
            'copy_tag' => 'Répliquer étiquette(s)',
            'delete_tag' => 'Supprimer étiquette(s)',
            'send_mail' => 'Envoyer mail(s)'
        );
        return $actions;
    }
    
    public function process_bulk_action()
    {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
            
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
            
            if (!wp_verify_nonce($nonce, $action))
                wp_die('Nope! Security check failed!');
        }
        
        $action   = $this->current_action();
        $users_id = $_REQUEST['user_id'];
        $tags     = $_REQUEST['tags'];
        
        switch ($action) {
            
            case 'get_tag':
                if ($users_id)
                    foreach ($users_id as $user_id) {
                        $current_tags = explode(',', get_user_option('tags', (int) $user_id));
                        foreach ($tags as $tag) {
                            if (array_search($tag, $current_tags) === false) {
                                $get_tags[] = $tag;
                            }
                        }
                        $tags_to_check = implode(',', $current_tags);
                    }
                clean_redirect_wp_admin(array(
                    'tags_checked' => $tags_to_check
                ));
                break;
            
            case 'add_tag':
                if ($users_id)
                    foreach ($users_id as $user_id) {$current_tags = explode(',', get_user_option('tags', (int) $user_id));
                        if ($tags)
                            foreach ($tags as $tag) {
                                if (array_search($tag, $current_tags) === false) {
                                    if ($current_tags[0] == '')
                                        $current_tags[0] = $tag;
                                    else
                                        $current_tags[] = $tag;
                                }
                            }
                        $new_tags = implode(',', $current_tags);
                        update_user_option((int) $user_id, 'tags', $new_tags);
                    }
                clean_redirect_wp_admin();
                break;
            
            case 'copy_tag':
                if ($users_id)
                    foreach ($users_id as $user_id) {
                        update_user_option((int) $user_id, 'tags', $tags);
                    }
                clean_redirect_wp_admin();
                break;
            
            case 'delete_tag':
                if ($users_id)
                    foreach ($users_id as $user_id) {
                        $current_tags = explode(',', get_user_option('tags', (int) $user_id));
                        if ($tags)
                            foreach ($tags as $tag) {
                                if (($key = array_search($tag, $current_tags)) !== false) {
                                    unset($current_tags[$key]);
                                }
                            }
                        $new_tags = implode(',', $current_tags);
                        update_user_option((int) $user_id, 'tags', $new_tags);
                    }
                clean_redirect_wp_admin();
                break;

            case 'send_mail':
                if ($users_id)
                    foreach ($users_id as $user_id) {
                    	$user = get_userdata($user_id);
                    	$link = create_autologin_link($user_id);
                        $vars = array(
                            'link' => $link
                            );
            			$body = get_email_template('/email/template.php', $vars);
                        if ($body && wp_mail($user->user_email, 'Propale', $body))
                            update_user_option($user_id, 'mail_sent', current_time('mysql'));
                        else
                            update_user_option($user_id, 'mail_sent', 'failed');
					}
                clean_redirect_wp_admin();
                break;
            
            default:
                // do nothing or something else
                return;
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
        
        $this->process_bulk_action();
        /* -- Preparing your query -- */
        $prefix          = $wpdb->get_blog_prefix();
        $user_guest_role = get_option('user_guest_role', 'guest');
        $regexp          = esc_sql('s:' . strlen($user_guest_role) . ':"' . $user_guest_role . '";');
        $query           = "
                        SELECT ID as user_id, display_name AS user_name, user_email, user_phone, user_login, user_tags, mail_sent, last_login, user_logo FROM wp_users
                        LEFT JOIN (SELECT meta_value as user_tags, user_id FROM wp_usermeta WHERE wp_usermeta.meta_key = '" . $prefix . "tags') as wp1 ON wp_users.ID = wp1.user_id
                        LEFT JOIN (SELECT meta_value as mail_sent, user_id FROM wp_usermeta WHERE wp_usermeta.meta_key = '" . $prefix . "mail_sent') as wp2 ON wp_users.ID = wp2.user_id
                        LEFT JOIN (SELECT meta_value as last_login, user_id FROM wp_usermeta WHERE wp_usermeta.meta_key = '" . $prefix . "last_autologin') as wp3 ON wp_users.ID = wp3.user_id
                        LEFT JOIN (SELECT meta_value as user_phone, user_id FROM wp_usermeta WHERE wp_usermeta.meta_key = '" . $prefix . "phone') as wp4 ON wp_users.ID = wp4.user_id
                        LEFT JOIN (SELECT meta_value as user_logo, user_id FROM wp_usermeta WHERE wp_usermeta.meta_key = '" . $prefix . "logo') as wp5 ON wp_users.ID = wp5.user_id
                        WHERE ID IN (
                                SELECT distinct(user_id) FROM ebdb.wp_usermeta where (meta_key = '" . $prefix . "capabilities' and meta_value REGEXP '" . $regexp . "'))";
        
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
