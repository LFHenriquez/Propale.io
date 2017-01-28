<?php class Client extends WP_user
{
    private static $fields =
            array(
                array(
                    'name' => 'display_name',
                    'class' => 'typeahead',
                    'label' => 'Client name',
                    'editable' => true,
                    'description' => 'Name of the client / Nom du client',
                ),
                array(
                    'name' => 'user_email',
                    'label' => 'Email',
                    'editable' => true,
                    'description' => 'Email address / Adresse email',
                ),
                array(
                    'name' => 'user_phone',
                    'label' => 'Phone',
                    'description' => 'Phone number / Numéro de téléphone',
                    'editable' => true,
                    'meta' => true
                ),
                array(
                    'name' => 'user_logo',
                    'label' => 'Logo',
                    'description' => 'Url of the client\'s logo / Url du logo du client',
                    'editable' => true,
                    'meta' => true
                ),
                array(
                    'name' => 'groups_of_slides',
                    'label' => 'Slides',
                    'description' => 'Slides to display / Slides à afficher',
                    'editable' => true,
                    'meta' => true
                ),
                array(
                    'name' => 'mail_sent',
                    'label' => 'Mail sent',
                    'editable' => false,
                    'meta' => true
                ),
                array(
                    'name' => 'mail_opened',
                    'label' => 'Mail opened',
                    'editable' => false,
                    'meta' => true
                ),
                array(
                    'name' => 'last_login',
                    'label' => 'Last login',
                    'editable' => false,
                    'meta' => true
                )
            );
    
    function __construct($id)
    {
        parent::__construct($id);
    }

    public static function create($username, $email, $values_to_create = array())
    {
        global $wpdb;
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
        
            $values = array('client_id' => $user_id);
            if ($values_to_create)
                foreach ($values_to_create as $key => $value) {
                    if (in_array($key, $items))
                        $values[$key] = $value;
                }

            $prefix = $wpdb->get_blog_prefix();
            $table_name = $prefix. 'proposal';
            $wpdb->insert( 
                $table_name, 
                $values, 
                '%s' 
            );

            return $instance;
        }
        else
            return $result;
    }

    public function is_client() {
        $user = get_userdata( $this->ID );
        $guest_role = get_option('user_guest_role','guest');
        return ( in_array( $guest_role, (array) $user->roles ) )? true: false;
    }

    public static function get_fields()
    {
        return self::$fields;
    }

    public function get_user_phone()
    {
        return self::get_item('user_phone');
    }

    public function set_user_phone($value)
    {
        return self::set_item('user_phone', $value);
    }

    public function get_user_logo()
    {
        return self::get_item('user_logo');
    }

    public function set_user_logo($value)
    {
        return self::set_item('user_logo', $value);
    }

    public function get_groups_of_slides()
    {
        $values = self::get_item('groups_of_slides');
        $values = explode(',', $values);
        sort($values);
        return $values;
    }

    public function set_groups_of_slides($values)
    {
        $values = implode(',', $values);
        return self::set_item('groups_of_slides', $values);
    }

    public function add_groups_of_slides($values)
    {
        $current_groups_of_slides = $this->get_groups_of_slides();
        if ($values)
            foreach ($values as $value) {
                if (array_search($value, $current_groups_of_slides) === false) {
                    if ($current_groups_of_slides[0] == '')
                        $current_groups_of_slides[0] = $value;
                    else
                        $current_groups_of_slides[] = $value;
                }
            }
        var_dump($current_groups_of_slides);   
        $this->set_groups_of_slides($current_groups_of_slides);
    }

    public function copy_groups_of_slides($values)
    {
        $this->set_groups_of_slides($values);
    }

    public function delete_groups_of_slides($values)
    {
        $current_groups_of_slides = $this->get_groups_of_slides();
        if ($values)
            foreach ($values as $value) {
                if (($key = array_search($value, $current_groups_of_slides)) !== false) {
                    unset($current_groups_of_slides[$key]);
                }
            }
        $this->set_groups_of_slides($current_groups_of_slides);
    }

    public function get_mail_sent()
    {
        return self::get_item('mail_sent');
    }

    public function send_mail()
    {
        $user_email = self::get_item('user_email');
        $link = create_autologin_link($this->ID);
        $vars = array(
            'id' => $this->ID,
            'link' => $link
            );
        if ($body && wp_mail($user_email, 'Propale', $body))
            return self::set_item('mail_sent', current_time('mysql'));
        else
            return false;
    }

    public function get_mail_opened()
    {
        return self::get_item('mail_opened');
    }

    public function mail_opened()
    {
        return self::set_item('mail_opened', current_time('mysql'));
    }

    public function get_last_login()
    {
        return self::get_item('last_login');
    }

    public function last_login()
    {
        return self::set_item('last_login', current_time('mysql'));
    }

    public function get_item($item)
    {
        $fields_meta = array('meta' => true);
        $array = self::fields_names($fields_meta);
        if (in_array($item, $array))
        {
            global $wpdb;
            $prefix          = $wpdb->get_blog_prefix();
            $table_name      = $prefix. 'proposal';
            $query           = "
                SELECT ".$item." FROM ".$table_name."
                WHERE client_id=".$this->ID.";";
            $result = $wpdb->get_var($query);
            return $result;
        }
        else
        {
            return parent::get($item);
        }
    }

    public function set_item($item, $value)
    {
        $fields_meta = array('meta' => true);
        $array = self::fields_names($fields_meta);
        if (in_array($item, $array))
        {        
            global $wpdb;
            $prefix          = $wpdb->get_blog_prefix();
            $table_name      = $prefix. 'proposal';
            $result = $wpdb->update($table_name, array($item => $value), array('client_id' => $this->ID));
            return $result;
        }
        else
        {
            return wp_update_user( array( 'ID' => $this->ID, $item => $value ) );
        }
    }

    public static function fields_names($cond = false)
    {
        $fields_names = array();
        if (self::$fields)
            foreach (self::$fields as $item) {
                if ($cond) {
                    foreach ($cond as $condition => $value) {
                        if (isset($item[$condition]) && $item[$condition] == $value)
                                $fields_names[] = $item['name'];
                    }
                }
                else {
                    $fields_names[] = $item['name'];
                }
            }
        return $fields_names;
    }
}