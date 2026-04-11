<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_Pioneer_Manager {
    public static function register_shortcodes() {
        add_shortcode('industry_pioneers', [__CLASS__, 'shortcode_industry_pioneers']);
    }

    public static function ajax_add_pioneer() {
        check_ajax_referer('sm_admin_action', 'nonce');
        if (!current_user_can('sm_manage_system') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $name = sanitize_text_field($_POST['name'] ?? '');
        if (SM_DB_Pioneers::name_exists($name)) {
            wp_send_json_error(['message' => 'هذا الاسم مسجل مسبقاً في النظام']);
        }

        $res = SM_DB_Pioneers::add_pioneer($_POST);
        if ($res) {
            SM_Logger::log('إضافة رائد مهنة', "تم إضافة رائد المهنة: $name");
            wp_send_json_success(['id' => $res]);
        } else {
            wp_send_json_error(['message' => 'فشل في إضافة رائد المهنة']);
        }
    }

    public static function ajax_delete_pioneer() {
        check_ajax_referer('sm_admin_action', 'nonce');
        $id = intval($_POST['id'] ?? 0);

        $res = SM_DB_Pioneers::delete_pioneer($id);
        if ($res) {
            SM_Logger::log('حذف رائد مهنة', "تم حذف رائد المهنة ID: $id");
            wp_send_json_success('تم الحذف بنجاح');
        } else {
            wp_send_json_error(['message' => 'فشل في الحذف أو لا تملك الصلاحية']);
        }
    }

    public static function shortcode_industry_pioneers($atts) {
        $govs = SM_Settings::get_governorates();
        $pioneers = SM_DB_Pioneers::get_pioneers();

        ob_start();
        ?>
        <div class="sm-pioneers-portal" dir="rtl">
            <div class="sm-portal-hero" style="background:#fff; border:1px solid #e2e8f0; border-radius:24px; padding:35px; margin-bottom:30px; text-align:center; box-shadow:0 10px 25px -5px rgba(0,0,0,0.04);">
                <h2 style="margin:0; font-weight:900; font-size:2em; color:var(--sm-dark-color);">رواد المهنة</h2>
                <p style="color:#64748b; margin-top:10px;">لوحة شرف مخصصة للمتميزين في مجالات الإصابات والتأهيل والعلوم الرياضية</p>

                <div style="max-width:800px; margin:25px auto 0; display:flex; gap:15px;">
                    <input type="text" id="sm-pioneer-search" placeholder="ابحث عن الاسم أو التخصص..." style="flex:2; height:48px; border-radius:12px; border:2px solid #f1f5f9; padding:0 15px;" oninput="smFilterPioneers()">
                    <select id="sm-pioneer-gov-filter" style="flex:1; height:48px; border-radius:12px; border:2px solid #f1f5f9;" onchange="smFilterPioneers()">
                        <option value="">كافة الفروع</option>
                        <?php foreach($govs as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                    </select>
                </div>
            </div>

            <div id="sm-pioneers-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:25px;">
                <?php foreach($pioneers as $p): ?>
                    <div class="sm-pioneer-card" data-name="<?php echo esc_attr($p->name); ?>" data-spec="<?php echo esc_attr($p->specialization); ?>" data-gov="<?php echo esc_attr($p->governorate); ?>" style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden; transition:0.3s; cursor:pointer;" onclick="smTogglePioneerDetails(<?php echo $p->id; ?>)">
                        <div style="height:200px; background:#f8fafc; overflow:hidden;">
                            <?php if($p->photo_url): ?>
                                <img src="<?php echo esc_url($p->photo_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e0;">
                                    <span class="dashicons dashicons-admin-users" style="font-size:60px; width:60px; height:60px;"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="padding:20px; position:relative;">
                            <h3 style="margin:0; font-size:1.2em; font-weight:800; color:var(--sm-dark-color);"><?php echo esc_html($p->name); ?></h3>
                            <?php if($p->specialization): ?>
                                <div style="font-size:12px; color:#64748b; margin-top:5px; font-weight:600;"><?php echo esc_html($p->specialization); ?></div>
                            <?php endif; ?>
                            <div style="margin-top:10px; display:flex; align-items:center; gap:8px;">
                                <span style="background:rgba(246, 48, 73, 0.05); color:#e53e3e; padding:4px 12px; border-radius:50px; font-size:11px; font-weight:800; border:1px solid rgba(246, 48, 73, 0.1);">
                                    <?php echo esc_html($govs[$p->governorate] ?? $p->governorate); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="sm-pioneer-details-<?php echo $p->id; ?>" class="sm-pioneer-details" style="display:none; grid-column: 1/-1; background:#f8fafc; border:1px solid #e2e8f0; border-radius:20px; padding:30px; margin-top:-10px; margin-bottom:20px; animation: smFadeIn 0.3s ease;">
                        <h4 style="margin:0 0 15px 0; font-weight:800; color:var(--sm-primary-color);">السيرة الذاتية والمسيرة المهنية</h4>
                        <div style="line-height:1.8; color:#4a5568;">
                            <?php echo wp_kses_post($p->bio); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
        function smFilterPioneers() {
            const search = document.getElementById('sm-pioneer-search').value.toLowerCase();
            const gov = document.getElementById('sm-pioneer-gov-filter').value;
            const cards = document.querySelectorAll('.sm-pioneer-card');

            cards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const spec = (card.dataset.spec || '').toLowerCase();
                const cardGov = card.dataset.gov;
                const matchesSearch = name.includes(search) || spec.includes(search);
                const matchesGov = !gov || cardGov === gov;

                card.style.display = (matchesSearch && matchesGov) ? 'block' : 'none';
                const detailsId = 'sm-pioneer-details-' + card.getAttribute('onclick').match(/\d+/)[0];
                const details = document.getElementById(detailsId);
                if (details) details.style.display = 'none';
            });
        }

        function smTogglePioneerDetails(id) {
            const details = document.getElementById('sm-pioneer-details-' + id);
            const isVisible = details.style.display === 'block';

            document.querySelectorAll('.sm-pioneer-details').forEach(d => d.style.display = 'none');

            if (!isVisible) {
                details.style.display = 'block';
                details.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
