<?php

namespace Drupal\utexas_instagram;

use Drupal\Component\Utility\Html;
use Drupal\text\TextSummary;
use Drupal\utexas_instagram_api\UTexasInstagramApi;

/**
 * Renders a UTexas Instagram 'feed' block.
 */
class InstagramHelper {

  /**
   * Retrieve the username associated with this integration.
   */
  public static function getAccountLink($config_id) {
    $request = new UTexasInstagramApi($config_id);
    if (empty($request->getConfigId())) {
      return '';
    }
    $obj = $request->getCurrentUserAccount();
    if (empty($obj)) {
      return '';
    }
    if (isset($obj->username)) {
      return $obj->username;
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public static function render($config_id) {

    $request = new UTexasInstagramApi($config_id);

    if (empty($request->getConfigId())) {
      $broken_block = [
        '#type' => 'inline_template',
        '#template' => '<div>Block is broken. Missing Instagram account authorization: {{ config_id }}.</div>',
        '#context' => [
          'config_id' => $config_id,
        ],
      ];
      return $broken_block;
    }

    $obj = $request->getMedia();

    if (empty($obj)) {
      return [];
    }

    $data = $obj->data;
    $instagram_posts = self::processData($data);

    $instagram_feed_posts = [];
    foreach ($instagram_posts as $post) {
      $instagram_feed_posts[] = [
        '#theme' => 'utexas_instagram_feed_post',
        '#pic_link' => $post['pic_link'] ?? '',
        '#pic_src' => $post['pic_src'] ?? '',
        '#pic_alt' => $post['pic_alt'] ?? '',
        '#pic_text' => $post['pic_text'] ?? '',
        '#username' => $post['username'] ?? '',
        '#timestamp' => $post['timestamp'] ?? '',
        '#attributes' => [
          'class' => [
            'utexas-instagram-feed-post',
          ],
        ],
        '#wrapper_attributes' => [
          'class' => [
            'utexas-instagram-feed__list-item',
          ],
        ],
      ];
    }

    $build = [];
    $js_data_id = Html::getUniqueId('utexas_instagram_feed_block');
    $block = [
      '#theme' => 'utexas_instagram_feed_block',
      '#instagram_posts' => [
        '#theme' => 'item_list',
        '#attributes' => [
          'class' => [
            'item-list',
            'utexas-instagram-feed__list',
          ],
        ],
        '#items' => $instagram_feed_posts,
      ],
      '#attributes' => [
        'class' => [
          'utexas-instagram-feed-block',
          'utexas-instagram-feed',
        ],
        'js-data-id' => $js_data_id,
      ],
      '#cache' => [
        'tags' => ['utexas_instagram_feed_blocks'],
      ],
      '#attached' => [
        'library' => [
          'utexas_instagram/utexas_instagram_feed_block',
        ],
      ],
    ];
    $build['instagram_feed_block'] = $block;

    return $build;
  }

  /**
   * Helper function to process instagram media data.
   */
  public static function processData(array $data) {
    foreach ($data as $key => $post) {
      if (isset($post->caption)) {
        $caption = str_replace("\n", "<br />", $post->caption);
        $instagram_posts[$key]['pic_text'] = \Drupal::service(TextSummary::class)->generate($caption, 'restricted_html', 300);
        $instagram_posts[$key]['pic_alt'] = $instagram_posts[$key]['pic_text'];
      }
      else {
        $instagram_posts[$key]['pic_alt'] = 'Image from ' . $data[0]->username;
      }

      $instagram_posts[$key]['pic_link'] = $post->permalink;

      if ($post->media_type === "VIDEO") {
        $instagram_posts[$key]['pic_src'] = $post->thumbnail_url;
      }
      else {
        $instagram_posts[$key]['pic_src'] = $post->media_url;
      }

      $instagram_posts[$key]['username'] = $post->username;
      $instagram_posts[$key]['timestamp'] = $post->timestamp;
    }

    return $instagram_posts;
  }

}
