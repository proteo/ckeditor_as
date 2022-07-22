<?php

namespace Drupal\ckeditor_as\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Advanced settings" CKEditor plugin.
 *
 * @CKEditorPlugin(
 *   id = "ckeditor_advanced_settings",
 *   label = @Translation("Advanced settings")
 * )
 */
class CkeditorAdvancedSettings extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface, CKEditorPluginContextualInterface {

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(Editor $editor) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
  }

  /**
   * Build the config array with default values.
   *
   * @param \Drupal\editor\Entity\Editor $editor
   *   The current text editor object.
   *
   * @return array
   *   Settings used by this plugin with current values, or default ones if no
   *   corresponding values are set yet.
   *
   * @see ckeditor_as_editor_js_settings_alter()
   *
   * @link https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_config.html
   */
  private function getDefaultConfig(Editor $editor) {
    // These values match default values set in CKEDITOR.config.
    $config = [
      'acf_mode' => 'disable',
      'acf_rules' => '',
      'startupOutlineBlocks' => FALSE,
      'ignoreEmptyParagraph' => TRUE,
      'fillEmptyBlocks' => TRUE,
      'paste_word_prompt' => TRUE,
    ];

    $settings = $editor->getSettings();
    $default = $settings['plugins']['ckeditor_advanced_settings'] ?? [];

    if (isset($default['acf_mode'])) {
      $config['acf_mode'] = trim($default['acf_mode']);
    }
    if (isset($default['acf_rules'])) {
      $config['acf_rules'] = trim($default['acf_rules']);
    }
    if (isset($default['startupOutlineBlocks'])) {
      $config['startupOutlineBlocks'] = (bool) $default['startupOutlineBlocks'];
    }
    if (isset($default['ignoreEmptyParagraph'])) {
      $config['ignoreEmptyParagraph'] = (bool) $default['ignoreEmptyParagraph'];
    }
    if (isset($default['fillEmptyBlocks'])) {
      $config['fillEmptyBlocks'] = (bool) $default['fillEmptyBlocks'];
    }
    if (isset($default['paste_word_prompt'])) {
      $config['paste_word_prompt'] = (bool) $default['paste_word_prompt'];
    }

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return $this->getDefaultConfig($editor);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $config = $this->getDefaultConfig($editor);

    // ACF mode.
    $url = Url::fromUri('https://docs.ckeditor.com/#!/guide/dev_advanced_content_filter');
    $link = Link::fromTextAndUrl('https://docs.ckeditor.com/#!/guide/dev_advanced_content_filter', $url)->toString();
    $form['acf_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#description' => $this->t('ACF limits and adapts input data (HTML code added in source mode or by the editor.setData method, pasted HTML code, etc.) so it matches the editor configuration in the best possible way. It may also deactivate features which generate HTML code that is not allowed by the configuration. See @link for details.', ['@link' => $link]),
      '#options' => [
        'automatic' => $this->t('Automatic'),
        'custom' => $this->t('Custom'),
        'disable' => $this->t('Disabled'),
      ],
      '#default_value' => $config['acf_mode'],
    ];
    // Allowed content rules.
    $url_allowed = Url::fromUri('https://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-allowedContent');
    $link_allowed = Link::fromTextAndUrl('allowedContent', $url_allowed)->toString();
    $url_extra_allowed = Url::fromUri('https://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-extraAllowedContent');
    $link_extra_allowed = Link::fromTextAndUrl('extraAllowedContent', $url_extra_allowed)->toString();
    $url_content_rules = Url::fromUri('https://docs.ckeditor.com/#!/guide/dev_allowed_content_rules');
    $link_content_rules = Link::fromTextAndUrl('https://docs.ckeditor.com/#!/guide/dev_allowed_content_rules', $url_content_rules)->toString();
    $form['acf_rules'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Content Rules'),
      '#description' => $this->t('Rules for whitelisting content for the advanced content filter. Uses the @link_allowed setting in Custom mode or the @link_extra_allowed settings in Automatic mode internally. See @link_content_rules for details.', [
        '@link_allowed' => $link_allowed,
        '@link_extra_allowed' => $link_extra_allowed,
        '@link_content_rules' => $link_content_rules,
      ]),
      '#default_value' => $config['acf_rules'],
      '#states' => [
        'visible' => [
          ':input[name="editor[settings][plugins][ckeditor_advanced_settings][acf_mode]"]' => ['!value' => 'disable'],
        ],
      ],
    ];
    // Show blocks.
    $form['startupOutlineBlocks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show blocks'),
      '#description' => $this->t('Whether to automaticaly enable the "Show blocks" command when the editor loads.'),
      '#default_value' => $config['startupOutlineBlocks'],
    ];
    // Ignore empty paragraphs.
    $form['ignoreEmptyParagraph'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ignore empty paragraphs'),
      '#description' => $this->t('Whether the editor must output an empty value if its content only consists of an empty paragraph.'),
      '#default_value' => $config['ignoreEmptyParagraph'],
    ];
    // Fill empty blocks.
    $form['fillEmptyBlocks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Fill empty blocks'),
      '#description' => $this->t('Whether a filler text (non-breaking space entity — &amp;nbsp;) will be inserted into empty block elements in HTML output.'),
      '#default_value' => $config['fillEmptyBlocks'],
    ];
    // Paste from Word cleanup prompt.
    $form['paste_word_prompt'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Paste from Word cleanup prompt'),
      '#description' => $this->t('Whether to prompt the user about the clean up of content being pasted from MS Word.'),
      '#default_value' => $config['paste_word_prompt'],
    ];

    return $form;
  }

}
