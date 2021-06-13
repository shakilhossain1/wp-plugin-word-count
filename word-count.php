<?php

/**
 * Plugin Name: Word Count
 * Description: this is an awesome plugin
 * version: 1.0
 * Author: Shakil Hossain
 * Author URI: https://www.shakilhossain.com
 * Text Domain: wcp
 * Domain Path: /languages
 */

 if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

class WordCountAndTimePlugin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'adminPage']);
        add_action('admin_init', [$this, 'settings']);
        add_filter('the_content', [$this, 'ifWrap']);
        add_action('init', [$this, 'languages']);
    }

    public function ifWrap($content)
    {
        global $post;
        if (
            (is_single() && is_main_query() && $post->post_type == 'post')
            && (
                get_option('wcp_wordcount', 1)
                || get_option('wcp_charactercount', 1)
                || get_option('wcp_readtime', 1)
            )
        ) {
            return $this->createHtml($content);
        }

        return $content;
    }

    public function createHtml($content)
    {
        $html = "<h3>". esc_html(get_option('wcp_headline', 'Post Statistics')) ."</h3><p>";

        if (get_option('wcp_wordcount', 1) || get_option('wcp_readtime', 1)) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if (get_option('wcp_wordcount', 1)) {
            $html .= __("This post has", 'wcp') . " " . $wordCount . " " . __('words.', 'wcp') . "<br>";
        }

        if (get_option('wcp_charactercount', 1)) {
            $html .= __("This post has", 'wcp') . " " . strlen(strip_tags($content)) . " " . __('Characters.', 'wcp') . "<br>";

        }

        if (get_option('wcp_readtime', 1)) {
            $html .= __("This post will take about", 'wcp') . " " . round($wordCount / 225) . " " . esc_html__('minute(s) to read.', 'wcp') . "<br>";
        }

        $html .= '</p>';

        if ( get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }

        return $content . "<br>" .$html;
    }

    public function settings()
    {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        // Post Statistik display location
        add_settings_field('wcp_location', __('display Location', 'wcp'), [$this, 'locationHtml'], 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', ['sanitize_callback' => [$this, 'sanitizeLocation'], 'default' => '0']);

        // Headline Text
        add_settings_field('wcp_headline', 'Headline Text', [$this, 'headlineHtml'], 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics']);

        // Actual Word Count
        add_settings_field('wcp_wordcount', 'Word Count', [$this, 'checkboxHtml'], 'word-count-settings-page', 'wcp_first_section', ['name' => 'wcp_wordcount']);
        register_setting('wordcountplugin', 'wcp_wordcount', ['sanitize_callback' => 'sanitize_text_field', 'default' => '1']);

        // Chracter Count
        add_settings_field('wcp_charactercount', __('Character Count', 'wcp'), [$this, 'checkboxHtml'], 'word-count-settings-page', 'wcp_first_section', ['name' => 'wcp_charactercount']);
        register_setting('wordcountplugin', 'wcp_charactercount', ['sanitize_callback' => 'sanitize_text_field', 'default' => '1']);

        // Read time
        add_settings_field('wcp_readtime', 'Read Time', [$this, 'checkboxHtml'], 'word-count-settings-page', 'wcp_first_section', ['name' => 'wcp_readtime']);
        register_setting('wordcountplugin', 'wcp_readtime', ['sanitize_callback' => 'sanitize_text_field', 'default' => '1']);
    }

    public function adminPage()
    {
        add_options_page(
            __('Word Count Setting Page', 'wcp'),
            __('Word Count', 'wcp'),
            'manage_options',
            'word-count-settings-page',
            [$this, 'adminHtml']
        );
    }

    public function locationHtml()
    {
        ?>
            <select name="wcp_location">
                <option value="0" <?php selected(get_option('wcp_location'), '0'); ?>>Beginning of the post</option>
                <option value="1" <?php selected(get_option('wcp_location'), '1'); ?>>End of the post</option>
            </select>
        <?php
    }

    public function headlineHtml()
    {
        $default = esc_attr(get_option('wcp_headline'));
        echo "<input type='text' name='wcp_headline' value='{$default}'>";
    }

    public function checkboxHtml($args)
    {
        ?>
            <input type="checkbox" name="<?= $args['name']; ?>" value="1" <?php checked(get_option($args['name']), '1') ?>>
        <?php
    }

    public function sanitizeLocation($input)
    {
        if ( $input != '0' && $input != '1' ) {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either beginning or End');
            return get_option('wcp_location');
        }

        return $input;
    }

    public function adminHtml()
    {
        ?>
            <div class="wrap">
                <h2>Word Count Settings</h2>
                <form action="options.php" method="POST">
                    <?php
                    settings_fields('wordcountplugin');
                    do_settings_sections('word-count-settings-page');
                    submit_button();
                    ?>
                </form>
            </div>
        <?php
    }

    public function languages()
    {
        load_plugin_textdomain('wcp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();
