<?php

namespace Drupal\ui_patterns_views_style\Plugin\UiPatterns\Source;

use Drupal\ui_patterns\Plugin\PatternSourceBase;

/**
 * Defines view style source plugin.
 *
 * @UiPatternsSource(
 *   id = "view_style",
 *   label = @Translation("View style"),
 *   tags = {
 *     "view_style"
 *   }
 * )
 */
class ViewStyleSource extends PatternSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getSourceFields() {
    $sources = [];
    // The title of this group of rows. May be empty.
    $sources[] = $this->getSourceField('title', 'title');
    $sources[] = $this->getSourceField('rows', 'rows');
    return $sources;
  }

}
