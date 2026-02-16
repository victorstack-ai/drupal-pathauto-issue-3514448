<?php

namespace Drupal\pathauto\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\pathauto\AliasStorageHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Pathauto entity delete action.
 */
#[Action(
  id: 'entity:pathauto_delete_alias',
  label: new TranslatableMarkup('Delete URL alias of an entity'),
  deriver: 'Drupal\pathauto\Plugin\Deriver\EntityUrlAliasDeleteActionDeriver',
)]
class DeleteAction extends ActionBase {

  /**
   * The path alias storage helper.
   *
   * @var \Drupal\pathauto\AliasStorageHelperInterface
   */
  protected AliasStorageHelperInterface $aliasStorageHelper;

  /**
   * Constructs a new DeleteAction instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\pathauto\AliasStorageHelperInterface|null $alias_storage_helper
   *   The path alias storage helper.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ?AliasStorageHelperInterface $alias_storage_helper = NULL,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // @phpstan-ignore globalDrupalDependencyInjection.useDependencyInjection
    $this->aliasStorageHelper = $alias_storage_helper ?: \Drupal::service('pathauto.alias_storage_helper');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('pathauto.alias_storage_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity !== NULL) {
      $this->aliasStorageHelper->deleteEntityPathAll($entity);
      if ($entity->hasField('path') && !$entity->get('path')->isEmpty()) {
        $entity->get('path')->first()->get('pathauto')->purge();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIfHasPermission($account, 'bulk delete aliases');
    return $return_as_object ? $result : $result->isAllowed();
  }

}
