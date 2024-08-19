<form method="post">
    <table class="form-table">
        <tr>
            <th><label for="affiliate_registration_requires_approval">Require Admin Approval for Registration</label></th>
            <td><input type="checkbox" name="affiliate_registration_requires_approval" <?php checked('yes', $requires_approval); ?>></td>
        </tr>
    </table>
    <input type="submit" name="save_registration_settings" value="Save Settings" class="button button-primary">
</form>
