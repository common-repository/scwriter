<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$nonce_name = SCWRITER_PREFIX.'_settings_nonce';
$nonce_action = SCWRITER_PREFIX.'_settings_updator';
$nonce = wp_create_nonce( $nonce_action );
$need_show_wizard = false;
$wizard_step = 'scwriter';
if (
    !isset($options['api_key']) || empty($options['api_key']) ||
    !isset($options['openai_api_key']) || empty($options['openai_api_key']) ||
    !isset($options['blog_topic']) || empty($options['blog_topic'])
) {
    $need_show_wizard = true;
}
if (!isset($options['api_key']) || empty($options['api_key'])) {
    $wizard_step = 'scwriter';
}
elseif (!isset($options['openai_api_key']) || empty($options['openai_api_key'])) {
    $wizard_step = 'openai';
}
elseif (!isset($options['blog_topic']) || empty($options['blog_topic'])) {
    $wizard_step = 'blog_topic';
}
?>

<div class="scwriter-tabs-header">
    <div class="scwriter-tabs-header-title">
        <h1><?php esc_html_e('SCwriter Settings', 'scwriter'); ?></h1>
    </div>

    <?php if ( !$need_show_wizard ) { ?>
            
        <nav class="scwriter-tabs-heading">
            <a href="#default" class="scwriter-tabs-heading-btn active"  data-tab="default"><?php esc_html_e('Default', 'scwriter'); ?></a>
            <a href="#api" class="scwriter-tabs-heading-btn"  data-tab="api"><?php esc_html_e('API Keys', 'scwriter'); ?></a>
            <a href="#automations" class="scwriter-tabs-heading-btn"  data-tab="automations"><?php esc_html_e('Automations', 'scwriter'); ?></a>
        </nav>
    
    <?php } ?>

</div>

<div class="scwriter-popup">
    <div class="scwriter-popup-overlay"></div>
    <div class="scwriter-popup-content">
        <button type="button" class="scwriter-popup-close dashicons-no-alt dashicons"></button>
        <div class="scwriter-popup-content-inner">
        </div>
    </div>
</div>

<div class="scwriter-padding-wrapper">
    <div class="form-messages"></div>
</div>

<?php if ( !$need_show_wizard ) { ?>
        
    <form action="#" class="scwriter-form scwriter-form-settings" method="POST">

        <div class="scwriter-tabs-contents">
            <div class="scwriter-tabs-content active" data-tab="default">

                <h2><?php esc_html_e('Blog Topic', 'scwriter'); ?></h2>
                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="blog_topic"><?php esc_html_e('Blog Topic', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Describe the main focus of your blog. What topics or themes will you cover?', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="blog_topic"
                            id="blog_topic"
                            class="scwriter-limit-words-input scwriter-validate-it"
                            data-limit="100"
                            required
                        ><?php echo esc_html($options['blog_topic'] ?? ''); ?></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                    <div class="scwriter-input-errors"></div>
                </div>

                <h2><?php esc_html_e('Visibility', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label><?php esc_html_e('User Roles', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Select the user roles that can create AI articles.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-form-checkboxes">
                        <?php 
                            global $wp_roles;

                            $all_roles = $wp_roles->roles;
                            $editable_roles = apply_filters('editable_roles', $all_roles);
                            foreach ($editable_roles as $slug => $editable_role) {
                                $show = true;
                                if ( $slug == 'administrator' ) {
                                    $show = is_multisite();
                                }
                                if ( $show ) {
                                    $checked = in_array($slug, $options['user_roles']);
                                    $checked_attr = $checked ? 'checked' : '';
                                    echo '<div class="scwriter-form-checkbox">';
                                    echo '<input type="checkbox" name="user_roles[]" id="user-role-'.esc_attr($slug).'" value="'.esc_attr($slug).'" '.esc_attr($checked_attr).'><label for="user-role-'.esc_attr($slug).'">'.esc_html($editable_role['name']).'</label>';
                                    echo '</div>';
                                }
                            }
                        ?>
                    </div>
                </div>

            </div>

            <div class="scwriter-tabs-content" data-tab="api">
                
                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="openai_api_key"><?php esc_html_e('OpenAI API Key', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php 
                                    printf(
                                        /* translators: %1$s: link to Open AI, %2$s: Note, %2$s: link to Open AI balance overview  */
                                        esc_html__( 'Input your OpenAI API key. %1$s %2$s you must add funds to your %3$s balance to use this service.', 'scwriter' ),
                                        sprintf(
                                            '<a href="%s" target="_blank">%s</a>',
                                            esc_url( 'https://help.openai.com/en/articles/4936850-where-do-i-find-my-openai-api-key' ),
                                            esc_html__( 'Where do I find my OpenAI API Key?', 'scwriter' )
                                        ),
                                        sprintf(
                                            '<b>%s</b>',
                                            esc_html__( 'Note:', 'scwriter' )
                                        ),
                                        sprintf(
                                            '<a href="%s" target="_blank">%s</a>',
                                            esc_url( 'https://platform.openai.com/account/billing/overview' ),
                                            esc_html__( 'OpenAI API account', 'scwriter' )
                                        ),
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                    <input
                        type="password"
                        name="openai_api_key"
                        id="openai_api_key"
                        required
                        value="<?php echo esc_attr($options['openai_api_key'] ?? ''); ?>"
                        placeholder="<?php esc_attr_e('Enter your OpenAI API key', 'scwriter'); ?>"
                        class="scwriter-validate-it"
                    >
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="api_key"><?php esc_html_e('SCwriter API Key', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Enter your API key for the SCwriter.', 'scwriter'); ?> <a href="<?php echo esc_url( SCWRITER_WEBSITE ); ?>?utm_source=scwriterplugin&utm_medium=settings" target="_blank"><?php esc_html_e('Where do I find my SCwriter API Key?', 'scwriter'); ?></a>
                            </div>
                        </div>
                    </div>
                    <input
                        type="password"
                        name="api_key"
                        id="api_key"
                        required
                        class="scwriter-validate-it"
                        value="<?php echo esc_attr($options['api_key'] ?? ''); ?>"
                    >
                    <div class="scwriter-input-errors"></div>
                </div>

            </div>

            <div class="scwriter-tabs-content" data-tab="automations">

                <h2><?php esc_html_e('Automatic Article Creation', 'scwriter'); ?></h2>

                <?php if ( $options['presets'] ) { ?>
                        
                    <div class="scwriter-presets-row">
                        <div class="scwriter-presets-row-top">
                            <label for="automated_author"><?php esc_html_e('Author', 'scwriter'); ?> <sup>*</sup></label>
                            <div class="scwriter-form-hint">
                                <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                                <div class="scwriter-form-hint-content">
                                    <?php esc_html_e('Choose the author of the automated created articles.', 'scwriter'); ?></a>
                                </div>
                            </div>
                        </div>
                        <?php 
                            $selected_user = isset($options['automated_author']) && $options['automated_author'] ? $options['automated_author'] : false;
                            $users = get_users(array(
                                'orderby' => 'display_name',
                            ));
                            
                            if ( !$selected_user ) {
                                foreach ( $users as $user ) {
                                    if ( in_array('administrator', $user->roles) ) {
                                        $selected_user = $user->ID;
                                        break;
                                    }
                                }
                            }
                        ?>
                        <select name="automated_author" id="automated_author" required class="scwriter-validate-it">
                            <option value=""><?php esc_html_e('Select author', 'scwriter'); ?></option>
                            <?php
                                foreach ( $users as $user) {
                                    $selected = $selected_user == $user->ID ? 'selected' : '';
                            ?>
                                <option value="<?php echo esc_attr($user->ID); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($user->data->display_name); ?></option>
                            <?php } ?>
                        </select>
                        <div class="scwriter-input-errors"></div>
                    </div>

                    <div class="scwriter-presets-row">
                        <div class="scwriter-presets-row-top">
                            <label for="trends"><?php esc_html_e('Trending Keywords', 'scwriter'); ?></label>
                            <div class="scwriter-form-hint">
                                <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                                <div class="scwriter-form-hint-content">
                                    <?php esc_html_e('Enter trending keywords, one per line. The AI will use each keyword to generate a new article, with one trend per article. If no keywords are provided, the AI will select the most relevant and trending keywords automatically.', 'scwriter'); ?></a>
                                </div>
                            </div>
                        </div>
                        <textarea name="trends" id="trends" rows="5"><?php echo esc_html($options['trends'] ?? ''); ?></textarea>
                    </div>

                    <div class="scwriter-presets-row">
                        <div class="scwriter-presets-row-top">
                            <label for="publish_frequency"><?php esc_html_e('Frequency', 'scwriter'); ?></label>
                            <div class="scwriter-form-hint">
                                <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                                <div class="scwriter-form-hint-content">
                                    <?php esc_html_e('Choose how frequently articles should be automatically created.', 'scwriter'); ?></a>
                                </div>
                            </div>
                        </div>
                        <select name="publish_frequency" id="publish_frequency">
                            <?php foreach ( \SCwriter\Enums\SCwriter_Frequency::getNames() as $frequency => $frequency_name) { ?>
                                <?php $selected = isset($options['publish_frequency']) && $options['publish_frequency'] == $frequency ? 'selected' : ''; ?>
                                <option value="<?php echo esc_attr($frequency); ?>" <?php echo esc_html($selected); ?> ><?php echo esc_html($frequency_name); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="scwriter-presets-row">
                        <div class="scwriter-presets-row-top">
                            <label for="preset"><?php esc_html_e('Preset', 'scwriter'); ?> <sup>*</sup></label>
                            <div class="scwriter-form-hint">
                                <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                                <div class="scwriter-form-hint-content">
                                    <?php esc_html_e('Select a preset to be used for automated article creation.', 'scwriter'); ?></a>
                                </div>
                            </div>
                        </div>
                        <select name="preset" id="preset">
                            <?php foreach ( $options['presets'] as $preset ) { ?>
                                <?php $selected = isset($options['default_preset']) && $options['default_preset'] == $preset['preset_id'] ? 'selected' : ''; ?>
                                <option value="<?php echo esc_attr($preset['preset_id']); ?>" <?php echo esc_html($selected); ?> ><?php echo esc_html($preset['preset_name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                <?php } else { ?>

                    <?php 
                        printf(
                            /* translators: %1$s: link to Preset Management */
                            esc_html__( 'Please %1$s to enable "Automatic Article Creation"', 'scwriter' ),
                            sprintf(
                                '<a href="%s">%s</a>',
                                esc_url( admin_url('admin.php?page=scwriter-presets') ),
                                esc_html__( 'create preset', 'scwriter' )
                            )
                        );
                    ?>

                <?php } ?>

            </div>

        </div>

        <div class="scwriter-form-save">
            <button type="submit" class="button button-primary scwriter-form-saver"><?php esc_html_e('Save Settings', 'scwriter'); ?> <span class="scwriter-loading"></span></button>
        </div>

        <input type="hidden" name="action" value="scwriter_update_settings">
        <input type="hidden" name="<?php echo $nonce_name; ?>" value="<?php echo $nonce; ?>">

    </form>

<?php } else { ?>

    <?php if ( $wizard_step == 'scwriter' ) { ?>

        <div class="scwriter-wizard-step active">
                
            <div class="scwriter-wizard-btns">
                <button 
                    type="button" 
                    class="scwriter-wizard-connect button-primary"
                    data-nonce="<?php echo $nonce; ?>"
                ><?php esc_html_e('Connect SCwriter', 'scwriter'); ?> <span class="scwriter-loading"></span></button>
            
                <span class="scwriter-wizard-or">OR</span>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <a href="#i-have-api-key" class="scwriter-wizard-open"><?php esc_html_e('I have the API Key', 'scwriter'); ?></a>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('You can find your SCwriter API key', 'scwriter'); ?> <a href="<?php echo esc_url( SCWRITER_WEBSITE ); ?>?utm_source=scwriterplugin&utm_medium=settings" target="_blank"><?php esc_html_e('here', 'scwriter'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="#" class="scwriter-form scwriter-form-wizard" data-step="scwriter" method="POST">

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="openai_api_key"><?php esc_html_e('Enter SCwriter API Key', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Enter your API key for the SCwriter.', 'scwriter'); ?> <a href="<?php echo esc_url( SCWRITER_WEBSITE ); ?>?utm_source=scwriterplugin&utm_medium=settings" target="_blank"><?php esc_html_e('Where do I find my SCwriter API Key?', 'scwriter'); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-presets-row-top">
                        <input
                            type="password"
                            name="api_key"
                            id="api_key"
                            required
                            class="scwriter-validate-it"
                            value=""
                        >
                    </div>
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-form-save">
                    <button type="submit" class="button button-primary scwriter-form-saver-scwriter_apikey"><?php esc_html_e('Save SCwriter API Key', 'scwriter'); ?> <span class="scwriter-loading"></span></button>
                </div>

                <input type="hidden" name="action" value="scwriter_wizard_add_scwriter">
                <input type="hidden" name="<?php echo $nonce_name; ?>" value="<?php echo $nonce; ?>">

            </form>

        </div>

    <?php } ?>

    <?php if ( in_array( $wizard_step, ['scwriter', 'openai'] ) ) { ?>

        <div class="scwriter-wizard-step <?php echo $wizard_step == 'openai' ? 'active' : ''; ?>">
            
            <form action="#" class="scwriter-form scwriter-form-wizard" data-step="openai" method="POST">

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="openai_api_key"><?php esc_html_e('OpenAI API Key', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php 
                                    printf(
                                        /* translators: %1$s: link to Open AI, %2$s: Note, %2$s: link to Open AI balance overview  */
                                        esc_html__( 'Input your OpenAI API key. %1$s %2$s you must add funds to your %3$s balance to use this service.', 'scwriter' ),
                                        sprintf(
                                            '<a href="%s" target="_blank">%s</a>',
                                            esc_url( 'https://help.openai.com/en/articles/4936850-where-do-i-find-my-openai-api-key' ),
                                            esc_html__( 'Where do I find my OpenAI API Key?', 'scwriter' )
                                        ),
                                        sprintf(
                                            '<b>%s</b>',
                                            esc_html__( 'Note:', 'scwriter' )
                                        ),
                                        sprintf(
                                            '<a href="%s" target="_blank">%s</a>',
                                            esc_url( 'https://platform.openai.com/account/billing/overview' ),
                                            esc_html__( 'OpenAI API account', 'scwriter' )
                                        ),
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                    <input
                        type="password"
                        name="openai_api_key"
                        id="openai_api_key"
                        required
                        value=""
                        placeholder="<?php esc_attr_e('Enter your OpenAI API key', 'scwriter'); ?>"
                        class="scwriter-validate-it"
                    >
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-form-save">
                    <button type="submit" class="button button-primary scwriter-form-saver-scwriter_openai"><?php esc_html_e('Save OpenAI API Key', 'scwriter'); ?> <span class="scwriter-loading"></span></button>
                </div>

                <input type="hidden" name="action" value="scwriter_wizard_add_openai">
                <input type="hidden" name="<?php echo $nonce_name; ?>" value="<?php echo $nonce; ?>">

            </form>

        </div>

    <?php } ?>


    <div class="scwriter-wizard-step <?php echo $wizard_step == 'blog_topic' ? 'active' : ''; ?>">
        
        <form action="#" class="scwriter-form scwriter-form-wizard" data-step="blog_topic" method="POST">

            <div class="scwriter-presets-row">
                <div class="scwriter-presets-row-top">
                    <label for="blog_topic"><?php esc_html_e('Blog Topic', 'scwriter'); ?> <sup>*</sup></label>
                    <div class="scwriter-form-hint">
                        <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                        <div class="scwriter-form-hint-content">
                            <?php esc_html_e('Describe the main focus of your blog. What topics or themes will you cover?', 'scwriter'); ?>
                        </div>
                    </div>
                </div>
                <div class="scwriter-limit-words">
                    <textarea
                        rows="5"
                        name="blog_topic"
                        id="blog_topic"
                        class="scwriter-limit-words-input scwriter-validate-it"
                        data-limit="100"
                        required
                    ><?php echo get_bloginfo( 'description' ); ?></textarea>
                    <span class="scwriter-limit-words-count"></span>
                </div>
                <div class="scwriter-input-errors"></div>
            </div>

            <div class="scwriter-form-save">
                <button type="submit" class="button button-primary scwriter-form-saver-scwriter_blog_topic"><?php esc_html_e('Save Blog Topic', 'scwriter'); ?> <span class="scwriter-loading"></span></button>
            </div>

            <input type="hidden" name="action" value="scwriter_wizard_add_blog_topic">
            <input type="hidden" name="<?php echo $nonce_name; ?>" value="<?php echo $nonce; ?>">

        </form>

    </div>

<?php } ?>