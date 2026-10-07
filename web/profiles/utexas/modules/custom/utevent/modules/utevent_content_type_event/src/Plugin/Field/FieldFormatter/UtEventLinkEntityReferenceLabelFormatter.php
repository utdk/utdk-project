<?php

namespace Drupal\utevent_content_type_event\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'Event entity reference label link' formatter.
 */
#[FieldFormatter(
  id: 'utevent_link_entity_reference_label',
  label: new TranslatableMarkup('UT Event Label Link'),
  description: new TranslatableMarkup('Convert the label of the referenced entities to a link if it is a url.'),
  field_types: ['entity_reference']
)]
class UtEventLinkEntityReferenceLabelFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as $key => $element) {
      if ((isset($element['#plain_text']) && filter_var($element['#plain_text'], FILTER_VALIDATE_URL))) {
        /** @var \Drupal\Core\Link $link */
        $link = Link::fromTextAndUrl($element['#plain_text'], Url::fromUri($element['#plain_text']));
        $renderable_link = $link->toRenderable();
        $renderable_link['#cache'] = $element['#cache'];
        $elements[$key] = $renderable_link;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // Do not allow formatter to be used for any entities except the
    // utevent_event node bundle.
    return $field_definition->getTargetBundle() === 'utevent_event';
  }

}
