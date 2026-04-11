<?php if (!defined('ABSPATH')) exit;
$govs = SM_Settings::get_governorates();
$pioneers = SM_DB_Pioneers::get_pioneers([
    'search' => $_GET['q'] ?? '',
    'specialization' => $_GET['spec'] ?? '',
    'governorate' => $_GET['gov'] ?? '',
    'status' => $_GET['status'] ?? ''
]);
?>

<div class="sm-pioneers-admin">
    <!-- Header Section -->
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:15px; padding:25px; margin-bottom:25px; box-shadow:0 4px 6px rgba(0,0,0,0.02);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h2 style="margin:0; font-weight:900; color:var(--sm-dark-color); font-size:1.6em;">إدارة رواد المهنة</h2>
                <p style="color:#64748b; margin-top:5px; font-size:13px;">تخصيص لوحة الشرف المعتمدة للمتميزين والمبدعين في المجال</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="<?php echo home_url('/industry-pioneers'); ?>" target="_blank" class="sm-btn sm-btn-outline" style="width:auto; height:42px; padding:0 20px; font-weight:700; border-radius:10px; display:flex; align-items:center; text-decoration:none; border-color:#cbd5e0; color:#4a5568;">
                    <span class="dashicons dashicons-external" style="margin-left:8px; font-size:18px;"></span> عرض بوابة الرواد
                </a>
                <button onclick="smOpenPioneerWizard()" class="sm-btn" style="width:auto; height:42px; padding:0 25px; font-weight:800; border-radius:10px; background:var(--sm-primary-color); box-shadow:none;">
                    <span class="dashicons dashicons-plus-alt" style="margin-top:4px;"></span> إضافة رائد جديد
                </button>
            </div>
        </div>

        <!-- Advanced Search Engine -->
        <form method="get" style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap:12px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #edf2f7;">
            <input type="hidden" name="sm_tab" value="pioneers">
            <div class="sm-form-group" style="margin:0;">
                <input type="text" name="q" value="<?php echo esc_attr($_GET['q'] ?? ''); ?>" placeholder="الاسم أو الكلمات المفتاحية..." class="sm-input" style="background:#fff;">
            </div>
            <div class="sm-form-group" style="margin:0;">
                <input type="text" name="spec" value="<?php echo esc_attr($_GET['spec'] ?? ''); ?>" placeholder="التخصص..." class="sm-input" style="background:#fff;">
            </div>
            <div class="sm-form-group" style="margin:0;">
                <select name="gov" class="sm-select" style="background:#fff;">
                    <option value="">كافة الفروع</option>
                    <?php foreach($govs as $k => $v) echo "<option value='$k' ".selected($_GET['gov'] ?? '', $k, false).">$v</option>"; ?>
                </select>
            </div>
            <div class="sm-form-group" style="margin:0;">
                <select name="status" class="sm-select" style="background:#fff;">
                    <option value="">كافة الحالات</option>
                    <option value="active" <?php selected($_GET['status'] ?? '', 'active'); ?>>نشط (ظاهر)</option>
                    <option value="inactive" <?php selected($_GET['status'] ?? '', 'inactive'); ?>>معطل (مخفي)</option>
                </select>
            </div>
            <button type="submit" class="sm-btn" style="width:auto; padding:0 25px; background:var(--sm-dark-color);">بحث</button>
            <?php if(!empty($_GET['q']) || !empty($_GET['spec']) || !empty($_GET['gov']) || !empty($_GET['status'])): ?>
                <a href="<?php echo add_query_arg('sm_tab', 'pioneers'); ?>" class="sm-btn sm-btn-outline" style="width:auto; padding:0 20px; text-decoration:none; display:flex; align-items:center;">حذف الفلاتر</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Management Table -->
    <div class="sm-table-container" style="background:#fff; border:1px solid #e2e8f0; border-radius:15px; box-shadow:0 4px 6px rgba(0,0,0,0.02);">
        <table class="sm-table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>الرائد</th>
                    <th>التخصص والفرع</th>
                    <th>الحالة</th>
                    <th style="text-align:left;">إجراءات الإدارة</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($pioneers)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">لا توجد بيانات مسجلة حالياً تطابق الفلاتر المختارة</td></tr>
                <?php else: foreach($pioneers as $idx => $p): ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="color:#94a3b8; font-weight:700;"><?php echo $idx + 1; ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:15px;">
                                <div style="width:45px; height:45px; border-radius:12px; overflow:hidden; border:2px solid #eee; flex-shrink:0;">
                                    <?php if($p->photo_url): ?>
                                        <img src="<?php echo esc_url($p->photo_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:100%; height:100%; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:#cbd5e0;">
                                            <span class="dashicons dashicons-admin-users"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight:900; color:var(--sm-dark-color); font-size:1.1em;"><?php echo esc_html($p->name); ?></div>
                                    <div style="font-size:11px; color:#64748b; margin-top:2px;">تمت الإضافة: <?php echo date_i18n('j M Y', strtotime($p->created_at)); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:700; color:var(--sm-primary-color); font-size:13px;"><?php echo esc_html($p->specialization); ?></div>
                            <div style="font-size:11px; color:#64748b; margin-top:2px;"><?php echo esc_html($govs[$p->governorate] ?? $p->governorate); ?></div>
                        </td>
                        <td>
                            <span class="sm-badge <?php echo $p->status === 'active' ? 'sm-badge-high' : 'sm-badge-low'; ?>" style="font-size:10px; padding:4px 12px;">
                                <?php echo $p->status === 'active' ? 'نشط (ظاهر)' : 'معطل (مخفي)'; ?>
                            </span>
                        </td>
                        <td style="text-align:left;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="<?php echo esc_url(home_url('/p/' . $p->slug)); ?>" target="_blank" class="sm-btn sm-btn-outline" style="padding:6px 12px; font-size:11px; font-weight:800; border-color:#cbd5e0; text-decoration:none; display:inline-flex; align-items:center;">عرض الملف</a>
                                <button onclick="smEditPioneer(<?php echo $p->id; ?>)" class="sm-btn sm-btn-outline" style="padding:6px 12px; font-size:11px; font-weight:800; border-color:#cbd5e0;">تعديل</button>
                                <button onclick="smTogglePioneerStatus(<?php echo $p->id; ?>)" class="sm-btn" style="padding:6px 12px; font-size:11px; font-weight:800; background:<?php echo $p->status === 'active' ? '#718096' : '#38a169'; ?>;">
                                    <?php echo $p->status === 'active' ? 'إخفاء' : 'إظهار'; ?>
                                </button>
                                <button onclick="smDeletePioneer(<?php echo $p->id; ?>)" class="sm-btn" style="padding:6px 12px; font-size:11px; font-weight:800; background:#e53e3e;">حذف</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 5-Step Wizard Modal -->
<div id="sm-pioneer-wizard-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width:850px; padding:0; overflow:hidden; border-radius:24px;">
        <div class="sm-modal-header" style="background:#f8fafc; padding:25px; border-bottom:1px solid #eee; margin:0;">
            <h3 id="wizard-title" style="font-weight:900; margin:0;">إضافة رائد مهنة جديد</h3>
            <button class="sm-modal-close" onclick="smClosePioneerWizard()">&times;</button>
        </div>

        <!-- Step Progress Bar -->
        <div style="background:#fff; padding:20px 40px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; position:relative;">
            <div style="position:absolute; top:40px; left:40px; right:40px; height:2px; background:#eee; z-index:1;"></div>
            <div id="wizard-progress-bar" style="position:absolute; top:40px; right:40px; width:0; height:2px; background:var(--sm-primary-color); z-index:2; transition:0.4s;"></div>

            <?php for($i=1; $i<=5; $i++):
                $labels = [1=>'الشخصية', 2=>'التخصص', 3=>'الخبرات', 4=>'السيرة الذاتية', 5=>'النشر'];
            ?>
                <div class="wizard-step-indicator" id="step-ind-<?php echo $i; ?>" style="position:relative; z-index:3; text-align:center; transition:0.3s;">
                    <div class="step-num" style="width:36px; height:36px; border-radius:50%; background:#fff; border:2px solid #eee; display:flex; align-items:center; justify-content:center; margin:0 auto 8px; font-weight:800; color:#94a3b8; font-size:14px;"><?php echo $i; ?></div>
                    <div style="font-size:11px; font-weight:800; color:#94a3b8;"><?php echo $labels[$i]; ?></div>
                </div>
            <?php endfor; ?>
        </div>

        <form id="sm-pioneer-wizard-form" style="padding:0; margin:0;">
            <input type="hidden" name="id" id="wizard-pioneer-id">

            <div style="padding:40px; max-height:60vh; overflow-y:auto;">
                <!-- Step 1: Personal Info -->
                <div class="wizard-step" id="wizard-step-1">
                    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px; align-items:center;">
                        <div style="text-align:center;">
                            <div id="wizard-photo-preview" style="width:180px; height:220px; background:#f8fafc; border:2px dashed #cbd5e0; border-radius:15px; margin:0 auto 15px; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                                <span class="dashicons dashicons-camera" style="font-size:40px; width:40px; height:40px; color:#94a3b8;"></span>
                            </div>
                            <input type="hidden" name="photo_url" id="wizard-photo-url">
                            <button type="button" onclick="smOpenMediaUploader('wizard-photo-url', 'wizard-photo-preview')" class="sm-btn sm-btn-outline" style="width:auto; padding:5px 20px; font-size:12px;">رفع صورة الملف</button>
                        </div>
                        <div style="display:grid; gap:20px;">
                            <div class="sm-form-group">
                                <label class="sm-label">الاسم الكامل (كما سيظهر في الموقع):</label>
                                <input type="text" name="name" class="sm-input" required placeholder="أدخل الاسم الرباعي أو المعتمد...">
                            </div>
                            <div class="sm-form-group">
                                <label class="sm-label">الفرع / المحافظة التابع لها:</label>
                                <select name="governorate" class="sm-select" required>
                                    <option value="">اختر الفرع...</option>
                                    <?php foreach($govs as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Specialization -->
                <div class="wizard-step" id="wizard-step-2" style="display:none;">
                    <div style="display:grid; gap:25px;">
                        <div class="sm-form-group">
                            <label class="sm-label">المسمى المهني الرئيسي:</label>
                            <input type="text" name="specialization" class="sm-input" required placeholder="مثال: استشاري تأهيل رياضي، رئيس قسم الإصابات...">
                        </div>
                        <div style="background:#fffcf2; padding:20px; border-radius:12px; border:1px solid #feebc8;">
                            <p style="margin:0; font-size:13px; color:#92400e; font-weight:700;">ملاحظة: سيتم عرض هذا المسمى أسفل اسم الرائد مباشرة في الكرت التعريفي.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Experience & Achievements -->
                <div class="wizard-step" id="wizard-step-3" style="display:none;">
                    <div style="display:grid; gap:30px;">
                        <div class="sm-form-group">
                            <label class="sm-label">ملخص الخبرات العملية والدرجات الأكاديمية:</label>
                            <textarea name="experience" class="sm-textarea" rows="5" placeholder="أدخل ملخص للمسار المهني..."></textarea>
                        </div>
                        <div class="sm-form-group">
                            <label class="sm-label">أهم الإنجازات والجوائز الممنوحة:</label>
                            <textarea name="achievements" class="sm-textarea" rows="5" placeholder="أدخل قائمة بالإنجازات أو الأبحاث الهامة..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Biography Editor -->
                <div class="wizard-step" id="wizard-step-4" style="display:none;">
                    <div class="sm-form-group">
                        <label class="sm-label" style="display:flex; justify-content:space-between; align-items:center;">
                            السيرة الذاتية المفصلة (Rich Text Editor)
                            <span style="font-size:11px; color:var(--sm-primary-color); font-weight:700;">* تدعم التنسيق المتقدم</span>
                        </label>
                        <div id="bio-editor-container" style="min-height:300px; border:1px solid #ddd; border-radius:10px;">
                            <!-- TinyMCE or similar will be loaded here -->
                            <textarea name="bio" id="pioneer-bio-editor" class="sm-textarea" style="height:300px;"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Review & Publish -->
                <div class="wizard-step" id="wizard-step-5" style="display:none;">
                    <div style="text-align:center; padding:20px 0;">
                        <div style="width:80px; height:80px; background:#f0fff4; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; color:#38a169;">
                            <span class="dashicons dashicons-yes-alt" style="font-size:50px; width:50px; height:50px;"></span>
                        </div>
                        <h3 style="font-weight:900; margin-bottom:10px;">اكتملت مراجعة البيانات</h3>
                        <p style="color:#64748b; font-size:14px;">يرجى التأكد من دقة البيانات المدخلة قبل النشر النهائي في لوحة الشرف.</p>

                        <div id="wizard-review-summary" style="margin-top:30px; background:#f8fafc; border:1px solid #eee; border-radius:15px; padding:25px; text-align:right;">
                            <!-- Summary JS injection -->
                        </div>

                        <div class="sm-form-group" style="margin-top:25px; text-align:right;">
                            <label class="sm-label">خيارات النشر:</label>
                            <select name="status" class="sm-select">
                                <option value="active">نشر الآن (ظهور فوري للعامة)</option>
                                <option value="inactive">حفظ كمسودة (مخفي من الموقع حالياً)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:#f8fafc; padding:25px 40px; border-top:1px solid #eee; display:flex; align-items:center; min-height:100px;">
                <div style="width:150px;">
                    <button type="button" id="wizard-prev-btn" onclick="smWizardPrev()" class="sm-btn sm-btn-outline" style="width:100%; padding:0; height:45px; display:none; font-weight:800;">&larr; الخطوة السابقة</button>
                </div>
                <div style="flex:1;"></div>
                <div style="width:200px;">
                    <button type="button" id="wizard-next-btn" onclick="smWizardNext()" class="sm-btn" style="width:100%; padding:0; height:45px; background:var(--sm-dark-color); font-weight:800;">الخطوة التالية &rarr;</button>
                    <button type="submit" id="wizard-submit-btn" class="sm-btn" style="width:100%; padding:0; height:45px; display:none; background:#38a169; font-weight:800;">نشر وتثبيت في لوحة الشرف</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 5;

function smOpenPioneerWizard() {
    currentStep = 1;
    document.getElementById('wizard-pioneer-id').value = '';
    document.getElementById('sm-pioneer-wizard-form').reset();
    document.getElementById('wizard-photo-preview').innerHTML = '<span class="dashicons dashicons-camera" style="font-size:40px; width:40px; height:40px; color:#94a3b8;"></span>';
    document.getElementById('wizard-title').innerText = 'إضافة رائد مهنة جديد';
    updateWizardUI();
    document.getElementById('sm-pioneer-wizard-modal').style.display = 'flex';
}

function smClosePioneerWizard() {
    if(confirm('هل أنت متأكد من إغلاق المعالج؟ سيتم فقدان التغييرات غير المحفوظة.')) {
        document.getElementById('sm-pioneer-wizard-modal').style.display = 'none';
    }
}

function smWizardNext() {
    if (currentStep < totalSteps) {
        // Simple validation for step 1
        if (currentStep === 1) {
            const name = document.querySelector('input[name="name"]').value;
            const gov = document.querySelector('select[name="governorate"]').value;
            if (!name || !gov) {
                smShowNotification('يرجى ملء كافة الحقول الأساسية', true);
                return;
            }
        }

        currentStep++;
        updateWizardUI();
        if (currentStep === 5) renderWizardReview();
    }
}

function smWizardPrev() {
    if (currentStep > 1) {
        currentStep--;
        updateWizardUI();
    }
}

function updateWizardUI() {
    document.querySelectorAll('.wizard-step').forEach(s => s.style.display = 'none');
    document.getElementById('wizard-step-' + currentStep).style.display = 'block';

    // Update indicators
    document.querySelectorAll('.wizard-step-indicator').forEach((ind, i) => {
        const num = ind.querySelector('.step-num');
        const label = ind.querySelector('div:last-child');
        if (i + 1 < currentStep) {
            num.style.background = '#38a169';
            num.style.borderColor = '#38a169';
            num.style.color = '#fff';
            num.innerHTML = '✓';
        } else if (i + 1 === currentStep) {
            num.style.background = 'var(--sm-primary-color)';
            num.style.borderColor = 'var(--sm-primary-color)';
            num.style.color = '#fff';
            num.innerHTML = i + 1;
            label.style.color = 'var(--sm-primary-color)';
        } else {
            num.style.background = '#fff';
            num.style.borderColor = '#eee';
            num.style.color = '#94a3b8';
            num.innerHTML = i + 1;
            label.style.color = '#94a3b8';
        }
    });

    document.getElementById('wizard-progress-bar').style.width = ((currentStep - 1) / (totalSteps - 1) * 100) + '%';

    document.getElementById('wizard-prev-btn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('wizard-next-btn').style.display = currentStep < totalSteps ? 'block' : 'none';
    document.getElementById('wizard-submit-btn').style.display = currentStep === totalSteps ? 'block' : 'none';
}

function renderWizardReview() {
    const f = document.getElementById('sm-pioneer-wizard-form');
    const summary = document.getElementById('wizard-review-summary');
    const name = f.name.value;
    const spec = f.specialization.value;
    const gov = f.governorate.options[f.governorate.selectedIndex].text;

    summary.innerHTML = `
        <div style="display:flex; align-items:center; gap:20px; margin-bottom:20px;">
            <div style="width:60px; height:60px; border-radius:10px; overflow:hidden; border:1px solid #ddd; background:#fff;">
                <img src="${f.photo_url.value || ''}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%23eee%22/><text y=%2250%%22 x=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22sans-serif%22 font-size=%2220%22 fill=%22%23999%22>No Photo</text></svg>'">
            </div>
            <div style="flex:1;">
                <div style="font-weight:900; font-size:1.2em;">${name}</div>
                <div style="color:var(--sm-primary-color); font-weight:700;">${spec} - ${gov}</div>
            </div>
        </div>
        <div style="font-size:12px; line-height:1.6; color:#64748b;">
            سيتم نشر هذا الملف الشخصي في "بوابة رواد المهنة" وسيكون متاحاً للمشاركة والبحث عبر المنصة الرسمية.
        </div>
    `;
}

// Override smOpenMediaUploader to support custom preview
const oldMediaUploader = window.smOpenMediaUploader;
window.smOpenMediaUploader = function(inputId, previewId = null) {
    const frame = wp.media({
        title: 'اختر صورة الرائد',
        button: { text: 'استخدام هذه الصورة' },
        multiple: false
    });
    frame.on('select', function() {
        const attachment = frame.state().get('selection').first().toJSON();
        document.getElementById(inputId).value = attachment.url;
        if (previewId) {
            document.getElementById(previewId).innerHTML = `<img src="${attachment.url}" style="width:100%; height:100%; object-fit:cover;">`;
        }
    });
    frame.open();
};

document.getElementById('sm-pioneer-wizard-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('wizard-submit-btn');
    btn.disabled = true;
    btn.innerText = 'جاري النشر...';

    const pid = document.getElementById('wizard-pioneer-id').value;
    const action = pid ? 'sm_edit_pioneer' : 'sm_add_pioneer';

    const fd = new FormData(this);
    fd.append('action', action);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    fetch(ajaxurl, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تمت العملية بنجاح');
            setTimeout(() => location.reload(), 1000);
        } else {
            smHandleAjaxError(res.data, 'فشل تنفيذ العملية');
            btn.disabled = false;
            btn.innerText = 'نشر وتثبيت في لوحة الشرف';
        }
    }).catch(err => {
        smHandleAjaxError(err);
        btn.disabled = false;
        btn.innerText = 'نشر وتثبيت في لوحة الشرف';
    });
});

function smEditPioneer(id) {
    smShowNotification('جاري تحميل بيانات الرائد...');
    fetch(ajaxurl + '?action=sm_get_pioneer_details&id=' + id + '&nonce=<?php echo wp_create_nonce("sm_admin_action"); ?>')
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const p = res.data;
            const f = document.getElementById('sm-pioneer-wizard-form');
            document.getElementById('wizard-pioneer-id').value = p.id;
            f.name.value = p.name;
            f.governorate.value = p.governorate;
            f.specialization.value = p.specialization;
            f.experience.value = p.experience;
            f.achievements.value = p.achievements;
            f.bio.value = p.bio;
            f.photo_url.value = p.photo_url;
            f.status.value = p.status;

            if (p.photo_url) {
                document.getElementById('wizard-photo-preview').innerHTML = `<img src="${p.photo_url}" style="width:100%; height:100%; object-fit:cover;">`;
            }

            document.getElementById('wizard-title').innerText = 'تعديل بيانات رائد المهنة';
            currentStep = 1;
            updateWizardUI();
            document.getElementById('sm-pioneer-wizard-modal').style.display = 'flex';
        }
    });
}

function smTogglePioneerStatus(id) {
    const fd = new FormData();
    fd.append('action', 'sm_toggle_pioneer_status');
    fd.append('id', id);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');
    fetch(ajaxurl, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
        if (res.success) location.reload();
    });
}

function smDeletePioneer(id) {
    if (!confirm('تحذير: سيتم حذف هذا الملف نهائياً من لوحة الشرف. هل أنت متأكد؟')) return;
    const fd = new FormData();
    fd.append('action', 'sm_delete_pioneer');
    fd.append('id', id);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');
    fetch(ajaxurl, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
        if (res.success) location.reload();
        else smHandleAjaxError(res.data);
    });
}
</script>

<style>
.wizard-step-indicator .step-num { transition: 0.3s; }
.sm-badge { border-radius:50px; font-weight:800; border:1px solid rgba(0,0,0,0.05); }
.sm-pioneer-card:hover { transform: translateY(-3px); }
</style>
