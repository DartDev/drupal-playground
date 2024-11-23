<?php

declare(strict_types=1);

namespace Drupal\dp_import_solutions\Batch;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Provides static Batch methods for basic CSV import.
 */
class ImportCSVBatch {

  /**
   * Import CSV file batch process callback.
   *
   * @param array $rows
   *   Array of rows from the CSV source to import as new nodes.
   * @param array|object $context
   *   Context for operations.
   */
  public static function batchProcess(array $rows, array|object &$context): void {
    $limit = 30;

    if (empty($context['sandbox'])) {
      $context['sandbox']['items'] = array_chunk($rows, $limit);
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_chunk'] = 0;
      $context['sandbox']['max'] = count($rows);
    }

    $current_chunk = $context['sandbox']['current_chunk'];

    if (isset($context['sandbox']['items'][$current_chunk])) {
      $node_storage = \Drupal::service('entity_type.manager')->getStorage('node');
      $rows = $context['sandbox']['items'][$current_chunk];

      foreach ($rows as $row) {
        try {
          static::createNode($node_storage, $row);
        }
        catch (EntityStorageException $ex) {
          $message = $ex->getMessage();
          \Drupal::service('messenger')->addError($message);
          \Drupal::service('logger.factory')->get('dp_import_solutions')->error($message);
        }

        $context['sandbox']['progress']++;

        $context['message'] = t('Now processing row @progress of @max', [
          '@progress' => $context['sandbox']['progress'],
          '@max' => $context['sandbox']['max'],
        ]);
      }
    }

    $context['sandbox']['current_chunk']++;

    $context['results']['processed'] = $context['sandbox']['progress'];

    if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Creates a node from the passed array.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   Node storage.
   * @param array $row
   *   Array with node data.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function createNode(EntityStorageInterface $node_storage, array $row): void {
    $node = $node_storage->create([
      'type' => 'article',
      'title' => $row[0],
      'body' => [
        'value' => $row[1],
        'format' => 'plain_text',
      ],
      'status' => 1,
      'uid' => 1,
    ]);

    $node->save();
  }

}
