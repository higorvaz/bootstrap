<?php
/**
 * @file
 * input.vars.php
 */

use Drupal\Core\Template\Attribute;

/**
 * Preprocess input.
 */
function bootstrap_preprocess_input(&$variables) {
  $config = \Drupal::config('bootstrap.settings');
  $element = &$variables['element'];
  $attributes = new Attribute($variables['attributes']);

  // Set the element's attributes.
  \Drupal\Core\Render\Element::setAttributes($element, array('id', 'name', 'value', 'type'));

  // Setup a default "icon" variable. This allows #icon to be passed
  // to every template and theme function.
  // @see https://drupal.org/node/2219965
  if (!isset($element['#icon'])) {
    $variables['element']['#icon'] = NULL;
  }
  if (!isset($element['#icon_position'])) {
    $variables['element']['#icon_position'] = 'before';
  }

  // Handle button inputs.
  if (_bootstrap_is_button($element)) {
    $variables['attributes']['class'][] = 'btn';
    _bootstrap_colorize_button($variables);
    _bootstrap_iconize_button($element);

    // Add button size, if necessary.
    if ($size = $config->get('bootstrap_button_size')) {
      $variables['attributes']['class'][] = $size;
    }

    // Add in the button type class.
    $variables['attributes']['class'][] = 'form-' . $element['#type'];
    $variables['label'] = $element['#value'];
  }

  _bootstrap_prerender_input($variables);

  // Additional Twig variables.
  $variables['icon'] = $element['#icon'];
  $variables['attributes']['title'] = $variables['attributes']['value'];
  $variables['element'] = $element;
}

function _bootstrap_prerender_input(&$variables) {
  $element = $variables['element'];
  $type = $element['#type'];

  // Only add the "form-control" class for specific element input types.
  $types = array(
    // Core.
    'color',
    'date',
    'email',
    'entity_autocomplete',
    'machine_name',
    'number',
    'password',
    'password_confirm',
    'range',
    'search',
    'select',
    'tel',
    'textfield',
    'url',
    // Webform module.
    'webform_email',
    'webform_number',
  );

  if (!empty($type) && (in_array($type, $types) || ($type === 'file' && empty($element['#managed_file'])))) {
    $variables['attributes']['class'][] = 'form-control';
  }

  // Tooltips for non- radios and checkboxes.
  if (!empty($type) && ($type !== 'checkbox' || $type !== 'radio' || $type !== 'checkboxes' || $type !== 'radios')) {
    if (!empty($element['#description']) && empty($element['#attributes']['title']) && _bootstrap_tooltip_description($element['#description'])) {
      $variables['attributes']['title'] = $element['#description'];
    }

    if (!empty($variables['attributes']['title'])) {
      $variables['attributes']['data-toggle'] = 'tooltip';
    }
  }
}
