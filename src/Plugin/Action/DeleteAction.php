<?php

namespace Drupal\pathauto\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Pathauto entity delete action.
 *
 * @Action(
 *   id = "entity:pathauto_delete_alias",
 *   label = @Translation("Delete URL alias of an entity"),
 *   deriver = "Drupal\pathauto\Plugin\Derivative\EntityUrlAliasDeleteActionDeriver"
 * )
 */
class DeleteAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!is_null($entity)) {
      \Drupal::service('pathauto.alias_storage_helper')->deleteEntityPathAll($entity);
      $entity->get('path')->first()->get('pathauto')->purge();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIfHasPermission($account, 'delete url aliases');
    return $return_as_object ? $result : $result->isAllowed();
  }

}
