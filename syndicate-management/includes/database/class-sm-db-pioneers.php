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

        if (!empty($args['specialization'])) {
            $where[] = "specialization LIKE %s";
            $params[] = '%' . $wpdb->esc_like($args['specialization']) . '%';
        }

        if (!empty($args['status'])) {
            $where[] = "status = %s";
            $params[] = $args['status'];
        }

        if (!empty($args['only_active'])) {
            $where[] = "status = 'active'";
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

        $name = sanitize_text_field($data['name']);
        $slug = sanitize_title($name);

        // Ensure slug uniqueness
        $check = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE slug = %s", $slug));
        if ($check) { $slug .= '-' . time(); }

        $res = $wpdb->insert($table, [
            'name' => $name,
            'slug' => $slug,
            'photo_url' => esc_url_raw($data['photo_url']),
            'specialization' => sanitize_text_field($data['specialization'] ?? ''),
            'experience' => wp_kses_post($data['experience'] ?? ''),
            'achievements' => wp_kses_post($data['achievements'] ?? ''),
            'bio' => wp_kses_post($data['bio']),
            'governorate' => sanitize_text_field($data['governorate']),
            'status' => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ]);

        return $res ? $wpdb->insert_id : false;
    }

    public static function update_pioneer($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';

        $update_data = [];
        $fields = ['name', 'photo_url', 'specialization', 'experience', 'achievements', 'bio', 'governorate', 'status'];

        foreach ($fields as $f) {
            if (isset($data[$f])) {
                if (in_array($f, ['bio', 'experience', 'achievements'])) {
                    $update_data[$f] = wp_kses_post($data[$f]);
                } elseif ($f === 'photo_url') {
                    $update_data[$f] = esc_url_raw($data[$f]);
                } else {
                    $update_data[$f] = sanitize_text_field($data[$f]);
                }
            }
        }

        if (isset($data['name'])) {
            $update_data['slug'] = sanitize_title($data['name']);
        }

        return $wpdb->update($table, $update_data, ['id' => $id]);
    }

    public static function toggle_status($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';
        $p = self::get_pioneer_by_id($id);
        if (!$p) return false;

        $new_status = ($p->status === 'active') ? 'inactive' : 'active';
        return $wpdb->update($table, ['status' => $new_status], ['id' => $id]);
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

    public static function get_pioneer_by_slug($slug) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE slug = %s", $slug));
    }

    public static function name_exists($name) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_pioneers';
        return $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE name = %s", $name));
    }
}
