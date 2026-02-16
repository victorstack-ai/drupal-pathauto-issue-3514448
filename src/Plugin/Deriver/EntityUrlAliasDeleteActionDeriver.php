<?php

namespace Drupal\pathauto\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * Constructs the URL alias delete action deriver.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Manages entity type plugin definitions.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Manages the discovery of entity fields.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Pathauto alias actions are only relevant for fieldable canonical
      // entities that expose the base path field.
      if (
        !$entity_type->hasLinkTemplate('canonical') ||
        !is_subclass_of($entity_type->getClass(), FieldableEntityInterface::class)
      ) {
        continue;
      }

      $base_fields = $this->entityFieldManager->getBaseFieldDefinitions($entity_type_id);
      if (!isset($base_fields['path'])) {
        continue;
      }

      $this->derivatives[$entity_type_id] = [
        'label' => $this->t('Delete URL alias'),
        'type' => $entity_type_id,
      ] + $base_plugin_definition;
    }
    return $this->derivatives;
  }

}
