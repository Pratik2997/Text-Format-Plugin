<?php

/*
  Plugin Name: Text Modification Plugin
  Description: A truly amazing plugin.
  Version: 1.0
  Author: Pratik
  Author URI: http://pratikpaudel458.com.np
*/
add_action('admin_enqueue_scripts', 'loadJs');
function loadJs()
{
    wp_enqueue_script('theJs', plugin_dir_url(__FILE__) . 'logic.js');
    wp_enqueue_style('theCss', plugin_dir_url(__FILE__) . 'style.css');
}

class TextModifyPlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'addOption'));
        add_action('admin_init', array($this, 'settingsLogic'));

        add_filter('the_content', array($this, 'actualLogic'));
    }

    function settingsLogic()
    {

        add_settings_section('wmp_the_section', null, null, 'convert-settings-page');

        add_settings_field('wmp_textformat', 'Text Format', array($this, 'radioHTML'), 'convert-settings-page', 'wmp_the_section', array('theName' => 'wmp_textformat'));
        register_setting('wordmodifyplugin', 'wmp_textformat', array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));

        register_setting('wordmodifyplugin', 'wmp_reset', array('sanitize_callback' => array($this, 'resetLogic'), 'default' => '0'));

        add_settings_field('wmp_color', 'Background Color', array($this, 'backgroundColorHTML'), 'convert-settings-page', 'wmp_the_section');
        register_setting('wordmodifyplugin', 'wmp_color', array('sanitize_callback' => array($this, 'sanitize_location'), 'default' => '0'));

        add_settings_field('wmp_query', 'Page-Slug', array($this, 'queryHTML'), 'convert-settings-page', 'wmp_the_section');
        register_setting('wordmodifyplugin', 'wmp_query', array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));
    }

    function queryHTML(){?>
    <div class="queryForSpecificPage">
        <input type="text" name="wmp_query">
        <small>Not functional yet. I'm currently working on it.</small>
    </div>
    <?php 
    
}
    
    function resetLogic($input)
    {
        if (isset($_POST['reset'])) {
            add_settings_error('wmp_reset', 'resetbutton', __('Your settings has been changed defualt setting.', 'text-domain'), 'updated');
            update_option('wmp_textformat', '0');
            update_option('wmp_color', '0');
        }

        return $input;
    }


    function radioHTML($param)
    {
?>
        <div class="theThreeRadios">
            <input type="radio" class="theThreeRadios" name="<?php echo $param['theName']; ?>" value="1" <?php checked(get_option($param['theName']), '1'); ?>>
            <span class="breakToNewLine">To Upper Case</span>
        </div>
        <div class="theThreeRadios">
            <input type="radio" class="theThreeRadios" name="<?php echo $param['theName']; ?>" value="2" <?php checked(get_option($param['theName']), '2'); ?>>
            <span class="breakToNewLine">To Lower Case</span>
        </div>
        <div class="theThreeRadios">
            <input type="radio" class="theThreeRadios" name="<?php echo $param['theName']; ?>" value="3" <?php checked(get_option($param['theName']), '3'); ?>>
            <span class="breakToNewLine">Capitalize</span>
        </div>

    <?php
        submit_button(__('Reset'), 'secondary', 'reset', false);
    }
    function sanitize_location($args)
    {
        if ($args != '0' and $args != '1' and $args != '2' and $args != '3') {
            add_settings_error('wmp_color', 'wmp_color_error', 'Value of the color must be defined');
            return get_option('wmp_color');
        }
        return $args;
    }

    function backgroundColorHTML()
    { ?>

        <select name="wmp_color">
            <option value="0" <?php selected(get_option("wmp_color"), '0') ?>>None</option>
            <option value="1" <?php selected(get_option("wmp_color"), '1') ?>>Red</option>
            <option value="2" <?php selected(get_option("wmp_color"), '2') ?>>Blue</option>
            <option value="3" <?php selected(get_option("wmp_color"), '3') ?>>Green</option>
        </select>

    <?php }



    function addOption()
    {
        add_options_page('Convert Content', esc_html('Convert Content'), 'manage_options', 'convert-settings-page', array($this, 'convHTML'));
    }

    function convHTML()
    { ?>
        <div class="wrap">
            <h1>Modify the Content</h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('wordmodifyplugin');
                do_settings_sections('convert-settings-page');

                submit_button();
                ?>
            </form>
        </div>

<?php }
    function actualLogic($content)
    {
        if (is_single() AND is_main_query() AND get_option('wmp_textformat', '1') or get_option('wmp_textformat', '2') or get_option('wmp_textformat', '3') OR get_option('wmp_color', '1') or get_option('wmp_color', '2') or get_option('wmp_color', '3') or get_option('wmp_color', '4')) {

            return $this->textModify($content);
        }

        return $content;
    }

    function textModify($content)
    {

        switch (get_option('wmp_textformat')) {
            case '1':
                $content = strtoupper($content);
                break;

            case '2':
                $content = strtolower($content);
                break;

            case '3':
                if (var_export($content) == "string") {

                    $whole = explode(".", $content);

                    foreach ($whole as $sentences) {
                        $trimmedSentences = trim($sentences);
                        $converted = ucfirst($trimmedSentences);
                        $content = $converted . "." . " ";
                        echo $content;
                    }
                }
                break;

            default:

                break;
        }
        switch (get_option('wmp_color')) {
            case '1':
                $content = '<div style = "background-color: red;">' . $content . '</div>';
                break;
            case '2':
                $content =  '<div style = "background-color: blue;">' . $content . '</div>';
                break;
            case '3':
                $content =  '<div style = "background-color: green;">' . $content . '</div>';
                break;

            default:
                echo $content;
                break;
        }

        return $content;
    }
}
$modifyTextCustom = new TextModifyPlugin();

?>