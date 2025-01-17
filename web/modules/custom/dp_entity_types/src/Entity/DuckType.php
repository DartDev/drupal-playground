<?php

declare(strict_types=1);

namespace Drupal\dp_entity_types\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\dp_entity_types\DuckTypeInterface;

/**
 * Defines the Duck type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "dp_duck_type",
 *   label = @Translation("Duck type"),
 *   label_collection = @Translation("Duck types"),
 *   label_singular = @Translation("duck type"),
 *   label_plural = @Translation("ducks types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count ducks type",
 *     plural = "@count ducks types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\dp_entity_types\Form\DuckTypeForm",
 *       "edit" = "Drupal\dp_entity_types\Form\DuckTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\dp_entity_types\DuckTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer dp_duck types",
 *   bundle_of = "dp_duck",
 *   config_prefix = "dp_duck_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/dp_duck_types/add",
 *     "edit-form" = "/admin/structure/dp_duck_types/manage/{dp_duck_type}",
 *     "delete-form" = "/admin/structure/dp_duck_types/manage/{dp_duck_type}/delete",
 *     "collection" = "/admin/structure/dp_duck_types",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "new_revision",
 *   },
 * )
 */
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
