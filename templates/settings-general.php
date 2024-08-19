<!-- Include the HTML and PHP logic for the settings page -->

<form method="post">
    <table class="form-table">
        <tr>
            <th><label for="affiliate_program_name">Program Name</label></th>
            <td><input type="text" name="affiliate_program_name" value="<?php echo esc_attr($affiliate_program_name); ?>" class="regular-text"></td>
        </tr>
    </table>
    <input type="submit" name="save_general_settings" value="Save Settings" class="button button-primary">
</form>
