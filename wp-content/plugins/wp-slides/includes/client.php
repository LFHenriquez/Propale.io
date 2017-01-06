<?php class Client extends WP_user
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
                    'name' => 'user_phone',
                    'label' => 'Phone',
                    'description' => 'Phone number / Numéro de téléphone',
                    'meta' => true
                ),
                array(
                    'name' => 'user_logo',
                    'label' => 'Logo',
                    'description' => 'Url of the client\'s logo / Url du logo du client',
                    'meta' => true
                ),
                array(
                    'name' => 'groups_of_slides',
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

    public static function get_fields()
    {
        return self::$fields;
    }

    public function get_user_phone()
    {
        return get_item('user_phone');
    }

    public function set_user_phone($value)
    {
        return set_item('user_phone', $value);
    }

    public function get_user_logo()
    {
        return get_item('user_logo');
    }

    public function set_user_logo($value)
    {
        return set_item('user_logo', $value);
    }

    public function get_groups_of_slides()
    {
        return get_item('groups_of_slides');
    }

    public function set_groups_of_slides($value)
    {
        return set_item('groups_of_slides', $value);
    }

    public function get_mail_sent()
    {
        return get_item('mail_sent');
    }

    public function set_mail_sent($value)
    {
        return set_item('mail_sent', $value);
    }

    public function get_mail_opened()
    {
        return get_item('mail_opened');
    }

    public function set_mail_opened($value)
    {
        return set_item('mail_opened', $value);
    }

    public function get_last_login()
    {
        return get_item('last_login');
    }

    public function set_last_login($value)
    {
        return set_item('last_login', $value);
    }

    private function get_item($item)
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

    private function set_item($item, $value)
    {
        global $wpdb;
        $prefix          = $wpdb->get_blog_prefix();
        $table_name      = $prefix. 'proposal';
        $result = $wpdb->update($table_name, array($item => $value), array('client_id' => $this->ID));
        return $result;
    }
}