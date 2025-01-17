<?php

namespace Drupal\dp_entity_types;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;

/**
 * Provides an interface defining a duck type config entity.
 */
interface DuckTypeInterface extends ConfigEntityInterface, RevisionableEntityBundleInterface {

  /**
   * Sets whether a new revision should be created by default.
   *
   * @param bool $new_revision
   *   TRUE if a new revision should be created by default.
   */
  public function setNewRevision(bool $new_revision);

  /**
   * Retrieves the description.
   *
   * @return string
   *   The description of this duck type.
   */
  public function getDescription(): string;

}
