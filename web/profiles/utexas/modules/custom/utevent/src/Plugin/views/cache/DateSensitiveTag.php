<?php

namespace Drupal\utevent\Plugin\views\cache;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsCache;
use Drupal\views\Plugin\views\cache\None;

/**
 * Date-sensitive caching of query results for Views displays.
 *
 * @ingroup views_cache_plugins
 */
#[ViewsCache(
  id: 'utevent_date_sensitive_tag',
  title: new TranslatableMarkup('Date-sensitive tag-based'),
  help: new TranslatableMarkup('Tag-based caching of date-sensitive data. Caches will be cleared on each new day.')
)]
class DateSensitiveTag extends None {

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Date-sensitive tag-based');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();
    $tags[] = 'date_sensitive';
    return $tags;
  }

}
