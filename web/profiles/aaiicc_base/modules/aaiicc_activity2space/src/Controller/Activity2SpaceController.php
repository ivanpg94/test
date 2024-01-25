<?php

namespace Drupal\aaiicc_activity2space\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;

class Activity2SpaceController extends ControllerBase {

  public function autocompleteActivity(Request $request) {
    $matches = [];
    $string = $request->query->get('q');
    if (!$string) {
      return new JsonResponse($results);
    }
    $string = Xss::filter($string);
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'activity')
      ->condition('title', $string, 'CONTAINS')
      ->range(0, 10);
    $nids = $query->execute();
    $nodes = $nids ? \Drupal\node\Entity\Node::loadMultiple($nids) : [];
    if (!empty($nids)) {
      foreach ($nids as $nid) {
        $node = \Drupal\node\Entity\Node::load($nid);
        $matches[] = [
          'value' => $node->getTitle(),//EntityAutocomplete::getEntityLabels([$node]),
          'label' => $node->getTitle().' ('.$node->id().')',
        ];
      }
    }
    return new JsonResponse($matches);
  }

}
