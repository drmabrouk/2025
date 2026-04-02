<?php if (!defined('ABSPATH')) exit;

if (empty($results)) {
    echo '<div style="text-align: center; padding: 100px 40px; background: #fff; border-radius: 24px; border: 2px dashed #e2e8f0;">
            <div style="font-size: 60px; margin-bottom: 25px;">🔍</div>
            <h3 style="color: var(--sm-dark-color); font-weight: 900; font-size: 1.5em;">لم يتم العثور على أبحاث تطابق البحث</h3>
            <p style="color: #94a3b8; font-weight: 500;">يرجى تجربة كلمات مفتاحية أخرى أو تغيير الفلاتر المطبقة في القائمة الجانبية.</p>
          </div>';
    return;
}

foreach ($results as $res):
    $type_map = [
        'journal_article' => 'مقال علمي محكم',
        'master_thesis' => 'رسالة ماجستير',
        'phd_dissertation' => 'أطروحة دكتوراه',
        'case_study' => 'دراسة حالة (Case Study)',
        'systematic_review' => 'مراجعة منهجية (Systematic Review)',
        'meta_analysis' => 'تحليل شمولي (Meta-analysis)',
        'book_chapter' => 'فصل في كتاب'
    ];
    $uni_name = SM_Settings::get_universities()[$res->university] ?? $res->university;
    $is_fav = is_user_logged_in() && SM_DB_Research::is_favorite($res->id, get_current_user_id());
?>
    <div class="sm-research-card <?php echo $res->is_featured ? 'featured' : ''; ?>" data-id="<?php echo $res->id; ?>">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 25px;">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <span class="sm-badge" style="background: rgba(246, 48, 73, 0.06); color: var(--sm-primary-color); border-radius: 8px; font-weight: 800; font-size: 12px; padding: 5px 12px; border: 1px solid rgba(246, 48, 73, 0.1);">
                        <?php echo $type_map[$res->research_type] ?? $res->research_type; ?>
                    </span>
                    <span style="color: #94a3b8; font-size: 12px; font-weight: 600;">
                        <span class="dashicons dashicons-calendar-alt" style="font-size: 16px; width: 16px; height: 16px; margin-left: 5px; vertical-align: middle;"></span>
                        <?php echo $res->publication_year ?: date('Y', strtotime($res->submitted_at)); ?>
                    </span>
                </div>
                <h3 style="margin: 0 0 15px 0; font-weight: 900; color: var(--sm-dark-color); font-size: 1.5em; line-height: 1.4; letter-spacing: -0.5px;">
                    <?php echo esc_html($res->title); ?>
                </h3>
                <div style="display: flex; gap: 20px; flex-wrap: wrap; color: #64748b; font-size: 14px; font-weight: 600;">
                    <span style="display: flex; align-items: center; gap: 6px;"><span class="dashicons dashicons-admin-users" style="color: #cbd5e0;"></span> <?php echo esc_html($res->authors); ?></span>
                    <span style="display: flex; align-items: center; gap: 6px;"><span class="dashicons dashicons-bank" style="color: #cbd5e0;"></span> <?php echo esc_html($uni_name); ?></span>
                </div>
            </div>

            <div class="sm-card-metrics" style="display: flex; flex-direction: column; align-items: flex-end; gap: 15px;">
                <?php if(is_user_logged_in()): ?>
                    <button onclick="smToggleFavorite(<?php echo $res->id; ?>, this)" class="sm-btn sm-btn-outline" style="width: 40px; height: 40px; padding: 0; border-radius: 10px; border-color: #eee;">
                        <span class="dashicons <?php echo $is_fav ? 'dashicons-star-filled' : 'dashicons-star-empty'; ?>" style="color: <?php echo $is_fav ? '#d69e2e' : '#94a3b8'; ?>;"></span>
                    </button>
                <?php endif; ?>

                <div style="display: flex; gap: 15px; color: #94a3b8; font-size: 11px; font-weight: 800; background: #f8fafc; padding: 8px 15px; border-radius: 10px; border: 1px solid #edf2f7;">
                    <span title="مشاهدات"><span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; margin-left: 4px;"></span><?php echo number_format($res->view_count); ?></span>
                    <span title="إعجابات"><span class="dashicons dashicons-thumbs-up" style="font-size: 14px; width: 14px; height: 14px; margin-left: 4px;"></span><?php echo number_format($res->like_count); ?></span>
                    <span title="تحميلات"><span class="dashicons dashicons-download" style="font-size: 14px; width: 14px; height: 14px; margin-left: 4px;"></span><?php echo number_format($res->download_count); ?></span>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 20px; margin-top: 5px;">
            <div style="display: flex; gap: 12px;">
                <button onclick="smToggleResearchAbstract(<?php echo $res->id; ?>)" class="sm-btn sm-btn-outline" style="height: 42px; padding: 0 20px; font-size: 13px; font-weight: 800; border-radius: 10px; border-color: #e2e8f0; color: var(--sm-dark-color) !important;">
                    الملخص العلمي <span id="research-icon-<?php echo $res->id; ?>" class="dashicons dashicons-arrow-down-alt2" style="transition: 0.3s; margin-right: 5px;"></span>
                </button>
                <button onclick="smPreviewResearch(<?php echo $res->id; ?>, '<?php echo esc_url($res->file_url); ?>', '<?php echo esc_js($res->title); ?>')" class="sm-btn" style="height: 42px; padding: 0 20px; font-size: 13px; font-weight: 800; border-radius: 10px; background: #2d3748;">
                    <span class="dashicons dashicons-visibility" style="margin-left:8px;"></span> معاينة سريعة
                </button>
            </div>

            <button onclick="smDownloadResearch(<?php echo $res->id; ?>, '<?php echo esc_url($res->file_url); ?>')" class="sm-btn" style="height: 42px; padding: 0 25px; font-size: 13px; font-weight: 900; border-radius: 10px; box-shadow: 0 4px 10px rgba(246, 48, 73, 0.15);">
                <span class="dashicons dashicons-download" style="margin-left:8px;"></span> تحميل النسخة الكاملة (PDF)
            </button>
        </div>

        <div id="research-abstract-<?php echo $res->id; ?>" style="display: none; margin-top: 20px; color: #4a5568; line-height: 1.9; font-size: 14.5px; background: #fcfcfc; padding: 30px; border-radius: 15px; border: 1px solid #f1f5f9; animation: smFadeIn 0.3s ease;">
            <div style="margin-bottom: 20px;">
                <strong style="color: var(--sm-dark-color); display: block; margin-bottom: 10px;">ملخص الدراسة:</strong>
                <?php echo nl2br(esc_html($res->abstract)); ?>
            </div>

            <?php if($res->keywords): ?>
                <div style="margin-bottom: 20px; font-size: 13px;">
                    <strong style="color: var(--sm-dark-color);">الكلمات المفتاحية:</strong>
                    <span style="color: var(--sm-primary-color); font-weight: 600;"><?php echo esc_html($res->keywords); ?></span>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; background: #fff; padding: 15px; border-radius: 12px; border: 1px solid #eee;">
                <div style="font-size: 12px;"><strong style="color: #94a3b8;">المنهجية:</strong> <?php echo esc_html($res->methodology ?: 'غير محدد'); ?></div>
                <div style="font-size: 12px;"><strong style="color: #94a3b8;">عينة الدراسة:</strong> <?php echo esc_html($res->sample_size ?: 'غير محدد'); ?></div>
                <div style="font-size: 12px;"><strong style="color: #94a3b8;">القسم:</strong> <?php echo SM_Settings::get_departments()[$res->department] ?? $res->department; ?></div>
                <div style="font-size: 12px;"><strong style="color: #94a3b8;">التخصص:</strong> <?php echo SM_Settings::get_specializations()[$res->specialization] ?? $res->specialization; ?></div>
                <?php if($res->doi): ?>
                    <div style="font-size: 12px; grid-column: span 2;"><strong style="color: #94a3b8;">DOI:</strong> <a href="https://doi.org/<?php echo esc_attr($res->doi); ?>" target="_blank" style="color: #3182ce;"><?php echo esc_html($res->doi); ?></a></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
