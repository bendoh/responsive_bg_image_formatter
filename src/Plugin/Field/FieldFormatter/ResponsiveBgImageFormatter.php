<?php

namespace Drupal\responsive_bg_image_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;

/**
 * @FieldFormatter(
 *  id = "responsive_bg_image_formatter",
 *  label = @Translation("Responsive Background Image"),
 *  field_types = {"image"}
 * )
 */
class ResponsiveBgImageFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'image_style' => '',
      'css_settings' => array(
        'bg_image_selector' => 'body',
        'bg_image_color' => '#FFFFFF',
        'bg_image_x' => '50%',
        'bg_image_y' => '50%',
        'bg_image_attachment' => 'scroll',
        'bg_image_background_size' => '',
        'bg_image_background_size_ie8' => 0,
        'bg_image_important' => 1,
      ),
    );
  }

	/**
	 * Get the possible responsive image styles
	 */
	protected function getResponsiveImageStyles($withNone = FALSE) {
		$styles = ResponsiveImageStyle::loadMultiple();
		$options = array();

		if ($withNone && empty($styles)) {
			$options[''] = t('- Defined None -');
		}

		foreach ($styles as $name => $style) {
			$options[$name] = $style->label();
		}

		return $options;
	}

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = array();
    $settings = $this->getSettings();
		$options = $this->getResponsiveImageStyles(TRUE);

    $element['image_style'] = array(
      '#title' => $this->t('Responsive Image style'),
      '#type' => 'select',
      '#default_value' => $settings['image_style'],
      '#empty_option' => $this->t('None (original image)'),
      '#options' => $options,
      '#description' => $this->t(
        'Select <a href="@href_image_style">the responsive image style</a> to use.',
        array(
          '@href_image_style' => Url::fromRoute('entity.responsive_image_style.collection')->toString(),
        )
      ),
    );

    // Fieldset for css settings.
    $element['css_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Default CSS Settings'),
      '#description' => $this->t('Default CSS settings for outputting the background property. These settings will be concatenated to form a complete css statement that uses the "background" property. For more information on the css background property see http://www.w3schools.com/css/css_background.asp"'),
    );
    // The selector for the background property.
    $element['css_settings']['bg_image_selector'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Selector(s)'),
      '#description' => $this->t('A valid CSS selector that will be used to apply the background image. One per line. If the field is a multivalue field, the first line will be applied to the first value, the second to the second value... and so on.'),
      '#default_value' => $settings['css_settings']['bg_image_selector'],
    );
    // The selector for the background property.
    $element['css_settings']['bg_image_color'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Color'),
      '#description' => $this->t('The fallback color to use as the background, formatted as any valid css color format (e.g. hex, rgb, text, hsl) [<a href="http://www.w3schools.com/css/pr_background-color.asp">css property: background-color</a>]'),
      '#default_value' => $settings['css_settings']['bg_image_color'],
    );

		// The selector for the background property.
    $element['css_settings']['bg_image_x'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Horizontal Alignment'),
      '#description' => $this->t('The horizontal alignment of the background image formatted as any valid css alignment. [<a href="http://www.w3schools.com/css/pr_background-position.asp">css property: background-position</a>]'),
      '#default_value' => $settings['css_settings']['bg_image_x'],
    );
    // The selector for the background property.
    $element['css_settings']['bg_image_y'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Vertical Alignment'),
      '#description' => $this->t('The vertical alignment of the background image formatted as any valid css alignment. [<a href="http://www.w3schools.com/css/pr_background-position.asp">css property: background-position</a>]'),
      '#default_value' => $settings['css_settings']['bg_image_y'],
		);

		// The selector for the background property.
    $element['css_settings']['bg_image_attachment'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Background Attachment'),
      '#description' => $this->t('The attachment setting for the background image. [<a href="http://www.w3schools.com/css/pr_background-attachment.asp">css property: background-attachment</a>]'),
      '#options' => array('scroll' => 'Scroll', 'fixed' => 'Fixed'),
      '#default_value' => $settings['css_settings']['bg_image_attachment'],
    );

    // The background-repeat property.
    $element['css_settings']['bg_image_repeat'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Background Repeat'),
      '#description' => $this->t('Define the repeat settings for the background image. [<a href="http://www.w3schools.com/css/pr_background-repeat.asp">css property: background-repeat</a>]'),
      '#options' => array(
        'no-repeat' => $this->t('No Repeat'),
        'repeat' => $this->t('Tiled (repeat)'),
        'repeat-x' => $this->t('Repeat Horizontally (repeat-x)'),
        'repeat-y' => $this->t('Repeat Vertically (repeat-y)'),
      ),
      '#default_value' => $settings['css_settings']['bg_image_repeat'],
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();
		$options = $this->getResponsiveImageStyles();

    if (isset($settings['css_settings']['bg_image_selector'])) {
      $summary[] = $this->t('CSS Selector: @selector', array('@selector' => $settings['css_settings']['bg_image_selector']));
    }
    else {
      $summary[] = $this->t('No selector');
    }

    if (isset($options[$settings['image_style']])) {
      $summary[] = $this->t('URL for image style: @style', array('@style' => $options[$settings['image_style']]));
    }
    else {
      $summary[] = $this->t('Original image style');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    $settings = $this->getSettings();
    $css_settings = $settings['css_settings'];
		$selectors = array_filter(preg_split('/$/', $css_settings['bg_image_selector']));

		$style = null;
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files) || empty($settings['image_style'])) {
      return $elements;
    }

    $defaults = self::defaultSettings();
    $css_settings += $defaults;

    // Pull the default css setting if not provided.
    $bg_color = $css_settings['bg_image_color'];
    $bg_x = $css_settings['bg_image_x'];
    $bg_y = $css_settings['bg_image_y'];
    $attachment = $css_settings['bg_image_attachment'];
    $repeat = $css_settings['bg_image_repeat'];
    $important = $css_settings['bg_image_important'];
    $background_size = 'cover';
    $media_query = $css_settings['bg_image_media_query'];

    // If important is true, we turn it into a string for css output.
    if ($important) {
      $important = '!important';
    }
    else {
      $important = '';
    }

    // Compute the background size property and any proprietary prefixes / filters
		$bg_size = sprintf('background-size: %s %s;', $background_size, $important);
		// Let's cover ourselves for other browsers as well...
		$bg_size .= sprintf('-webkit-background-size: %s %s;', $background_size, $important);
		$bg_size .= sprintf('-moz-background-size: %s %s;', $background_size, $important);
		$bg_size .= sprintf('-o-background-size: %s %s;', $background_size, $important);
		$bg_size .= sprintf("filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='%s', sizingMethod='scale');", $image_path);
		$bg_size .= sprintf("-ms-filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='%s', sizingMethod='scale');", $image_path);

		// Compute base styles that apply to all instances
		$base = join(', ', $selectors) . ' {';
		$base .= sprintf('background-color: %s %s;', $bg_color, $important);
		$base .= sprintf('background-repeat: %s %s;', $repeat, $important);
		$base .= sprintf('background-attachment: %s %s;', $attachment, $important);
		$base .= sprintf('background-position: %s %s %s;', $bg_x, $bg_y, $important);
		$base .= $bg_size;
		$base .= '}';

    foreach ($files as $delta => $file) {
			// Use specified selectors in round-robin order
			$selector = $selectors[$index % count($selectors)];

			$vars = array(
				'uri' => $file->getFileUri(),
				'responsive_image_style_id' => $settings['image_style'],
			);
			template_preprocess_responsive_image($vars);

			// Split each source into multiple rules
			foreach ($vars['sources'] as $source) {
				$attr = $source->toArray();

				$srcset = explode(', ', $attr['srcset']);

				foreach ($srcset as $src) {
					list($src, $res) = explode(' ', $src);

					$media = $attr['media'];

					// Add "retina" to media query if this is a 2x image
					if($res && $res === '2x') {
						$media = "$media and (-webkit-min-device-pixel-ratio: 2), $media and (min-resolution: 192dpi)";
					}

					$media = str_replace('screen (max-width', 'screen and (max-width', $media);

					$style .= "@media $media { $selector {" . $this->getBackgroundImageCss($src, $css_settings) . ' } }';
				}
			}

      $elements['#attached']['html_head'][] = [[
        '#tag' => 'style',
        '#value' => $base . $style,
      ], 'responsive_bg_image_formatter_css_' . $delta,
      ];

			$index++;
    }

    return $elements;
  }

  /**
   * Function taken from the module 'bg_image'.
   *
   * Adds a background image to the page using the
   * css 'background' property.
   *
   * @param string $image_path
   *    The path of the image to use. This can be either
   *      - A relative path e.g. sites/default/files/image.png
   *      - A uri: e.g. public://image.png.
   * @param array $css_settings
   *    An array of css settings to use. Possible values are:
   *      - bg_image_selector: The css selector to use
   *      - bg_image_color: The background color
   *      - bg_image_attachment: The attachment property (scroll or fixed)
   *      - bg_image_repeat: The repeat settings
   *      - bg_image_background_size: The background size property if necessary
   *    Default settings will be used for any values not provided.
   * @param string $image_style
   *   Optionally add an image style to the image before applying it to the
   *   background.
   *
   * @return array
   *   The array containing the CSS.
   */
  public function getBackgroundImageCss($image_path, $css_settings = array(), $image_style = NULL) {

		$style = sprintf('background-image: url("%s") %s;', $image_path, $important);

		return $style;
  }

}
