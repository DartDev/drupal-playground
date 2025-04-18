<?php

declare(strict_types=1);

namespace Drupal\dp_entity_types;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the Duck entity type.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 */
final class DuckAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    if ($account->hasPermission($this->entityType->getAdminPermission())) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return match($operation) {
      'view' => $this->checkViewAccess($entity, $account),
      'update' => AccessResult::allowedIfHasPermission($account, 'edit dp_duck'),
      'delete' => AccessResult::allowedIfHasPermission($account, 'delete dp_duck'),
      'delete revision' => AccessResult::allowedIfHasPermission($account, 'delete dp_duck revision'),
      'view all revisions', 'view revision' => AccessResult::allowedIfHasPermissions($account, ['view dp_duck revision', 'view dp_duck']),
      'revert' => AccessResult::allowedIfHasPermissions($account, ['revert dp_duck revision', 'edit dp_duck']),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, ['create dp_duck', 'administer dp_duck types'], 'OR');
  }

  /**
   * Checks whether the given entity can be accessed by the user.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check access.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function checkViewAccess(EntityInterface $entity, AccountInterface $account): AccessResultInterface {
    if (!$entity->isEnabled()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIfHasPermission($account, 'view dp_duck');
  }

}
