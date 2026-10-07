<?php

namespace Drupal\utexas_missing_blocks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

/**
 * Defines methods for creating the missing block report.
 */
class MissingBlockReport extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    $data = self::prepareData();
    $markup = [];
    $markup['intro'] = ['#markup' => Markup::create('<h3>Missing Inline Blocks</h3><p>This table provides an audit of missing site content due to a bug in page cloning. This bug was discovered by the Drupal Kit team in December 2022. Technical details of the issue are described at <a href="https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/2046">https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/2046</a>.</p><p>Cloned Flex Pages in site: ' . $data['cloned'] . '</p><p>Pages with missing inline blocks: ' . $data['missing_inline'] . '</p>')];
    $markup['inline_table'] = [
      '#type' => 'table',
      '#attributes' => [
        'class' => [''],
        'border' => '1',
        'style' => 'border-spacing:0px;text-align: left;',
      ],
      '#header' => ['Affected Page', 'Title of Missing Block(s)'],
      '#rows' => $data['inline_rows'],
    ];
    $markup['reusable'] = ['#markup' => Markup::create('<h3>Missing Reusable Blocks</h3><p>This table provides an audit of missing reusable blocks. This happens when a block in the site\'s shared <a href="/admin/content/block-content">Block Library</a> is deleted directly but a reference to it remains in page layout.</p><p>Pages with missing reusable blocks: ' . $data['missing_reusable'] . '</p>')];
    $markup['reusable_table'] = [
      '#type' => 'table',
      '#attributes' => [
        'class' => [''],
        'border' => '1',
        'style' => 'border-spacing:0px;text-align: left;',
      ],
      '#header' => ['Affected Page', 'Title of Missing Block(s)'],
      '#rows' => $data['reusable_rows'],
    ];
    return $markup;
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public static function prepareData() {
    $entity_storage = \Drupal::entityTypeManager()->getStorage('node');
    $connection = \Drupal::database();
    // Get a list of all block revision IDs in the system.
    $query = $connection->select('block_content_field_revision', 'b');
    $query->fields('b', ['revision_id']);
    $result = $query->execute();
    $block_revisions = $result->fetchCol();
    $existing_block_revisions = array_values($block_revisions);

    // SELECT * FROM `node_field_revision` WHERE `title` LIKE '%Cloned%'.
    $query = $connection->select('node_field_revision', 'n');
    $query->condition('n.title', '%' . $connection->escapeLike('Cloned') . '%', 'LIKE');
    $query->fields('n', ['nid']);
    $result = $query->execute();
    $all_cloned_pages = array_unique(array_values($result->fetchCol()));

    // Get all *current* Layout Builder layouts.
    $query = $connection->select('node__layout_builder__layout', 'n');
    $query->condition('n.bundle', 'utexas_flex_page', '=');
    $query->fields('n', ['entity_id', 'layout_builder__layout_section']);
    $result = $query->execute();
    $layouts = $result->fetchAll();
    $nodes_checked = [];
    $inline_rows = [];
    $reusable_rows = [];
    foreach ($layouts as $layout) {
      if (in_array($layout->entity_id, $all_cloned_pages)) {
        $nodes_checked[$layout->entity_id] = '';
      }
      // This serialized data is trusted from Layout Builder,
      // so we do not restrict object types in unserialize().
      // phpcs:ignore
      $section = unserialize($layout->layout_builder__layout_section);
      $components = $section->getComponents();
      foreach ($components as $component) {
        $plugin = $component->getPlugin();
        $component_array = $component->toArray();
        $deriver_id = $plugin->getPluginDefinition()['id'];
        if ($deriver_id == 'broken') {
          $reusable_rows[$layout->entity_id][] = $component_array['configuration']['label'];
        }
        elseif (isset($component_array['configuration']['block_revision_id']) && !in_array($component_array['configuration']['block_revision_id'], $existing_block_revisions)) {
          $inline_rows[$layout->entity_id][] = $component_array['configuration']['label'];
        }
      }
    }
    if (!empty($inline_rows)) {
      foreach ($inline_rows as $id => &$row) {
        $number_of_blocks = count($row);
        /** @var \Drupal\node\NodeInterface $node */
        $node = $entity_storage->load($id);
        $row = [
          Markup::create('<a href="/node/' . $id . '/layout">' . $node->getTitle() . '</a>'), '(' . $number_of_blocks . ') ' . implode(', ', $row),
        ];
      }
    }
    if (!empty($reusable_rows)) {
      foreach ($reusable_rows as $id => &$row) {
        $number_of_blocks = count($row);
        /** @var \Drupal\node\NodeInterface $node */
        $node = $entity_storage->load($id);
        $row = [
          Markup::create('<a href="/node/' . $id . '/layout">' . $node->getTitle() . '</a>'), '(' . $number_of_blocks . ') ' . implode(', ', $row),
        ];
      }
    }
    return [
      'cloned' => number_format(count($nodes_checked)),
      'missing_inline' => number_format(count($inline_rows)),
      'inline_rows' => $inline_rows,
      'reusable_rows' => $reusable_rows,
      'missing_reusable' => number_format(count($reusable_rows)),
    ];
  }

}
