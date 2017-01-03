<?php

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action('show_user_profile', 'show_user_tags_in_profile');
add_action('edit_user_profile', 'show_user_tags_in_profile');
add_action('personal_options_update', 'save_user_tags_in_profile');
add_action('edit_user_profile_update', 'save_user_tags_in_profile');

/*----------------------------------------------
Functions
----------------------------------------------*/
function show_user_tags_in_profile($user)
{ ?>
    <h2>User Tags Plugin</h2>
    <table class="form-table">
        <tr>
            <th><label for="user_tags">Tags</label></th>
            <td>
                <input type="text" name="user_tags" id="user_tags" value="<?php echo esc_attr(get_user_option('tags', $user->ID)); ?>" class="regular-text" /><br />
                <span class="description">Please enter user tags (comma separated) / Entrez les étiquettes de l'utilisateur (en les séparants par des virgules sans espace après la virgule)</span>
            </td>
        <tr>
        </tr>
            <th><label for="user_phone">Téléphone</label></th>
            <td>
                <input type="text" name="user_phone" id="user_phone" value="<?php echo esc_attr(get_user_option('phone', $user->ID)); ?>" class="regular-text" /><br />
                <span class="description">Phone number / numéro de téléphone</span>
            </td>
        </tr>
        <tr>
            <th><label for="user_logo">Logo url</label></th>
            <td>
                <input type="text" name="user_logo" id="user_logo" value="<?php echo esc_attr(get_user_option('logo', $user->ID)); ?>" class="regular-text" /><br />
                <span class="description">Please enter guest logo (url) / Entrez l'url du logo de l'invité (en les séparants par des virgules sans espace après la virgule)</span>
            </td>
        <tr>
    </table>
<?php }

function save_user_tags_in_profile($user_id)
{
    if (!current_user_can('edit_user', $user_id))
        return false;
    
    /* Copy and paste this line for additional fields. Make sure to change the field ID. */
    update_user_option(absint($user_id), 'tags', wp_kses_post($_POST['user_tags']));
    update_user_option(absint($user_id), 'phone', wp_kses_post($_POST['user_phone']));
    update_user_option(absint($user_id), 'logo', wp_kses_post($_POST['user_logo']));
}