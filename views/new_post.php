<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$table_of_contents = array(
    esc_html__('In the article', 'scwriter'),
    esc_html__('Inside the article', 'scwriter'),
    esc_html__('Within the article', 'scwriter'),
    esc_html__('Throughout the article', 'scwriter'),
    esc_html__('In the manuscript', 'scwriter'),
    esc_html__('Inside the article', 'scwriter'),
    esc_html__('In this article', 'scwriter'),
    esc_html__('In the post', 'scwriter'),
    esc_html__('Inside the post', 'scwriter'),
    esc_html__('Within the post', 'scwriter'),
    esc_html__('Throughout the post', 'scwriter'),
    esc_html__('In the blog post', 'scwriter'),
    esc_html__('Inside the post', 'scwriter'),
    esc_html__('In this post', 'scwriter'),
    esc_html__('In the story', 'scwriter'),
    esc_html__('Inside the story', 'scwriter'),
    esc_html__('Within the story', 'scwriter'),
    esc_html__('Throughout the story', 'scwriter'),
    esc_html__('In the narrative', 'scwriter'),
    esc_html__('Inside the story', 'scwriter'),
    esc_html__('In this story', 'scwriter'),
);
$random_table_of_contents = $table_of_contents[array_rand($table_of_contents)];
$random_table_contents_css_class = sanitize_title($random_table_of_contents);
$default_values = array(
    'article_length' => '0',
    'article_length_custom' => '400',
    'enable_real_time_data' => '0',
    'enable_preview_outline' => '0',
    'secondary_keywords' => '',
    'enable_serp_analysis' => '0',
    'tone_of_voice' => esc_html('Conversational, Friendly, Knowledgeable and Clear', 'scwriter'),
    'point_of_view' => esc_html('First Person Singular (I, me, my, mine)', 'scwriter'),
    'language' => 'en',
    'country' => 'us',
    'enable_images' => '0',
    'images_source' => 'placeholders',
    'enable_external_links' => '0',
    'external_links_stop_words' => '',
    'enable_internal_links' => '0',
    'enable_introduction' => '1',
    'introduction_extra_prompt' => '',
    'enable_faq' => '',
    'enable_table_of_contents' => '1',
    'table_contents_css_class' => '',
    'table_contents_title' => '',
    'title_extra_prompt' => '',
    'body_extra_prompt' => '',
    'enable_conclusion' => '1',
    'conclusion_extra_prompt' => '',
    'enable_categories' => '0',
    'enable_categories_create_new' => '1',
    'enable_tags' => '0',
    'enable_dividers' => '0',
    'global_prompt' => '',
    'stop_words' => '',
    'enable_improve_readability' => '1',
    'title' => ''
);
?>
<svg class="scwriter-svg-icons">
    <symbol id="scwriter-svg-loading" viewBox="0 0 24 24">
        <line x1="12" y1="2" x2="12" y2="6"></line>
        <line x1="12" y1="18" x2="12" y2="22"></line>
        <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
        <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
        <line x1="2" y1="12" x2="6" y2="12"></line>
        <line x1="18" y1="12" x2="22" y2="12"></line>
        <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
        <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
    </symbol>
</svg>

<div class="scwriter-tabs-header">
    <div class="scwriter-tabs-header-title --single">
        <h1><?php esc_html_e('Create Post Using AI', 'scwriter'); ?></h1>
    </div>
</div>
<div class="scwriter-padding-wrapper">

    <div class="form-messages">
        <?php 
            if ( isset( $options['form_messages'] ) ) {
                if ( $options['form_messages']['error'] === true ) {
                    echo sprintf('<div class="notice notice-error"><p>%s</p></div>', wp_kses($options['form_messages']['error_message'], ['a' => array( 'href' => array(), 'target' => array() )]));
                } else {
                    echo sprintf('<div class="notice notice-success"><p>%s</p></div>', wp_kses($options['form_messages']['message'], ['a' => array( 'href' => array(), 'target' => array() )]));
                }
            }
        ?>
    </div>

    <script>
        let scwriter_presets = <?php echo json_encode($options['presets']); ?>;
        let scwriter_default_preset = '<?php echo $options['default_preset']; ?>';
        let scwriter_default_preset_data = <?php echo json_encode($default_values); ?>;
        let scwriter_table_of_contents = <?php echo json_encode($table_of_contents); ?>;
    </script>
    <div class="scwriter-popup-template scwriter-add-preset">
        <label><?php esc_html_e('Preset Name', 'scwriter'); ?></label>
        <div class="scwriter-popup-content-input">
            <input type="text" class="scwriter-new-preset-name">
            <button type="button" class="button-secondary scwriter-add-new-preset">
                <?php esc_html_e('Add New Preset', 'scwriter'); ?>
            </button>
        </div>
        <div class="scwriter-popup-content-input-errors">
            <p><?php esc_html_e('Input contains invalid characters. Please use letters and numbers only.', 'scwriter'); ?></p>
        </div>
    </div>
    <div class="scwriter-popup-template scwriter-edit-preset">
        <label><?php esc_html_e('Preset Name', 'scwriter'); ?></label>
        <div class="scwriter-popup-content-input">
            <input type="text" class="scwriter-current-preset-name">
            <button type="button" class="button-secondary scwriter-popup-edit-preset">
                <?php esc_html_e('Update Preset Name', 'scwriter'); ?>
            </button>
        </div>
        <div class="scwriter-popup-content-input-errors">
            <p><?php esc_html_e('Input contains invalid characters. Please use letters and numbers only.', 'scwriter'); ?></p>
        </div>
    </div>
    <div class="scwriter-popup">
        <div class="scwriter-popup-overlay"></div>
        <div class="scwriter-popup-content">
            <button type="button" class="scwriter-popup-close dashicons-no-alt dashicons"></button>
            <div class="scwriter-popup-content-inner">
            </div>
        </div>
    </div>

    <form action="<?php echo esc_url(admin_url( 'admin-post.php' )); ?>" class="scwriter-form" method="POST">
        <div class="scwriter-presets --cols">
            <div class="scwriter-presets-col">
                <div class="scwriter-presets-row --presets-dropdown">
                    <div class="scwriter-presets-row-top">
                        <label for="preset_id"><?php esc_html_e('Presets', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Saved templates let you save and easily load your settings for different articles with one click, and you can update them by clicking the save icon next to the template name.', 'scwriter'); ?>
                            </div>
                        </div>
                        <button type="button" class="scwriter-presets-create button-secondary" data-message="<?php echo esc_attr('A new preset will be created, and the form will be reset. Are you sure you want to continue?', 'scwriter'); ?>"><?php esc_html_e('Create Preset', 'scwriter'); ?></button>
                    </div>
                    <div class="scwriter-presets-row-dropdown">
                        <select name="preset_id" id="preset_id" class="scwriter-select2" required>
                        </select>
                        <input type="hidden" class="preset_name" id="preset_name" name="preset_name" value="">
                        <button type="button" class="scwriter-presets-btn scwriter-presets-btn-edit dashicons dashicons-edit-large" title="Edit">
                            <svg class="scwriter-svg-loading">
                                <use href="#scwriter-svg-loading"></use>
                            </svg>
                        </button>
                        <button type="button" class="scwriter-presets-btn scwriter-presets-btn-save" title="Save">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="scwriter-svg-icon"><path d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-242.7c0-17-6.7-33.3-18.7-45.3L352 50.7C340 38.7 323.7 32 306.7 32L64 32zm0 96c0-17.7 14.3-32 32-32l192 0c17.7 0 32 14.3 32 32l0 64c0 17.7-14.3 32-32 32L96 224c-17.7 0-32-14.3-32-32l0-64zM224 288a64 64 0 1 1 0 128 64 64 0 1 1 0-128z" fill="currentColor"/></svg>
                            <svg class="scwriter-svg-loading">
                                <use href="#scwriter-svg-loading"></use>
                            </svg>
                        </button>
                        <button type="button" class="scwriter-presets-btn scwriter-presets-btn-delete dashicons dashicons-trash" title="Delete" data-message="<?php echo esc_attr('Are you sure you want to delete this preset?', 'scwriter'); ?>">
                            <svg class="scwriter-svg-loading">
                                <use href="#scwriter-svg-loading"></use>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="primary_keyword"><?php esc_html_e('Primary Keyword', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('What is the main keyword or topic you want your article to focus on?', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="primary_keyword"
                            id="primary_keyword"
                            required
                            class="scwriter-limit-words-input scwriter-validate-it"
                            data-limit="20"
                            placeholder="<?php esc_attr_e('Favorite Superpower', 'scwriter') ?>"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                    <div class="scwriter-input-errors"></div>
                </div>
                
                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="title"><?php esc_html_e('Custom Title', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('If you have a specific headline, we’ll use it to create the article without generating an AI headline.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-limit-words">
                        <input
                            name="title"
                            id="title"
                            type="text"
                            class="scwriter-limit-words-input scwriter-validate-it"
                            data-limit="25"
                            placeholder=""
                        />
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="article_length"><?php esc_html_e('Article Length', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <strong><?php esc_html_e('Default:', 'scwriter'); ?></strong> <?php esc_html_e('The AI will determine the optimal length of the article by analyzing the top 10 competitors using the same primary keyword on Google, ensuring the article is comprehensive.', 'scwriter'); ?>
                                <br>
                                <br>
                                <strong><?php esc_html_e('Specific Article Size:', 'scwriter'); ?></strong> <?php esc_html_e('If you prefer a shorter or longer article, select one of the options below.', 'scwriter'); ?>
                                <br>
                                <br>
                                <strong><?php esc_html_e('Custom Number of Words:', 'scwriter'); ?></strong> <?php esc_html_e('You can also specify the exact number of words using the “Custom Number of Words” option.', 'scwriter'); ?>
                                <br>
                                <br>
                                <?php esc_html_e('The average length for each section is 200 words.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <select
                        name="article_length"
                        id="article_length"
                        required
                        class="scwriter-condition"
                        data-value="article_length_custom"
                        data-target="article_length_custom"
                        data-target-required="true"
                    >
                        <option value="0"><?php esc_attr_e('Default', 'scwriter'); ?></option>
                        <option value="article_length_custom"><?php esc_attr_e('Custom Number of Words', 'scwriter'); ?></option>
                        <option value="400,600"><?php esc_attr_e('Shorter: Press releases, Announcements (400-600 words)', 'scwriter'); ?></option>
                        <option value="600,1000"><?php esc_attr_e('Short: News, Demos (600-1000 words)', 'scwriter'); ?></option>
                        <option value="1000,1400"><?php esc_attr_e('Medium: B2B Cases, Info Blogs (1000-1400 words)', 'scwriter'); ?></option>
                        <option value="1600,2400"><?php esc_attr_e('Long: Guides, How-tos (1600-2400 words)', 'scwriter'); ?></option>
                        <option value="2400,2800"><?php esc_attr_e('Longer: Pillars (2400-2800 words)', 'scwriter'); ?></option>
                    </select>
                </div>
                
                <div class="scwriter-presets-row article_length_custom scwriter-condition-content">
                    <div class="scwriter-presets-row-top">
                        <label for="article_length_custom"><?php esc_html_e('Words Number', 'scwriter'); ?> <sup>*</sup></label>
                    </div>
                    <input
                        type="number"
                        name="article_length_custom"
                        id="article_length_custom"
                        min="400"
                        max="3000"
                        value="400"
                        class="scwriter-validate-it"
                    >
                    <div class="scwriter-input-errors"></div>
                </div>
                
                <div class="scwriter-presets-row scwriter-enable-outline-holder">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_preview_outline"><?php esc_html_e('Preview Outline', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('When enabled, an editable outline will be created before writing your article. You can reorder, delete, and add new sections to the outline before generating the article.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_preview_outline" id="enable_preview_outline" class="enable_preview_outline" value="1">
                        <span></span>
                    </label>
                </div>
                
                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_real_time_data"><?php esc_html_e('Use Real-Time Data', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('When enabled, we will collect data from search results for each section to aid in crafting the article.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_real_time_data" id="enable_real_time_data" value="1">
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row scwriter-outline-holder --hidden">
                    <div class="scwriter-presets-row-top">
                        <label><?php esc_html_e('Outline', 'scwriter'); ?></label>
                    </div>
                    <div id="scwriter-outline" class="scwriter-outline"></div>
                </div>

                <h2><?php esc_html_e('SEO', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                    <label for="secondary_keywords"><?php esc_html_e('Secondary Keywords', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Enter any keywords or entities you want covered in the article, separated by commas or new lines. We will automatically assign them to the most relevant sections and seamlessly incorporate them.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="secondary_keywords"
                            id="secondary_keywords"
                            class="scwriter-limit-words-input scwriter-validate-it"
                            data-limit="100"
                            placeholder="<?php esc_attr_e('e.g., Invisibility, Telekinesis, Super Strength, Time Manipulation', 'scwriter') ?>"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_serp_analysis"><?php esc_html_e('SERP Analysis', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('We will analyze the top 20 ranking pages and extract up to 75 topically relevant NLP entities to include in the article. This ensures a more comprehensive article with a better chance of ranking. Please note, this may slightly increase AI costs and the time required for article completion.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_serp_analysis" id="enable_serp_analysis" value="1">
                        <span></span>
                    </label>
                </div>


                <h2><?php esc_html_e('Style', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="tone_of_voice"><?php esc_html_e('Tone of Voice', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Select the tone of voice for your article by combining the most popular options:', 'scwriter'); ?>
                                <br>
                                <br>
                                <?php esc_html_e('Analytical, Authoritative, Casual, Clear and Concise, Conversational, Empathetic, Encouraging, Energetic, Excited, Formal, Friendly, Honest, Humorous, Informative, Inspirational, Knowledgeable, Motivational, Persuasive, Playful, Professional, Storytelling.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <textarea
                        name="tone_of_voice"
                        id="tone_of_voice"
                        rows="5"
                        required
                        placeholder="<?php esc_attr_e('Conversational, Friendly, Knowledgeable and Clear', 'scwriter') ?>"
                        value="<?php esc_attr_e('Conversational, Friendly, Knowledgeable and Clear', 'scwriter') ?>"
                        class="scwriter-validate-it"
                    ></textarea>
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="point_of_view"><?php esc_html_e('Point of View', 'scwriter'); ?> <sup>*</sup></label>
                    </div>
                    <select name="point_of_view" id="point_of_view" required>
                        <option value="<?php esc_attr_e('First Person Singular (I, me, my, mine)', 'scwriter'); ?>"><?php esc_attr_e('First Person Singular (I, me, my, mine)', 'scwriter'); ?></option>
                        <option value="<?php esc_attr_e('First Person Plural (we, us, our, ours)', 'scwriter'); ?>"><?php esc_attr_e('First Person Plural (we, us, our, ours)', 'scwriter'); ?></option>
                        <option value="<?php esc_attr_e('Second Person (you, your, yours)', 'scwriter'); ?>"><?php esc_attr_e('Second Person (you, your, yours)', 'scwriter'); ?></option>
                        <option value="<?php esc_attr_e('Third Person (he, she, it, they)', 'scwriter'); ?>"><?php esc_attr_e('Third Person (he, she, it, they)', 'scwriter'); ?></option>
                    </select>
                </div>
                

                <h2><?php esc_html_e('Location', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="language"><?php esc_html_e('Language', 'scwriter'); ?> <sup>*</sup></label>
                    </div>
                    <?php 
                        $values = array(
                            "af" => "Afrikaans",
                            "sq" => "Albanian",
                            "sm" => "Amharic",
                            "ar" => "Arabic",
                            "az" => "Azerbaijani",
                            "eu" => "Basque",
                            "be" => "Belarusian",
                            "bn" => "Bengali",
                            "bh" => "Bihari",
                            "bs" => "Bosnian",
                            "bg" => "Bulgarian",
                            "ca" => "Catalan",
                            "zh-CN" => "Chinese (Simplified)",
                            "zh-TW" => "Chinese (Traditional)",
                            "hr" => "Croatian",
                            "cs" => "Czech",
                            "da" => "Danish",
                            "nl" => "Dutch",
                            "en" => "English",
                            "eo" => "Esperanto",
                            "et" => "Estonian",
                            "fo" => "Faroese",
                            "fi" => "Finnish",
                            "fr" => "French",
                            "fy" => "Frisian",
                            "gl" => "Galician",
                            "ka" => "Georgian",
                            "de" => "German",
                            "el" => "Greek",
                            "gu" => "Gujarati",
                            "iw" => "Hebrew",
                            "hi" => "Hindi",
                            "hu" => "Hungarian",
                            "is" => "Icelandic",
                            "id" => "Indonesian",
                            "ia" => "Interlingua",
                            "ga" => "Irish",
                            "it" => "Italian",
                            "ja" => "Japanese",
                            "jw" => "Javanese",
                            "kn" => "Kannada",
                            "ko" => "Korean",
                            "la" => "Latin",
                            "lv" => "Latvian",
                            "lt" => "Lithuanian",
                            "mk" => "Macedonian",
                            "ms" => "Malay",
                            "ml" => "Malayam",
                            "mt" => "Maltese",
                            "mr" => "Marathi",
                            "ne" => "Nepali",
                            "no" => "Norwegian",
                            "nn" => "Norwegian (Nynorsk)",
                            "oc" => "Occitan",
                            "fa" => "Persian",
                            "pl" => "Polish",
                            "pt-BR" => "Portuguese (Brazil)",
                            "pt-PT" => "Portuguese (Portugal)",
                            "pa" => "Punjabi",
                            "ro" => "Romanian",
                            "ru" => "Russian",
                            "gd" => "Scots Gaelic",
                            "sr" => "Serbian",
                            "si" => "Sinhalese",
                            "sk" => "Slovak",
                            "sl" => "Slovenian",
                            "es" => "Spanish",
                            "su" => "Sudanese",
                            "sw" => "Swahili",
                            "sv" => "Swedish",
                            "tl" => "Tagalog",
                            "ta" => "Tamil",
                            "te" => "Telugu",
                            "th" => "Thai",
                            "ti" => "Tigrinya",
                            "tr" => "Turkish",
                            "uk" => "Ukrainian",
                            "ur" => "Urdu",
                            "uz" => "Uzbek",
                            "vi" => "Vietnamese",
                            "cy" => "Welsh",
                            "xh" => "Xhosa",
                            "zu" => "Zulu",
                        );
                        $default = 'en';
                        $selected = $default;
                    ?>
                    <select id="language" name="language" required>
                        <option value=""><?php esc_html_e('Select Language', 'scwriter'); ?></option>
                        <?php foreach ($values as $key => $value) { ?>
                            <option <?php echo $key == $selected? 'selected' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="country"><?php esc_html_e('Country', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Select the main country for your targeting. This choice will localize the results for Real-Time Data, SEO Optimization, and article sections. Ensure the Target Keyword is included in the relevant language.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <?php 
                        $values = array(
                            "af" => "Afghanistan",
                            "al" => "Albania",
                            "dz" => "Algeria",
                            "as" => "American Samoa",
                            "ad" => "Andorra",
                            "ao" => "Angola",
                            "ai" => "Anguilla",
                            "aq" => "Antarctica",
                            "ag" => "Antigua and Barbuda",
                            "ar" => "Argentina",
                            "am" => "Armenia",
                            "aw" => "Aruba",
                            "au" => "Australia",
                            "at" => "Austria",
                            "az" => "Azerbaijan",
                            "bs" => "Bahamas",
                            "bh" => "Bahrain",
                            "bd" => "Bangladesh",
                            "bb" => "Barbados",
                            "by" => "Belarus",
                            "be" => "Belgium",
                            "bz" => "Belize",
                            "bj" => "Benin",
                            "bm" => "Bermuda",
                            "bt" => "Bhutan",
                            "bo" => "Bolivia",
                            "ba" => "Bosnia and Herzegovina",
                            "bw" => "Botswana",
                            "bv" => "Bouvet Island",
                            "br" => "Brazil",
                            "io" => "British Indian Ocean Territory",
                            "bn" => "Brunei Darussalam",
                            "bg" => "Bulgaria",
                            "bf" => "Burkina Faso",
                            "bi" => "Burundi",
                            "kh" => "Cambodia",
                            "cm" => "Cameroon",
                            "ca" => "Canada",
                            "cv" => "Cape Verde",
                            "ky" => "Cayman Islands",
                            "cf" => "Central African Republic",
                            "td" => "Chad",
                            "cl" => "Chile",
                            "cn" => "China",
                            "cx" => "Christmas Island",
                            "cc" => "Cocos (Keeling) Islands",
                            "co" => "Colombia",
                            "km" => "Comoros",
                            "cg" => "Congo",
                            "cd" => "Congo, the Democratic Republic of the",
                            "ck" => "Cook Islands",
                            "cr" => "Costa Rica",
                            "ci" => "Cote D'ivoire",
                            "hr" => "Croatia",
                            "cu" => "Cuba",
                            "cy" => "Cyprus",
                            "cz" => "Czech Republic",
                            "dk" => "Denmark",
                            "dj" => "Djibouti",
                            "dm" => "Dominica",
                            "do" => "Dominican Republic",
                            "ec" => "Ecuador",
                            "eg" => "Egypt",
                            "sv" => "El Salvador",
                            "gq" => "Equatorial Guinea",
                            "er" => "Eritrea",
                            "ee" => "Estonia",
                            "et" => "Ethiopia",
                            "fk" => "Falkland Islands (Malvinas)",
                            "fo" => "Faroe Islands",
                            "fj" => "Fiji",
                            "fi" => "Finland",
                            "fr" => "France",
                            "gf" => "French Guiana",
                            "pf" => "French Polynesia",
                            "tf" => "French Southern Territories",
                            "ga" => "Gabon",
                            "gm" => "Gambia",
                            "ge" => "Georgia",
                            "de" => "Germany",
                            "gh" => "Ghana",
                            "gi" => "Gibraltar",
                            "gr" => "Greece",
                            "gl" => "Greenland",
                            "gd" => "Grenada",
                            "gp" => "Guadeloupe",
                            "gu" => "Guam",
                            "gt" => "Guatemala",
                            "gn" => "Guinea",
                            "gw" => "Guinea-Bissau",
                            "gy" => "Guyana",
                            "ht" => "Haiti",
                            "hm" => "Heard Island and Mcdonald Islands",
                            "va" => "Holy See (Vatican City State)",
                            "hn" => "Honduras",
                            "hk" => "Hong Kong",
                            "hu" => "Hungary",
                            "is" => "Iceland",
                            "in" => "India",
                            "id" => "Indonesia",
                            "ir" => "Iran, Islamic Republic of",
                            "iq" => "Iraq",
                            "ie" => "Ireland",
                            "il" => "Israel",
                            "it" => "Italy",
                            "jm" => "Jamaica",
                            "jp" => "Japan",
                            "jo" => "Jordan",
                            "kz" => "Kazakhstan",
                            "ke" => "Kenya",
                            "ki" => "Kiribati",
                            "kp" => "Korea, Democratic People's Republic of",
                            "kr" => "Korea, Republic of",
                            "kw" => "Kuwait",
                            "kg" => "Kyrgyzstan",
                            "la" => "Lao People's Democratic Republic",
                            "lv" => "Latvia",
                            "lb" => "Lebanon",
                            "ls" => "Lesotho",
                            "lr" => "Liberia",
                            "ly" => "Libyan Arab Jamahiriya",
                            "li" => "Liechtenstein",
                            "lt" => "Lithuania",
                            "lu" => "Luxembourg",
                            "mo" => "Macao",
                            "mk" => "Macedonia, the Former Yugosalv Republic of",
                            "mg" => "Madagascar",
                            "mw" => "Malawi",
                            "my" => "Malaysia",
                            "mv" => "Maldives",
                            "ml" => "Mali",
                            "mt" => "Malta",
                            "mh" => "Marshall Islands",
                            "mq" => "Martinique",
                            "mr" => "Mauritania",
                            "mu" => "Mauritius",
                            "yt" => "Mayotte",
                            "mx" => "Mexico",
                            "fm" => "Micronesia, Federated States of",
                            "md" => "Moldova, Republic of",
                            "mc" => "Monaco",
                            "mn" => "Mongolia",
                            "ms" => "Montserrat",
                            "ma" => "Morocco",
                            "mz" => "Mozambique",
                            "mm" => "Myanmar",
                            "na" => "Namibia",
                            "nr" => "Nauru",
                            "np" => "Nepal",
                            "nl" => "Netherlands",
                            "an" => "Netherlands Antilles",
                            "nc" => "New Caledonia",
                            "nz" => "New Zealand",
                            "ni" => "Nicaragua",
                            "ne" => "Niger",
                            "ng" => "Nigeria",
                            "nu" => "Niue",
                            "nf" => "Norfolk Island",
                            "mp" => "Northern Mariana Islands",
                            "no" => "Norway",
                            "om" => "Oman",
                            "pk" => "Pakistan",
                            "pw" => "Palau",
                            "ps" => "Palestinian Territory, Occupied",
                            "pa" => "Panama",
                            "pg" => "Papua New Guinea",
                            "py" => "Paraguay",
                            "pe" => "Peru",
                            "ph" => "Philippines",
                            "pn" => "Pitcairn",
                            "pl" => "Poland",
                            "pt" => "Portugal",
                            "pr" => "Puerto Rico",
                            "qa" => "Qatar",
                            "re" => "Reunion",
                            "ro" => "Romania",
                            "ru" => "Russian Federation",
                            "rw" => "Rwanda",
                            "sh" => "Saint Helena",
                            "kn" => "Saint Kitts and Nevis",
                            "lc" => "Saint Lucia",
                            "pm" => "Saint Pierre and Miquelon",
                            "vc" => "Saint Vincent and the Grenadines",
                            "ws" => "Samoa",
                            "sm" => "San Marino",
                            "st" => "Sao Tome and Principe",
                            "sa" => "Saudi Arabia",
                            "sn" => "Senegal",
                            "cs" => "Serbia and Montenegro",
                            "sc" => "Seychelles",
                            "sl" => "Sierra Leone",
                            "sg" => "Singapore",
                            "sk" => "Slovakia",
                            "si" => "Slovenia",
                            "sb" => "Solomon Islands",
                            "so" => "Somalia",
                            "za" => "South Africa",
                            "gs" => "South Georgia and the South Sandwich Islands",
                            "es" => "Spain",
                            "lk" => "Sri Lanka",
                            "sd" => "Sudan",
                            "sr" => "Suriname",
                            "sj" => "Svalbard and Jan Mayen",
                            "sz" => "Swaziland",
                            "se" => "Sweden",
                            "ch" => "Switzerland",
                            "sy" => "Syrian Arab Republic",
                            "tw" => "Taiwan, Province of China",
                            "tj" => "Tajikistan",
                            "tz" => "Tanzania, United Republic of",
                            "th" => "Thailand",
                            "tl" => "Timor-Leste",
                            "tg" => "Togo",
                            "tk" => "Tokelau",
                            "to" => "Tonga",
                            "tt" => "Trinidad and Tobago",
                            "tn" => "Tunisia",
                            "tr" => "Turkey",
                            "tm" => "Turkmenistan",
                            "tc" => "Turks and Caicos Islands",
                            "tv" => "Tuvalu",
                            "ug" => "Uganda",
                            "ua" => "Ukraine",
                            "ae" => "United Arab Emirates",
                            "uk" => "United Kingdom",
                            "us" => "United States",
                            "um" => "United States Minor Outlying Islands",
                            "uy" => "Uruguay",
                            "uz" => "Uzbekistan",
                            "vu" => "Vanuatu",
                            "ve" => "Venezuela",
                            "vn" => "Viet Nam",
                            "vg" => "Virgin Islands, British",
                            "vi" => "Virgin Islands, U.S.",
                            "wf" => "Wallis and Futuna",
                            "eh" => "Western Sahara",
                            "ye" => "Yemen",
                            "zm" => "Zambia",
                            "zw" => "Zimbabwe",
                        );
                        $default = 'us';
                        $selected = $default;
                    ?>
                    <select id="country" name="country" required>
                        <option value=""><?php esc_html_e('Select Country', 'scwriter'); ?></option>
                        <?php foreach ($values as $key => $value) { ?>
                            <option <?php echo $key == $selected? 'selected' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </div>


                <h2><?php esc_html_e('Media', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_images"><?php esc_html_e('Add Images', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('We will insert image after each H2 section and include SEO-optimized alt keywords.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input
                            type="checkbox"
                            name="enable_images"
                            id="enable_images"
                            value="1"
                            class="scwriter-condition"
                            data-value="checked"
                            data-target="images_source"
                            data-target-required="true"
                        >
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row images_source scwriter-condition-content">
                    <div class="scwriter-presets-row-top">
                        <label for="images_source"><?php esc_html_e('Image Source', 'scwriter'); ?> <sup>*</sup></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Choose the image source for your content. Placeholders will insert gray background images, while Stock Photos: Pexels automatically adds free images from pexels.com. We’ll ensure relevant images are found and included to fit your content.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <select id="images_source" name="images_source">
                        <option value="placeholders"><?php esc_html_e('Placeholders', 'scwriter'); ?></option>
                        <option value="stock_pexels"><?php esc_html_e('Stock Photos: Pexels', 'scwriter'); ?></option>
                    </select>
                </div>

                <h2><?php esc_html_e('Links', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_external_links"><?php esc_html_e('External Links', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('We will automatically add relevant external links to websites that are non-competitive with your primary keyword.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_external_links" id="enable_external_links" value="1">
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="external_links_stop_words"><?php esc_html_e('Stop Words for External Links', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Enter words to block certain external links. We won’t add links containing these words. Use commas or new lines to separate each word.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <textarea
                        rows="5"
                        name="external_links_stop_words"
                        id="external_links_stop_words"
                        class="scwriter-validate-it"
                    ></textarea>
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_internal_links"><?php esc_html_e('Internal Links', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('We will automatically add internal links from already published posts or pages.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_internal_links" id="enable_internal_links" value="1">
                        <span></span>
                    </label>
                </div>


                <h2><?php esc_html_e('Sections', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_introduction"><?php esc_html_e('Include Introduction', 'scwriter'); ?></label>
                    </div>
                    <label class="scwriter-toggle">
                        <input
                            type="checkbox"
                            name="enable_introduction"
                            id="enable_introduction"
                            value="1"
                            class="scwriter-condition"
                            checked
                            data-value="checked"
                            data-target="introduction_extra_prompt"
                            data-target-required="false"
                        >
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row introduction_extra_prompt scwriter-condition-content --show">
                    <div class="scwriter-presets-row-top">
                        <label for="introduction_extra_prompt"><?php esc_html_e('Extra Introduction Prompt', 'scwriter'); ?></label>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="introduction_extra_prompt"
                            id="introduction_extra_prompt"
                            class="scwriter-limit-words-input"
                            data-limit="100"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                </div>

                <div class="scwriter-presets-row --hidden">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_faq"><?php esc_html_e('Include FAQ Section', 'scwriter'); ?></label>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_faq" id="enable_faq" value="1">
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_table_of_contents"><?php esc_html_e('Include Table of Contents', 'scwriter'); ?></label>
                    </div>
                    <label class="scwriter-toggle">
                        <input
                            type="checkbox"
                            name="enable_table_of_contents"
                            id="enable_table_of_contents"
                            value="1"
                            class="scwriter-condition"
                            data-value="checked"
                            data-target="enable_table_of_contents"
                            data-target-required="true"
                        >
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row enable_table_of_contents scwriter-condition-content">
                    <div class="scwriter-presets-row-top">
                        <label for="table_contents_css_class"><?php esc_html_e('Table of Contents CSS Class Name', 'scwriter'); ?> <sup>*</sup></label>
                    </div>
                    <input
                        type="text"
                        name="table_contents_css_class"
                        id="table_contents_css_class"
                        class="scwriter-validate-it"
                        value="<?php echo $random_table_contents_css_class; ?>"
                    >
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row enable_table_of_contents scwriter-condition-content">
                    <div class="scwriter-presets-row-top">
                        <label for="table_contents_title"><?php esc_html_e('Table of Contents Title', 'scwriter'); ?> <sup>*</sup></label>
                    </div>
                    <input
                        type="text"
                        name="table_contents_title"
                        id="table_contents_title"
                        class="scwriter-validate-it"
                        value="<?php echo $random_table_of_contents; ?>"
                    >
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="title_extra_prompt"><?php esc_html_e('Extra Title Prompt', 'scwriter'); ?></label>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="title_extra_prompt"
                            id="title_extra_prompt"
                            class="scwriter-limit-words-input"
                            data-limit="100"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="body_extra_prompt"><?php esc_html_e('Extra Body Prompt', 'scwriter'); ?></label>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="body_extra_prompt"
                            id="body_extra_prompt"
                            class="scwriter-limit-words-input"
                            data-limit="100"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_conclusion"><?php esc_html_e('Include Conclusion', 'scwriter'); ?></label>
                    </div>
                    <label class="scwriter-toggle">
                        <input
                            type="checkbox"
                            name="enable_conclusion"
                            id="enable_conclusion"
                            value="1"
                            checked
                            class="scwriter-condition"
                            data-value="checked"
                            data-target="conclusion_extra_prompt"
                            data-target-required="false"
                        >
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row scwriter-condition-content conclusion_extra_prompt --show">
                    <div class="scwriter-presets-row-top">
                        <label for="conclusion_extra_prompt"><?php esc_html_e('Extra Conclusion Prompt', 'scwriter'); ?></label>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="conclusion_extra_prompt"
                            id="conclusion_extra_prompt"
                            class="scwriter-limit-words-input"
                            data-limit="100"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                </div>


                <h2><?php esc_html_e('Organizers', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_categories"><?php esc_html_e('Include Categories', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('When enabled, we will create new categories or reuse existing ones for the article.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input
                            type="checkbox"
                            name="enable_categories"
                            id="enable_categories"
                            value="1"
                            class="scwriter-condition"
                            data-value="checked"
                            data-target="enable_categories_create_new"
                            data-target-required="false"
                        >
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row scwriter-condition-content enable_categories_create_new">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_categories_create_new"><?php esc_html_e('Create New Categories', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('When checked, we will create new categories if relevant ones are not found in the list. If unchecked, we will use only existing categories.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_categories_create_new" id="enable_categories_create_new" value="1" checked>
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_tags"><?php esc_html_e('Include Tags', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('When enabled, we will create new tags for the article.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_tags" id="enable_tags" value="1">
                        <span></span>
                    </label>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_dividers"><?php esc_html_e('Section Dividers', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('When enabled, we will insert a horizontal line after each H2 section for clearer separation. This applies when Image Placeholders are deactivated.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        $default = '';
                        $checked = $default;
                    ?>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_dividers" id="enable_dividers" value="1" <?php echo $checked; ?> >
                        <span></span>
                    </label>
                </div>


                <h2><?php esc_html_e('Enhancements', 'scwriter'); ?></h2>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="global_prompt"><?php esc_html_e('Global Prompt', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Enter any high-level specific instructions you want applied to all parts of the article. This helps the AI follow a consistent direction throughout.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="global_prompt"
                            id="global_prompt"
                            class="scwriter-limit-words-input"
                            data-limit="500"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="stop_words"><?php esc_html_e('Stop Words', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('Enter words to be ignored during processing, separated by commas or new lines. This is useful, for example, when you know which words the AI often uses and you want to exclude them. Use commas or new lines to separate each word.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="scwriter-limit-words">
                        <textarea
                            rows="5"
                            name="stop_words"
                            id="stop_words"
                            class="scwriter-limit-words-input scwriter-validate-it"
                            data-limit="1000"
                        ></textarea>
                        <span class="scwriter-limit-words-count"></span>
                    </div>
                    <div class="scwriter-input-errors"></div>
                </div>

                <div class="scwriter-presets-row">
                    <div class="scwriter-presets-row-top">
                        <label for="enable_improve_readability"><?php esc_html_e('Improve Readability', 'scwriter'); ?></label>
                        <div class="scwriter-form-hint">
                            <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                            <div class="scwriter-form-hint-content">
                                <?php esc_html_e('When enabled, we will use simpler words, phrases, and shorter sentences to target an 8th-grade reading level.', 'scwriter'); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        $default = 'checked';
                        $checked = $default;
                    ?>
                    <label class="scwriter-toggle">
                        <input type="checkbox" name="enable_improve_readability" id="enable_improve_readability" value="1" <?php echo $checked; ?> >
                        <span></span>
                    </label>
                </div>
            
            </div>
            <div class="scwriter-presets-col">
                <div class="scwriter-post-create-wrap">
                    <button type="submit" class="button button-primary scwriter-post-create" data-type="article">
                        <span class="scwriter-post-article"><?php esc_html_e('Create New Article', 'scwriter'); ?></span>
                        <span class="scwriter-post-draft"><?php esc_html_e('Create Draft', 'scwriter'); ?></span>
                        <span class="scwriter-loading"></span>
                    </button>
                    <?php if ( isset($options['usage']['article_limit']) && $options['usage']['article_limit'] != 0 ) { ?>
                        <div class="scwriter-post-create-usage">
                            <div class="scwriter-post-create-usage-stat">
                                <?php
                                    /* translators: %1$d is current usage, %2$d is usage limit */
                                    echo sprintf(
                                        esc_html__('%1$d/%2$d articles', 'scwriter'),
                                        $options['usage']['article_count'],
                                        $options['usage']['article_limit']
                                    );
                                ?>
                            </div>
                            <div class="scwriter-form-hint">
                                <button type="button" class="scwriter-form-hint-opener"><span class="dashicons dashicons-info-outline"></span></button>
                                <div class="scwriter-form-hint-content">
                                    <?php 
                                        /* translators: %s is reset date */
                                        echo sprintf(
                                            __('Your article limit will reset on %s.', 'your-text-domain'),
                                            $options['usage']['reset_date']
                                        );
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <input type="hidden" name="action" value="scwriter_create_scwriter_post">
            <?php wp_nonce_field(SCWRITER_PREFIX.'_nonce_field_value', SCWRITER_PREFIX.'_nonce_field'); ?>
        </div>
    </form>
</div>