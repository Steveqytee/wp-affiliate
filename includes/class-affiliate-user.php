<?php

class Affiliate_User {

    public static function get_affiliate_details($affiliate_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'affiliates';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $affiliate_id);
        $affiliate = $wpdb->get_row($query);

        return $affiliate;
    }

}
