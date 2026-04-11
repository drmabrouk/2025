<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_Pioneer_Manager {
    public static function register_shortcodes() {
        add_shortcode('industry_pioneers', [__CLASS__, 'shortcode_industry_pioneers']);

        // Shortened URL structure: /p/slug instead of /industry-pioneers/slug
        add_rewrite_rule('^p/([^/]+)/?', 'index.php?pagename=industry-pioneers&pioneer_slug=$matches[1]', 'top');

        add_filter('query_vars', function($vars) {
            $vars[] = 'pioneer_slug';
            return $vars;
        });

        // Ensure rewrite rules are flushed if they were just added
        if (get_option('sm_pioneers_rewrite_flushed') !== SM_VERSION) {
            flush_rewrite_rules();
            update_option('sm_pioneers_rewrite_flushed', SM_VERSION);
        }
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

    public static function ajax_edit_pioneer() {
        check_ajax_referer('sm_admin_action', 'nonce');
        if (!current_user_can('sm_manage_system') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $id = intval($_POST['id'] ?? 0);
        $res = SM_DB_Pioneers::update_pioneer($id, $_POST);
        if ($res !== false) {
            SM_Logger::log('تعديل رائد مهنة', "تم تعديل بيانات رائد المهنة ID: $id");
            wp_send_json_success('تم تحديث البيانات بنجاح');
        } else {
            wp_send_json_error(['message' => 'فشل في تحديث البيانات']);
        }
    }

    public static function ajax_toggle_pioneer_status() {
        check_ajax_referer('sm_admin_action', 'nonce');
        if (!current_user_can('sm_manage_system') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $id = intval($_POST['id'] ?? 0);
        $res = SM_DB_Pioneers::toggle_status($id);
        if ($res) {
            wp_send_json_success('تم تغيير الحالة بنجاح');
        } else {
            wp_send_json_error(['message' => 'فشل في تغيير الحالة']);
        }
    }

    public static function ajax_get_pioneer_details() {
        check_ajax_referer('sm_admin_action', 'nonce');
        $id = intval($_GET['id'] ?? 0);
        $p = SM_DB_Pioneers::get_pioneer_by_id($id);
        if ($p) {
            wp_send_json_success($p);
        } else {
            wp_send_json_error(['message' => 'رائد المهنة غير موجود']);
        }
    }

    public static function shortcode_industry_pioneers($atts) {
        $slug = get_query_var('pioneer_slug');
        if ($slug) {
            return self::render_single_pioneer($slug);
        }

        $govs = SM_Settings::get_governorates();
        $pioneers = SM_DB_Pioneers::get_pioneers(['only_active' => true]);

        ob_start();
        ?>
        <div class="sm-pioneers-portal" dir="rtl">
            <div class="sm-portal-hero" style="background:#fff; border:1px solid #e2e8f0; border-radius:30px; padding:60px 40px; margin-bottom:50px; text-align:center; box-shadow:0 20px 40px rgba(0,0,0,0.03); position:relative; overflow:hidden;">
                <div style="position:absolute; top:-100px; right:-100px; width:250px; height:250px; background:rgba(246, 48, 73, 0.02); border-radius:50%;"></div>
                <div style="position:relative; z-index:2;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; width:60px; height:60px; background:rgba(246, 48, 73, 0.07); border-radius:20px; margin-bottom:20px;">
                        <span class="dashicons dashicons-awards" style="font-size:30px; width:30px; height:30px; color:var(--sm-primary-color);"></span>
                    </div>
                    <h1 style="margin:0; font-weight:900; font-size:2.8em; color:var(--sm-dark-color); letter-spacing:-1px;">دليل رواد المهنة</h1>
                    <p style="color:#64748b; margin-top:15px; font-size:1.1em; max-width:600px; margin-left:auto; margin-right:auto; line-height:1.6;">لوحة الشرف الرقمية المعتمدة لنقابة الإصابات والتأهيل، نعتز بنخبة من المبدعين والرواد في تخصصاتنا المهنية.</p>

                    <div style="max-width:900px; margin:40px auto 0; background:#f8fafc; padding:10px; border-radius:20px; border:1px solid #f1f5f9; display:flex; gap:10px; box-shadow:0 10px 20px rgba(0,0,0,0.02);">
                        <div style="flex:2; position:relative;">
                            <span class="dashicons dashicons-search" style="position:absolute; right:15px; top:16px; color:#94a3b8;"></span>
                            <input type="text" id="sm-pioneer-search" placeholder="ابحث باسم الرائد، التخصص الدقيق، أو الكلمات المفتاحية..." style="width:100%; height:52px; border-radius:15px; border:none; padding:0 45px 0 15px; font-family:inherit; font-weight:600; outline:none; font-size:14px;" oninput="smFilterPioneers()">
                            <div id="sm-pioneer-autocomplete" style="position:absolute; top:110%; left:0; right:0; background:#fff; border-radius:12px; box-shadow:0 15px 30px rgba(0,0,0,0.1); z-index:100; display:none; border:1px solid #eee; text-align:right; max-height:250px; overflow-y:auto;"></div>
                        </div>
                        <select id="sm-pioneer-gov-filter" style="flex:1; height:52px; border-radius:15px; border:none; background:#fff; font-family:inherit; font-weight:700; color:var(--sm-dark-color); cursor:pointer; outline:none; font-size:14px; padding:0 15px;" onchange="smFilterPioneers()">
                            <option value="">جميع الفروع والمحافظات</option>
                            <?php foreach($govs as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="sm-pioneers-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:35px;">
                <?php foreach($pioneers as $p): ?>
                    <div class="sm-pioneer-card" data-name="<?php echo esc_attr($p->name); ?>" data-spec="<?php echo esc_attr($p->specialization); ?>" data-gov="<?php echo esc_attr($p->governorate); ?>" style="background:#fff; border:1px solid #e2e8f0; border-radius:24px; overflow:hidden; transition:0.4s ease; cursor:pointer; box-shadow:0 4px 6px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 20px 30px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.02)';" onclick="location.href='<?php echo esc_url(home_url('/p/' . $p->slug)); ?>'">
                        <div style="height:240px; background:#f8fafc; overflow:hidden; position:relative;">
                            <?php if($p->photo_url): ?>
                                <img src="<?php echo esc_url($p->photo_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e0;">
                                    <span class="dashicons dashicons-admin-users" style="font-size:80px; width:80px; height:80px;"></span>
                                </div>
                            <?php endif; ?>
                            <div style="position:absolute; bottom:15px; right:15px;">
                                <span style="background:rgba(246, 48, 73, 0.9); color:#fff; padding:4px 15px; border-radius:50px; font-size:11px; font-weight:800; backdrop-filter:blur(5px); box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                                    <?php echo esc_html($govs[$p->governorate] ?? $p->governorate); ?>
                                </span>
                            </div>
                        </div>
                        <div style="padding:25px; text-align:center;">
                            <h3 style="margin:0; font-size:1.4em; font-weight:900; color:var(--sm-dark-color);"><?php echo esc_html($p->name); ?></h3>
                            <div style="margin-top:8px; color:var(--sm-primary-color); font-weight:700; font-size:14px;"><?php echo esc_html($p->specialization); ?></div>
                            <div style="margin-top:20px; padding-top:20px; border-top:1px solid #f1f5f9; display:flex; justify-content:center;">
                                <span style="color:#64748b; font-size:13px; font-weight:600; display:flex; align-items:center; gap:5px;">استعراض الملف الشخصي <span class="dashicons dashicons-arrow-left-alt2" style="font-size:14px; margin-top:2px;"></span></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
        const pioneerData = <?php echo json_encode(array_map(function($p) { return ['name' => $p->name, 'spec' => $p->specialization]; }, $pioneers)); ?>;

        function smFilterPioneers() {
            const input = document.getElementById('sm-pioneer-search');
            const search = input.value.toLowerCase();
            const gov = document.getElementById('sm-pioneer-gov-filter').value;
            const cards = document.querySelectorAll('.sm-pioneer-card');
            const autocomplete = document.getElementById('sm-pioneer-autocomplete');

            let visibleCount = 0;
            cards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const spec = (card.dataset.spec || '').toLowerCase();
                const cardGov = card.dataset.gov;
                const matchesSearch = name.includes(search) || spec.includes(search);
                const matchesGov = !gov || cardGov === gov;

                if (matchesSearch && matchesGov) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Handle Autocomplete
            if (search.length > 1) {
                const suggestions = pioneerData.filter(p => p.name.toLowerCase().includes(search) || p.spec.toLowerCase().includes(search)).slice(0, 5);
                if (suggestions.length > 0) {
                    autocomplete.innerHTML = suggestions.map(s => `
                        <div style="padding:12px 20px; cursor:pointer; border-bottom:1px solid #f8fafc;" onclick="smSelectPioneer('${s.name.replace(/'/g, "\\'")}')">
                            <div style="font-weight:800; font-size:13px;">${s.name}</div>
                            <div style="font-size:11px; color:var(--sm-primary-color);">${s.spec}</div>
                        </div>
                    `).join('');
                    autocomplete.style.display = 'block';
                } else {
                    autocomplete.style.display = 'none';
                }
            } else {
                autocomplete.style.display = 'none';
            }
        }

        function smSelectPioneer(name) {
            const input = document.getElementById('sm-pioneer-search');
            input.value = name;
            document.getElementById('sm-pioneer-autocomplete').style.display = 'none';
            smFilterPioneers();
        }

        document.addEventListener('click', (e) => {
            if (!document.getElementById('sm-pioneer-search').contains(e.target)) {
                document.getElementById('sm-pioneer-autocomplete').style.display = 'none';
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    private static function render_single_pioneer($slug) {
        $p = SM_DB_Pioneers::get_pioneer_by_slug($slug);
        if (!$p || $p->status !== 'active') {
            return '<div style="text-align:center; padding:100px 20px;">
                <div style="font-size:60px; margin-bottom:20px;">👤</div>
                <h2 style="font-weight:900;">رائد المهنة غير موجود</h2>
                <p style="color:#64748b;">عذراً، الملف الذي تبحث عنه غير متاح أو تم نقله.</p>
                <a href="' . home_url('/industry-pioneers') . '" class="sm-btn" style="width:auto; padding:0 30px; margin-top:20px;">العودة للدليل</a>
            </div>';
        }
        $govs = SM_Settings::get_governorates();

        ob_start();
        ?>
        <div class="sm-pioneer-profile" dir="rtl" style="max-width:1000px; margin:60px auto; background:#fff; border-radius:35px; overflow:hidden; box-shadow:0 30px 60px rgba(0,0,0,0.07); border:1px solid #f1f5f9;">
            <div style="background:linear-gradient(135deg, var(--sm-primary-color) 0%, var(--sm-dark-color) 100%); padding:80px 40px; text-align:center; color:#fff; position:relative; overflow:hidden;">
                <div style="position:absolute; top:-50px; right:-50px; width:250px; height:250px; background:rgba(255,255,255,0.05); border-radius:50%;"></div>
                <div style="position:absolute; bottom:-100px; left:-50px; width:300px; height:300px; background:rgba(255,255,255,0.03); border-radius:50%;"></div>

                <div style="width:180px; height:180px; margin:0 auto 30px; border-radius:50%; border:6px solid rgba(255,255,255,0.2); overflow:hidden; box-shadow:0 15px 35px rgba(0,0,0,0.25); position:relative; z-index:2;">
                    <?php if($p->photo_url): ?>
                        <img src="<?php echo esc_url($p->photo_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f8fafc; color:#cbd5e0;">
                            <span class="dashicons dashicons-admin-users" style="font-size:100px; width:100px; height:100px;"></span>
                        </div>
                    <?php endif; ?>
                </div>

                <h1 style="margin:0; font-size:2.8em; font-weight:900; color:#fff; text-shadow:0 2px 4px rgba(0,0,0,0.1);"><?php echo esc_html($p->name); ?></h1>
                <div style="margin-top:10px; font-size:1.4em; opacity:0.95; font-weight:700; letter-spacing:0.5px;"><?php echo esc_html($p->specialization); ?></div>

                <div style="margin-top:25px; display:inline-flex; align-items:center; gap:10px; background:rgba(255,255,255,0.15); padding:8px 25px; border-radius:50px; font-size:15px; font-weight:800; backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2);">
                    <span class="dashicons dashicons-location" style="font-size:18px;"></span>
                    <?php echo esc_html($govs[$p->governorate] ?? $p->governorate); ?>
                </div>
            </div>

            <div style="padding:50px; display:grid; gap:50px; background:#fff;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:40px;">
                    <?php if($p->experience): ?>
                        <div style="background:#f8fafc; padding:35px; border-radius:24px; border:1px solid #f1f5f9; position:relative;">
                            <div style="position:absolute; top:20px; left:20px; opacity:0.05;"><span class="dashicons dashicons-businessman" style="font-size:60px; width:60px; height:60px;"></span></div>
                            <h3 style="display:flex; align-items:center; gap:12px; font-weight:900; color:var(--sm-dark-color); margin:0 0 20px 0; font-size:1.3em;">
                                <span style="background:var(--sm-primary-color); color:#fff; width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-businessman"></span></span> الخبرات العملية
                            </h3>
                            <div style="line-height:1.8; color:#4a5568; font-weight:500;">
                                <?php echo wp_kses_post($p->experience); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($p->achievements): ?>
                        <div style="background:#fffcf2; padding:35px; border-radius:24px; border:1px solid #fef3c7; position:relative;">
                            <div style="position:absolute; top:20px; left:20px; opacity:0.05;"><span class="dashicons dashicons-awards" style="font-size:60px; width:60px; height:60px;"></span></div>
                            <h3 style="display:flex; align-items:center; gap:12px; font-weight:900; color:var(--sm-dark-color); margin:0 0 20px 0; font-size:1.3em;">
                                <span style="background:#f59e0b; color:#fff; width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-awards"></span></span> الإنجازات والجوائز
                            </h3>
                            <div style="line-height:1.8; color:#92400e; font-weight:500;">
                                <?php echo wp_kses_post($p->achievements); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="border:1px solid #f1f5f9; border-radius:24px; padding:40px; background:#fcfcfc;">
                    <h3 style="display:flex; align-items:center; gap:12px; font-weight:900; color:var(--sm-dark-color); margin:0 0 25px 0; font-size:1.5em;">
                         السيرة الذاتية المفصلة
                    </h3>
                    <div style="line-height:2.2; color:#4a5568; font-size:1.15em; text-align:justify;">
                        <?php echo wp_kses_post($p->bio); ?>
                    </div>
                </div>

                <div style="margin-top:20px; padding-top:40px; border-top:2px solid #f8fafc; display:flex; justify-content:space-between; align-items:center;">
                    <a href="<?php echo home_url('/industry-pioneers'); ?>" style="color:#64748b; text-decoration:none; font-weight:800; display:flex; align-items:center; gap:10px; font-size:1.1em; transition:0.3s;" onmouseover="this.style.color='var(--sm-primary-color)';" onmouseout="this.style.color='#64748b';">
                        <span class="dashicons dashicons-arrow-right-alt2" style="background:#f1f5f9; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center;"></span> العودة لدليل الرواد
                    </a>
                    <div style="display:flex; gap:15px;">
                        <button onclick="smSharePioneer('<?php echo esc_url(home_url('/p/' . $p->slug)); ?>')" class="sm-btn" style="width:auto; padding:0 35px; background:#3182ce; height:50px; font-weight:800; border-radius:15px; box-shadow:0 10px 20px rgba(49, 130, 206, 0.2);">
                            <span class="dashicons dashicons-share" style="margin-top:4px;"></span> مشاركة الملف الشخصي
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function smSharePioneer(url) {
            if (navigator.share) {
                navigator.share({ title: 'رائد المهنة: <?php echo esc_js($p->name); ?>', text: 'تعرف على رائد المهنة المتميز من خلال ملفه الشخصي المعتمد.', url: url });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    const btn = event.currentTarget;
                    const oldText = btn.innerHTML;
                    btn.innerHTML = '✓ تم النسخ';
                    setTimeout(() => btn.innerHTML = oldText, 2000);
                });
            }
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
