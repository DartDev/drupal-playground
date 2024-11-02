<?php

declare(strict_types=1);

namespace Drupal\dp_import_solutions\Batch;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides default methods for custom batch operations.
 *
 * These methods can be especially useful when multiple batch
 * operations are queued at once, for example in an update hook.
 *
 * Specialized static batch classes can implement their own, unique
 * batch finalization callbacks, and it is a generally good thing to do
 * since this allows to customize the feedback and post-batch behavior.
 */
class DefaultBatch {

  use StringTranslationTrait;

  /**
   * Example batch process callback.
   *
   * @param array $nids
   *   Array of node IDs.
   * @param array|object $context
   *   Context for operations.
   */
  public static function batchProcess(array $nids, array|object &$context): void {
    // Amount of items to process per batch operation.
    $limit = 10;

    if (empty($context['sandbox'])) {
      // We split the items into chunks to easily load multiple nodes at once.
      $context['sandbox']['items'] = array_chunk($nids, $limit);
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_chunk'] = 0;
      $context['sandbox']['max'] = count($nids);
    }

    // For better code readability - place current chunk index into a variable.
    $current_chunk = $context['sandbox']['current_chunk'];

    // Verify that the next chunk exists.
    if (isset($context['sandbox']['items'][$current_chunk])) {
      // Request the node storage and load all nodes from the chunk at once.
      $node_storage = \Drupal::service('entity_type.manager')->getStorage('node');
      $nodes = $node_storage->loadMultiple($context['sandbox']['items'][$current_chunk]);

      foreach ($nodes as $node) {
        // Do the magic of processing each node as needed here, then save it.
        $node->save();

        // Update the batch progress tracking.
        $context['sandbox']['progress']++;

        $context['message'] = t('Now processing node @progress of @max', [
          '@progress' => $context['sandbox']['progress'],
          '@max' => $context['sandbox']['max'],
        ]);
      }
    }

    // Increment the chunk for the next iteration.
    $context['sandbox']['current_chunk']++;

    // Store the total number of processed items for reporting.
    $context['results']['processed'] = $context['sandbox']['progress'];

    // Update the percentage of finished items.
    if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Outcome of the operation.
   * @param array $results
   *   Array of results.
   * @param array $operations
   *   Array of operations.
   */
  public static function batchFinished(bool $success, array $results, array $operations): void {
    if ($success) {
      // Display the number of processed nodes.
      \Drupal::service('messenger')->addMessage(t('@count items processed.', [
        '@count' => $results['processed'] ?: 0,
      ]));
    }
    else {
      // An error occurred.
      // The $operations variable contains all operations that were not
      // processed prior to the error. We take the one that caused an issue.
      $error_operation = reset($operations);
      if ($error_operation) {
        $error_message = t('An error occurred while processing @operation with arguments: @arguments', [
          '@operation' => print_r($error_operation[0], TRUE),
          '@arguments' => print_r($error_operation[1], TRUE),
        ]);
        \Drupal::service('messenger')->addError($error_message);
        \Drupal::service('logger.factory')->get('dp_import_solutions')->error($error_message);
      }
    }
  }

}
