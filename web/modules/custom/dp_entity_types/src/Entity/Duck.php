<?php

declare(strict_types=1);

namespace Drupal\dp_entity_types\Entity;

use Drupal\content_translation\ContentTranslationHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dp_entity_types\DuckAccessControlHandler;
use Drupal\dp_entity_types\DuckInterface;
use Drupal\dp_entity_types\DuckListBuilder;
use Drupal\dp_entity_types\Form\DuckForm;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Defines the duck entity class.
 */
#[ContentEntityType(
  id: 'dp_duck',
  label: new TranslatableMarkup('Duck'),
  label_collection: new TranslatableMarkup('Ducks'),
  label_singular: new TranslatableMarkup('duck'),
  label_plural: new TranslatableMarkup('ducks'),
  entity_keys: [
    'id' => 'id',
    'revision' => 'revision_id',
    'langcode' => 'langcode',
    'bundle' => 'bundle',
    'label' => 'label',
    'uuid' => 'uuid',
    'owner' => 'uid',
    'status' => 'status',
    'can_quack' => 'can_quack',
  ],
  handlers: [
    'storage' => SqlContentEntityStorage::class,
    'storage_schema' => SqlContentEntityStorageSchema::class,
    'view_builder' => EntityViewBuilder::class,
    'access' => DuckAccessControlHandler::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => DuckForm::class,
      'add' => DuckForm::class,
      'edit' => DuckForm::class,
      'delete' => ContentEntityDeleteForm::class,
      'delete-multiple-confirm' => DeleteMultipleForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
      'revision' => RevisionHtmlRouteProvider::class,
    ],
    'list_builder' => DuckListBuilder::class,
    'translation' => ContentTranslationHandler::class,
  ],
  links: [
    'canonical' => '/dp-duck/{dp_duck}',
    'collection' => '/admin/content/dp-duck',
    'add-page' => '/dp-duck/add',
    'add-form' => '/dp-duck/add/{dp_duck_type}',
    'edit-form' => '/dp-duck/{dp_duck}/edit',
    'delete-form' => '/dp-duck/{dp_duck}/delete',
    'delete-multiple-form' => '/admin/content/dp-duck/delete-multiple',
    'version-history' => '/dp-duck/{dp_duck}/revisions',
    'revision' => '/dp-duck/{dp_duck}/revision/{dp_duck_revision}/view',
    'revision-delete-form' => '/dp-duck/{dp_duck}/revision/{dp_duck_revision}/delete',
    'revision-revert-form' => '/dp-duck/{dp_duck}/revision/{dp_duck_revision}/revert',
  ],
  admin_permission: 'administer dp_duck types',
  collection_permission: 'access dp_duck collection',
  bundle_entity_type: 'dp_duck_type',
  bundle_label: new TranslatableMarkup('Duck type'),
  base_table: 'dp_duck',
  data_table: 'dp_duck_field_data',
  revision_table: 'dp_duck_revision',
  revision_data_table: 'dp_duck_field_revision',
  translatable: TRUE,
  show_revision_ui: TRUE,
  label_count: [
    'singular' => '@count duck',
    'plural' => '@count ducks',
  ],
  field_ui_base_route: 'entity.dp_duck_type.edit_form',
  revision_metadata_keys: [
    'revision_user' => 'revision_uid',
    'revision_created' => 'revision_timestamp',
    'revision_log_message' => 'revision_log',
  ],
)]
final class Duck extends RevisionableContentEntityBase implements DuckInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Define a custom boolean field to always appear on each duck entity.
    //
    // The BaseFieldDefinition statements define both of the Database Schema
    // and the UI component in the administration theme.
    //
    // While parts of these definitions can be later adjusted, it must be
    // handled with care, as the Database Schema will remain unchanged.
    $fields['can_quack'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Can Quack'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Yes')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 5,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the duck was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the duck was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(): bool {
    return (bool) $this->get('status')->value;
  }

}
