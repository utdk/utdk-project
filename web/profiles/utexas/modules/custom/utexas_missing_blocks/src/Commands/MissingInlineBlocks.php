<?php

namespace Drupal\utexas_missing_blocks\Commands;

use Drupal\utexas_missing_blocks\Controller\MissingBlockReport;
use Drupal\utexas_missing_blocks\Defuser;
use Drush\Commands\DrushCommands;

/**
 * Find missing inline blocks.
 */
class MissingInlineBlocks extends DrushCommands {

  /**
   * Commands for auditing inline blocks.
   *
   * @command utexas_missing_blocks:audit
   * @aliases mib
   *
   * @usage utexas_missing_blocks:audit
   */
  public function audit() {
    $data = MissingBlockReport::prepareData();
    $this->output()->writeln('Cloned Flex Pages in site: ' . $data['cloned']);
    $this->output()->writeln('Pages with missing inline blocks: ' . $data['missing_inline']);
    $this->output()->writeln('Pages with missing reusable blocks: ' . $data['missing_reusable']);
  }

  /**
   * Commands for auditing inline blocks.
   *
   * @command utexas_missing_blocks:defuse
   * @aliases mibd
   *
   * @usage utexas_missing_blocks:defuse
   */
  public function defuse() {
    $data = Defuser::defuse();
    $missing = [];
    $fixed = [];
    foreach ($data as $key => $values) {
      if (isset($values['nodes_with_missing_blocks'])) {
        foreach ($values['nodes_with_missing_blocks'] as $n => $t) {
          $missing[$n] = $t;
        }
      }
      else {
        $fixed[] = $key;
      }
    }
    $this->output()->writeln('Entities remediated: ' . count($fixed));
    if (count($fixed) !== 0) {
      $this->output()->writeln('Entity IDs (remediated): ' . implode(', ', $fixed));
    }
    $this->output()->writeln('Entities with unrecoverable blocks: ' . count($missing));
    if (count($missing) !== 0) {
      $this->output()->writeln('Entity IDs (unrecoverable): ' . implode(', ', array_keys($missing)));
    }
    $this->output()->writeln('A full report has been written to the State API with key "missing_inline_block_defusement_report"');
  }

}
