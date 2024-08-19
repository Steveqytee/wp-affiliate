<?php

class Affiliate_Shortcodes {

    public static function init() {
        add_shortcode('affiliate_registration_form', [self::class, 'render_registration_form']);
        add_shortcode('affiliate_login_form', [self::class, 'render_login_form']);
    }

    public static function render_registration_form() {
        return Affiliate_Registration::render_registration_form();
    }

    public static function render_login_form() {
        ob_start();
        include MY_AFFILIATE_PLUGIN_DIR . 'templates/login-form.php';
        return ob_get_clean();
    }

    public static function affiliate_login_form() {
        if (is_user_logged_in()) {
            wp_redirect(site_url('/affiliate-dashboard'));
            exit;
        }

        ob_start();
        ?>
        <div class="affiliate-login-form">
            <h2>Login</h2>
            <form method="post">
                <p>
                    <label for="username">Username or email:</label>
                    <input type="text" name="username" required value="<?php echo isset($_POST['username']) ? esc_attr($_POST['username']) : ''; ?>">
                    <?php if (isset($_POST['affiliate_login']) && empty($_POST['username'])): ?>
                        <span style="color:red;">This field is required.</span>
                    <?php endif; ?>
                </p>
                <p>
                    <label for="password">Password:</label>
                    <input type="password" name="password" required>
                    <?php if (isset($_POST['affiliate_login']) && empty($_POST['password'])): ?>
                        <span style="color:red;">This field is required.</span>
                    <?php endif; ?>
                </p>
                <p>
                    <input type="checkbox" name="rememberme"> Remember me
                </p>
                <p>
                    <input type="submit" name="affiliate_login" value="Login">
                </p>
                <?php wp_nonce_field('affiliate_login_action', 'affiliate_login_nonce'); ?>
            </form>
            <p><a href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a></p>
            <p>Not an affiliate? <a href="<?php echo site_url('/affiliate-register'); ?>">Register here</a></p>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function handle_affiliate_login() {
        if (isset($_POST['affiliate_login']) && wp_verify_nonce($_POST['affiliate_login_nonce'], 'affiliate_login_action')) {
            $username = sanitize_text_field($_POST['username']);
            $password = $_POST['password'];
            $remember = isset($_POST['rememberme']) ? true : false;

            $creds = [
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => $remember
            ];

            $user = wp_signon($creds, false);

            if (is_wp_error($user)) {
                $error_code = $user->get_error_code();
                if ($error_code == 'incorrect_password') {
                    $error_message = 'Incorrect password. Please try again.';
                } elseif ($error_code == 'invalid_username') {
                    $error_message = 'Username not found. Please check your username or email.';
                } else {
                    $error_message = 'Login failed: ' . esc_html($user->get_error_message());
                }

                // Trigger a JavaScript alert with the error message
                add_action('wp_footer', function() use ($error_message) {
                    echo '<script type="text/javascript">alert("' . esc_js($error_message) . '");</script>';
                });

            } else {
                wp_redirect(site_url('/affiliate-dashboard'));
                exit;
            }
        }
    }

    public static function affiliate_dashboard_shortcode($atts) {
        if (!is_user_logged_in() || !current_user_can('affiliate')) {
            return "You must be logged in as an affiliate to view this.";
        }

        $current_user = wp_get_current_user();
        // Fetch and display data related to the affiliate
        return "<h2>Welcome, {$current_user->display_name}</h2>";
    }

}

add_action('init', ['Affiliate_Shortcodes', 'handle_affiliate_login']);
add_shortcode('affiliate_login_form', ['Affiliate_Shortcodes', 'affiliate_login_form']);
