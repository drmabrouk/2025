<?php if (!defined('ABSPATH')) exit;

if (empty($results)) {
    echo '<div style="text-align: center; padding: 80px 40px; background: #fff; border-radius: 20px; border: 2px dashed #e2e8f0;">
            <div style="font-size: 50px; margin-bottom: 20px;">🔍</div>
            <h3 style="color: var(--sm-dark-color); font-weight: 800;">لم يتم العثور على أبحاث تطابق البحث</h3>
            <p style="color: #94a3b8;">يرجى تجربة كلمات مفتاحية أخرى أو تغيير الفلاتر المطبقة.</p>
          </div>';
    return;
}

foreach ($results as $res):
    $type_map = [
        'journal_article' => 'مقال علمي محكم',
        'master_thesis' => 'رسالة ماجستير',
        'phd_dissertation' => 'أطروحة دكتوراه',
        'case_study' => 'دراسة حالة',
        'book_chapter' => 'فصل في كتاب'
    ];
    $uni_name = SM_Settings::get_universities()[$res->university] ?? $res->university;
?>
    <div class="sm-research-card <?php echo $res->is_featured ? 'featured' : ''; ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; gap: 20px;">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <span class="sm-badge sm-badge-low" style="background: rgba(246, 48, 73, 0.08); color: var(--sm-primary-color); border-radius: 8px; font-weight: 800; font-size: 11px;">
                        <?php echo $type_map[$res->research_type] ?? $res->research_type; ?>
                    </span>
                    <span style="color: #94a3b8; font-size: 11px; font-weight: 600;">
                        <span class="dashicons dashicons-calendar-alt" style="font-size: 14px; width: 14px; height: 14px; margin-left: 4px;"></span>
                        <?php echo date('Y/m/d', strtotime($res->submitted_at)); ?>
                    </span>
                </div>
                <h3 style="margin: 0 0 10px 0; font-weight: 900; color: var(--sm-dark-color); font-size: 1.3em; line-height: 1.4;">
                    <?php echo esc_html($res->title); ?>
                </h3>
                <div style="display: flex; gap: 15px; flex-wrap: wrap; color: #64748b; font-size: 13px; font-weight: 600;">
                    <span><span class="dashicons dashicons-admin-users" style="font-size:16px; width:16px; height:16px; margin-left:5px;"></span><?php echo esc_html($res->authors); ?></span>
                    <span><span class="dashicons dashicons-bank" style="font-size:16px; width:16px; height:16px; margin-left:5px;"></span><?php echo esc_html($uni_name); ?></span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <button onclick="smPreviewResearch('<?php echo esc_url($res->file_url); ?>', '<?php echo esc_js($res->title); ?>')" class="sm-btn" style="height: 40px; padding: 0 15px; font-size: 12px; background: #2d3748;">
                    <span class="dashicons dashicons-visibility" style="margin-left:5px;"></span> معاينة
                </button>
                <button onclick="smDownloadResearch('<?php echo esc_url($res->file_url); ?>')" class="sm-btn" style="height: 40px; padding: 0 15px; font-size: 12px;">
                    <span class="dashicons dashicons-download" style="margin-left:5px;"></span> تحميل PDF
                </button>
            </div>
        </div>

        <div style="border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: 15px;">
            <button onclick="smToggleResearchAbstract(<?php echo $res->id; ?>)" style="background: none; border: none; color: var(--sm-primary-color); font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 5px; font-size: 13px; padding: 0;">
                عرض ملخص الدراسة <span id="research-icon-<?php echo $res->id; ?>" class="dashicons dashicons-arrow-down-alt2" style="transition: 0.3s; font-size: 18px;"></span>
            </button>
            <div id="research-abstract-<?php echo $res->id; ?>" style="display: none; margin-top: 15px; color: #4a5568; line-height: 1.8; font-size: 14px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #edf2f7;">
                <?php echo nl2br(esc_html($res->abstract)); ?>
                <div style="margin-top: 15px; display: flex; gap: 10px; font-size: 11px; font-weight: 700;">
                    <span style="background: #fff; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 6px;">القسم: <?php echo SM_Settings::get_departments()[$res->department] ?? $res->department; ?></span>
                    <span style="background: #fff; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 6px;">التخصص: <?php echo SM_Settings::get_specializations()[$res->specialization] ?? $res->specialization; ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
