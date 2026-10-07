<?php

namespace Drupal\utexas_devel\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush commands.
 */
class UtexasDevel extends DrushCommands {

  /**
   * Command for generating Promo List items.
   */
  #[CLI\Command(name: 'utexas_devel:promo-list', aliases: ['udpl'])]
  #[CLI\Argument(name: 'type', description: 'Type of list to generate')]
  #[CLI\Option(name: 'number', description: 'Number, either number of items or number of bytes')]
  #[CLI\Usage(name: 'utexas_devel:promo-list large --number=64000', description: 'utexas_devel:promo-list large --number=64000')]
  #[CLI\Usage(name: 'utexas_devel:promo-list large --number=64', description: 'utexas_devel:promo-list many --number=64')]
  public function promolist($type, $options = ['number' => '1']) {
    $items = [];
    if (isset($options['number'])) {
      $number = $options['number'];
    }
    else {
      $number = 1;
    }
    $list = ['headline' => "Promo list with $number items"];
    if ($type == 'many') {
      $this->output()->writeln("Generating Promo list with $number items");
      $range = range(1, $number);
      foreach ($range as $delta) {
        $items[] = [
          'item' => [
            'headline' => "Item $delta",
            'image' => 0,
            'copy' => [
              'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ];
      }
      $list["promo_list_items"] = serialize($items);
    }
    else {
      $this->output()->writeln("Generating single Promo List with $number bytes");
      $bytes = random_bytes($number / 2);
      $large_text = bin2hex($bytes);
      $list = [
        'headline' => "Promo list with $number bytes",
        "promo_list_items" => serialize([
          [
            'item' => [
              'headline' => "Item 1",
              'image' => 0,
              'copy' => [
                'value' => $large_text,
                'format' => 'restricted_html',
              ],
              'link' => [
                'uri' => 'https://www.utexas.edu',
              ],
            ],
          ],
        ]),
      ];
    }
    // Bypass requirement to specify allowed classes since they are unknown.
    // phpcs:ignore
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_promo_list',
      'reusable' => TRUE,
    ]);
    $block->info = 'Promo List';
    $block->uuid = md5('promo_list_1' . time());
    $block->field_block_pl = $list;
    $block->save();
    $id = $block->id();
    $this->output()->writeln("See /admin/content/block/$id");
  }

  /**
   * Command for generating Promo Unit items.
   */
  #[CLI\Command(name: 'utexas_devel:promo-unit', aliases: ['udpl'])]
  #[CLI\Argument(name: 'type', description: 'Type of list to generate')]
  #[CLI\Option(name: 'number', description: 'Number, either number of items or number of bytes')]
  #[CLI\Usage(name: 'utexas_devel:promo-unit large --number=64000', description: 'utexas_devel:promo-unit large --number=64000')]
  #[CLI\Usage(name: 'utexas_devel:promo-unit large --number=64', description: 'utexas_devel:promo-unit many --number=64')]
  public function promounit($type, $options = ['number' => '1']) {
    $items = [];
    if (isset($options['number'])) {
      $number = $options['number'];
    }
    else {
      $number = 1;
    }
    $list = ['headline' => "Promo Unit with $number items"];
    if ($type == 'many') {
      $this->output()->writeln("Generating Promo Unit with $number items");
      $range = range(1, $number);
      foreach ($range as $delta) {
        $items[] = [
          'item' => [
            'headline' => "Item $delta",
            'image' => 0,
            'copy' => [
              'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
              'format' => 'restricted_html',
            ],
            'link' => [
              'uri' => 'https://www.utexas.edu',
            ],
          ],
        ];
      }
      $list["promo_unit_items"] = serialize($items);
    }
    else {
      $this->output()->writeln("Generating single Promo Unit with $number bytes");
      $bytes = random_bytes($number / 2);
      $large_text = bin2hex($bytes);
      $list = [
        'headline' => "Promo Unit with $number bytes",
        "promo_unit_items" => serialize([
          [
            'item' => [
              'headline' => "Item 1",
              'image' => 0,
              'copy' => [
                'value' => $large_text,
                'format' => 'restricted_html',
              ],
              'link' => [
                'uri' => 'https://www.utexas.edu',
              ],
            ],
          ],
        ]),
      ];
    }
    // We allow non-dependency injection calls.
    // phpcs:ignore
    $blockEntityManager = \Drupal::entityTypeManager()
      ->getStorage('block_content');
    $block = $blockEntityManager->create([
      'type' => 'utexas_promo_unit',
      'reusable' => TRUE,
    ]);
    $block->info = 'Promo Unit';
    $block->uuid = md5('promo_unit_1' . time());
    $block->field_block_pu = $list;
    $block->save();
    $id = $block->id();
    $this->output()->writeln("See /admin/content/block/$id");
  }

}
