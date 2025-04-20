<?php

declare(strict_types=1);

namespace Drupal\Tests\dp_entity_types_preconfig\Kernel;

use Drupal\dp_entity_types\Entity\DuckType;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the uninstallation of the module.
 */
#[Group('dp_entity_types_preconfig')]
class UninstallTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'dp_entity_types',
    'dp_entity_types_preconfig',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(static::$modules);
  }

  /**
   * Tests the uninstallation behavior of the module.
   *
   * The module is supposed to automatically remove the entity types it created.
   *
   * Clean version where preconfigured entity types are the only entity types.
   */
  public function testUninstallClean(): void {
    $duck_types = DuckType::loadMultiple();

    // There should be 2 duck types total after the module installation.
    self::assertCount(2, $duck_types);

    // Uninstall the module to have the uninstallation hooks kick in.
    \Drupal::service('module_installer')->uninstall(['dp_entity_types_preconfig']);

    $duck_types = DuckType::loadMultiple();

    // There should be 0 duck types total after the module uninstallation.
    self::assertCount(0, $duck_types);
  }

  /**
   * Tests the uninstallation behavior of the module.
   *
   * The module is supposed to automatically remove the entity types it created.
   *
   * Dirty version where manual entity types exist in the system.
   */
  public function testUninstallDirty(): void {
    $duck_types = DuckType::loadMultiple();

    // There should be 2 duck types total after the module installation.
    self::assertCount(2, $duck_types);

    // Add a manually created duck type, not governed by the module.
    $manual_duck_type = DuckType::create([
      'id' => 'manual_duck_type',
      'label' => 'Manual Duck Type',
    ]);
    $manual_duck_type->save();

    $duck_types = DuckType::loadMultiple();

    // Verify we have 3 duck types now - two preconfigured and one manual.
    self::assertCount(3, $duck_types);
    self::assertArrayHasKey('heavy_assault', $duck_types);
    self::assertArrayHasKey('moral_support', $duck_types);
    self::assertArrayHasKey('manual_duck_type', $duck_types);

    // Uninstall the module to have the uninstallation hooks kick in.
    \Drupal::service('module_installer')->uninstall(['dp_entity_types_preconfig']);

    $duck_types = DuckType::loadMultiple();

    // In the end we should only have 1 manual duck type in the system.
    self::assertCount(1, $duck_types);
    self::assertArrayHasKey('manual_duck_type', $duck_types);
  }

}
