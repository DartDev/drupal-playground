<?php

declare(strict_types=1);

namespace Drupal\dp_entity_types;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of duck type entities.
 *
 * @see \Drupal\dp_entity_types\Entity\DuckType
 */
final class DuckTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = [
      'data' => $this->t('Name'),
      'class' => ['label'],
    ];

    $header['description'] = [
      'data' => $this->t('Description'),
      'class' => ['description', RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['label'] = [
      'data' => $entity->label(),
      'class' => ['label'],
    ];

    $row['description'] = [
      'data' => ['#markup' => $entity->getDescription()],
      'class' => ['description'],
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();

    $build['table']['#empty'] = $this->t(
      'No duck types available. <a href=":link">Add duck type</a>.',
      [':link' => Url::fromRoute('entity.dp_duck_type.add_form')->toString()],
    );

    return $build;
  }

}
