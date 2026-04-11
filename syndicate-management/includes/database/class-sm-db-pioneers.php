<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_DB_Pioneers {
    public static function get_pioneers($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';
        $where = ["1=1"];
        $params = [];

        if (!empty($args['search'])) {
            $s = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = "(name LIKE %s OR bio LIKE %s OR specialization LIKE %s)";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($args['governorate'])) {
            $where[] = "governorate = %s";
            $params[] = $args['governorate'];
        }

        $where_sql = implode(' AND ', $where);
        $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC";

        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($sql, $params));
        }
        return $wpdb->get_results($sql);
    }

    public static function add_pioneer($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';

        $res = $wpdb->insert($table, [
            'name' => sanitize_text_field($data['name']),
            'photo_url' => esc_url_raw($data['photo_url']),
            'specialization' => sanitize_text_field($data['specialization'] ?? ''),
            'bio' => wp_kses_post($data['bio']),
            'governorate' => sanitize_text_field($data['governorate']),
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ]);

        return $res ? $wpdb->insert_id : false;
    }

    public static function delete_pioneer($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';

        $pioneer = self::get_pioneer_by_id($id);
        if (!$pioneer) return false;

        // Permission check: Only the creator or super admin can delete
        if (!current_user_can('manage_options') && $pioneer->created_by != get_current_user_id()) {
            return false;
        }

        return $wpdb->delete($table, ['id' => $id]);
    }

    public static function get_pioneer_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function name_exists($name) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';
        return $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE name = %s", $name));
    }
}
