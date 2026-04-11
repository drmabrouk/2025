<?php if (!defined('ABSPATH')) exit;
$pioneers = SM_DB_Pioneers::get_pioneers();
$govs = SM_Settings::get_governorates();
?>

<div class="sm-pioneers-admin">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0;">إدارة رواد المهنة</h2>
        <button onclick="document.getElementById('sm-add-pioneer-modal').style.display='flex'" class="sm-btn" style="width:auto; padding:0 20px;">+ إضافة رائد جديد</button>
    </div>

    <div class="sm-table-container">
        <table class="sm-table">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>الاسم</th>
                    <th>الفرع/المحافظة</th>
                    <th>تاريخ الإضافة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($pioneers)): ?>
                    <tr><td colspan="5" style="text-align:center;">لا يوجد رواد مسجلين حالياً</td></tr>
                <?php else: foreach($pioneers as $p): ?>
                    <tr>
                        <td>
                            <?php if($p->photo_url): ?>
                                <img src="<?php echo esc_url($p->photo_url); ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                            <?php else: ?>
                                <span class="dashicons dashicons-admin-users" style="font-size:30px;"></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo esc_html($p->name); ?></strong></td>
                        <td><span class="sm-badge sm-badge-low"><?php echo esc_html($govs[$p->governorate] ?? $p->governorate); ?></span></td>
                        <td><?php echo date_i18n('Y/m/d', strtotime($p->created_at)); ?></td>
                        <td>
                            <button onclick="smDeletePioneer(<?php echo $p->id; ?>)" class="sm-btn" style="background:#e53e3e; padding:4px 10px; font-size:12px;">حذف</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Pioneer Modal -->
<div id="sm-add-pioneer-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width:600px;">
        <div class="sm-modal-header">
            <h3>إضافة رائد مهنة جديد</h3>
            <button class="sm-modal-close" onclick="document.getElementById('sm-add-pioneer-modal').style.display='none'">&times;</button>
        </div>
        <form id="sm-add-pioneer-form" style="padding:20px;">
            <div class="sm-form-group">
                <label class="sm-label">الاسم الكامل:</label>
                <input type="text" name="name" class="sm-input" required>
            </div>
            <div class="sm-form-group">
                <label class="sm-label">التخصص أو المسمى المهني:</label>
                <input type="text" name="specialization" class="sm-input" required placeholder="مثال: خبير تأهيل رياضي">
            </div>
            <div class="sm-form-group">
                <label class="sm-label">الصورة الشخصية (رابط):</label>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="photo_url" id="sm-pioneer-photo-url" class="sm-input">
                    <button type="button" onclick="smOpenMediaUploader('sm-pioneer-photo-url')" class="sm-btn" style="width:auto; font-size:12px; background:#4a5568;">رفع</button>
                </div>
            </div>
            <div class="sm-form-group">
                <label class="sm-label">الفرع / المحافظة:</label>
                <select name="governorate" class="sm-select" required>
                    <?php foreach($govs as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                </select>
            </div>
            <div class="sm-form-group">
                <label class="sm-label">السيرة الذاتية والنبذة المهنية:</label>
                <textarea name="bio" class="sm-textarea" rows="6" required></textarea>
            </div>
            <button type="submit" class="sm-btn" style="width:100%; height:50px; font-weight:800;">حفظ ونشر البيانات</button>
        </form>
    </div>
</div>

<script>
document.getElementById('sm-add-pioneer-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'جاري الحفظ...';

    const fd = new FormData(this);
    fd.append('action', 'sm_add_pioneer');
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    fetch(ajaxurl, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم إضافة رائد المهنة بنجاح');
            setTimeout(() => location.reload(), 1000);
        } else {
            smHandleAjaxError(res.data, 'فشل الإضافة');
            btn.disabled = false;
            btn.innerText = 'حفظ ونشر البيانات';
        }
    }).catch(err => {
        smHandleAjaxError(err);
        btn.disabled = false;
        btn.innerText = 'حفظ ونشر البيانات';
    });
});

function smDeletePioneer(id) {
    if (!confirm('هل أنت متأكد من حذف هذا السجل؟ لا يمكن التراجع عن هذا الإجراء.')) return;

    const fd = new FormData();
    fd.append('action', 'sm_delete_pioneer');
    fd.append('id', id);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    fetch(ajaxurl, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم الحذف بنجاح');
            setTimeout(() => location.reload(), 500);
        } else {
            smHandleAjaxError(res.data, 'فشل الحذف');
        }
    }).catch(err => smHandleAjaxError(err));
}
</script>
