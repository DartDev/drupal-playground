<?php

declare(strict_types=1);

namespace Drupal\dp_entity_types\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the duck edit form.
 */
final class DuckForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->toLink()->toString()];
    $logger_args = [
      '%label' => $this->entity->label(),
      'link' => $this->entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New duck %label has been created.', $message_args));
        $this->logger('dp_entity_types')->notice('New duck %label has been created.', $logger_args);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The duck %label has been updated.', $message_args));
        $this->logger('dp_entity_types')->notice('The duck %label has been updated.', $logger_args);
        break;

      default:
        throw new \LogicException('Could not save the duck.');
    }

    $form_state->setRedirectUrl($this->entity->toUrl());

    return $result;
  }

}
