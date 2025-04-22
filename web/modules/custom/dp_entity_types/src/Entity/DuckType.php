<?php

declare(strict_types=1);

namespace Drupal\dp_entity_types\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dp_entity_types\DuckTypeInterface;
use Drupal\dp_entity_types\DuckTypeListBuilder;
use Drupal\dp_entity_types\Form\DuckTypeForm;

/**
 * Defines the Duck type configuration entity.
 */
#[ConfigEntityType(
  id: 'dp_duck_type',
  label: new TranslatableMarkup('Duck type'),
  label_collection: new TranslatableMarkup('Duck types'),
  label_singular: new TranslatableMarkup('duck type'),
  label_plural: new TranslatableMarkup('ducks types'),
  config_prefix: 'dp_duck_type',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  handlers: [
    'access' => EntityAccessControlHandler::class,
    'form' => [
      'add' => DuckTypeForm::class,
      'edit' => DuckTypeForm::class,
      'delete' => EntityDeleteForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
    'list_builder' => DuckTypeListBuilder::class,
  ],
  links: [
    'add-form' => '/admin/structure/dp_duck_types/add',
    'edit-form' => '/admin/structure/dp_duck_types/manage/{dp_duck_type}',
    'delete-form' => '/admin/structure/dp_duck_types/manage/{dp_duck_type}/delete',
    'collection' => '/admin/structure/dp_duck_types',
  ],
  admin_permission: 'administer dp_duck types',
  bundle_of: 'dp_duck',
  label_count: [
    'singular' => '@count duck type',
    'plural' => '@count duck types',
  ],
  config_export: [
    'id',
    'label',
    'description',
    'new_revision',
  ],
)]
final class DuckType extends ConfigEntityBundleBase implements DuckTypeInterface {

  /**
   * The machine name of this duck type.
   */
  protected string $id;

  /**
   * The human-readable name of the duck type.
   */
  protected string $label;

  /**
   * A brief description of this duck type.
   */
  protected ?string $description = NULL;

  /**
   * Default value of the 'Create new revision' checkbox of this duck type.
   *
   * @var bool
   */
  protected bool $new_revision = TRUE;

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return $this->description ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision): void {
    $this->new_revision = $new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision(): bool {
    return $this->new_revision;
  }

}
