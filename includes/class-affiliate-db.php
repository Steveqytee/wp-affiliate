<?php

// In includes/class-affiliate-db.php:
class Affiliate_DB {

// Creates the affiliate registrations table
    public static function create_affiliate_registrations_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_registrations';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                affiliate_id bigint(20) UNSIGNED NOT NULL,
                first_name varchar(100) NOT NULL,
                last_name varchar(100) NOT NULL,
                email varchar(100) NOT NULL,
                phone varchar(20),
                address text,
                state varchar(100),
                coupon_code varchar(50),
                followers int(10),
                social_media text,
                social_id varchar(100),
                promotion_plan text,
                how_hear text,
                status varchar(20) DEFAULT 'pending',
                registration_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }


    // Create the affiliates table
    public static function create_affiliate_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliates';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) UNSIGNED NOT NULL,
                coupon_id bigint(20) UNSIGNED NOT NULL,
                commission_rate float(5,2) DEFAULT 5.00 NOT NULL,
                status varchar(20) DEFAULT 'pending',
                created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                custom_msg text,
                PRIMARY KEY (id),
                INDEX (user_id),
                FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
                FOREIGN KEY (coupon_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    // Create affiliate sales table
    public static function create_affiliate_sales_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_sales';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                affiliate_id bigint(20) UNSIGNED NOT NULL,
                sale_amount decimal(10,2) NOT NULL,
                commission decimal(10,2) NOT NULL,
                sale_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id),
                FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}affiliates(id) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    // Create affiliate referrals table
    public static function create_affiliate_referrals_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_referrals';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                affiliate_id bigint(20) UNSIGNED NOT NULL,
                referral_id bigint(20) UNSIGNED NOT NULL,
                referral_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}affiliates(id) ON DELETE CASCADE,
                FOREIGN KEY (referral_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    // Create affiliate performance table
    public static function create_affiliate_performance_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_performance';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                affiliate_id bigint(20) UNSIGNED NOT NULL,
                sales_count int NOT NULL,
                total_revenue decimal(10,2) NOT NULL,
                commission_earned decimal(10,2) NOT NULL,
                conversion_rate decimal(5,2) NOT NULL,
                order_value decimal(10,2) NOT NULL,
                last_sale_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id),
                FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}affiliates(id) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    // Create affiliate product commissions table
    public static function create_affiliate_product_commissions_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_product_commissions';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                affiliate_id bigint(20) UNSIGNED NOT NULL,
                product_id bigint(20) UNSIGNED NOT NULL,
                min_quantity int NOT NULL,
                commission_type varchar(20) NOT NULL,
                commission_value decimal(10,2) NOT NULL,
                PRIMARY KEY  (id),
                FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}affiliates(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    // Create affiliate order commissions table
    public static function create_affiliate_order_commissions_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_order_commissions';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                affiliate_id bigint(20) UNSIGNED NOT NULL,
                order_value_threshold decimal(10,2) NOT NULL,
                commission_type_below varchar(20) NOT NULL,
                commission_value_below decimal(10,2) NOT NULL,
                commission_type_above varchar(20) NOT NULL,
                commission_value_above decimal(10,2) NOT NULL,
                PRIMARY KEY  (id),
                FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}affiliates(id) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

  // Create affiliate quantity commissions table
  public static function create_affiliate_quantity_commissions_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'affiliate_quantity_commissions';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            affiliate_id bigint(20) UNSIGNED NOT NULL,
            min_quantity int NOT NULL,
            commission_type varchar(20) NOT NULL,
            commission_value decimal(10,2) NOT NULL,
            custom_msg text,
            PRIMARY KEY  (id),
            FOREIGN KEY (affiliate_id) REFERENCES {$wpdb->prefix}affiliates(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
 // Create affiliate user table
 public static function create_affiliate_user_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'affiliate_user';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        coupon_id bigint(20) UNSIGNED NOT NULL,
        commission_rate float NOT NULL DEFAULT 0,
        custom_msg text DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'inactive',
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
        FOREIGN KEY (coupon_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Run all create table methods on plugin activation
public static function affiliate_custom_db_activate() {
    self::create_affiliate_registrations_table();
    self::create_affiliate_table();
    self::create_affiliate_sales_table();
    self::create_affiliate_referrals_table();
    self::create_affiliate_performance_table();
    self::create_affiliate_product_commissions_table(); // New table
    self::create_affiliate_order_commissions_table();   // New table
    self::create_affiliate_quantity_commissions_table(); // New table
    self::create_affiliate_user_table();
}
}

// Ensure the activation function is called when the plugin is activated
register_activation_hook(__FILE__, ['Affiliate_DB', 'affiliate_custom_db_activate']);

?>
