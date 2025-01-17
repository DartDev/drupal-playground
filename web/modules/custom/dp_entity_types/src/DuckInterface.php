<?php

declare(strict_types=1);

namespace Drupal\dp_entity_types;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a duck entity type.
 */
interface DuckInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
