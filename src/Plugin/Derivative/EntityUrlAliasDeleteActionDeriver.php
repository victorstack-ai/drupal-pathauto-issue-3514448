<?php

namespace Drupal\pathauto\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate Delete URL Alias action plugin for each enabled entity type.
 */
class EntityUrlAliasDeleteActionDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs the URL alias delete action deriver.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Manages entity type plugin definitions.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Manages the discovery of entity fields.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->config = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $config = $this->config->get('pathauto.settings');
    $enabled_entity_types = $config->get('enabled_entity_types');

    // Get all entity types enabled for pathauto.
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if (is_subclass_of($entity_type->getClass(), FieldableEntityInterface::class) && $entity_type->hasLinkTemplate('canonical')) {
        $field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($entity_type_id);
        $patterns_count = $this->entityTypeManager->getStorage('pathauto_pattern')->getQuery()
          ->condition('type', 'canonical_entities:' . $entity_type_id)
          ->count()
          ->execute();

        if (isset($field_definitions['path']) || $patterns_count) {
          $enabled_entity_types[] = $entity_type_id;
        }
      }
    }

    foreach (array_unique($enabled_entity_types) as $entity_type_id) {
      $action_id = 'delete_alias_' . $entity_type_id;
      $this->derivatives[$entity_type_id] = [
        'id' => $action_id,
        'label' => $this->t('Delete URL alias'),
        'type' => $entity_type_id,
      ] + $base_plugin_definition;
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
