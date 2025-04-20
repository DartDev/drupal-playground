<?php

declare(strict_types=1);

namespace Drupal\Tests\dp_entity_types_preconfig\Kernel;

use Drupal\dp_entity_types\Entity\DuckType;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the existence of automatically created entity types.
 */
#[Group('dp_entity_types_preconfig')]
class EntityTypesExistTest extends KernelTestBase {

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
   * Tests the existence of automatically created entity types in the system.
   */
  public function testEntityTypesExist(): void {
    $duck_types = DuckType::loadMultiple();

    // There should be 2 duck types total after the module installation.
    self::assertCount(2, $duck_types);

    // We expect to have two specific duck types in the system, as defined
    // in the `config/install` directory of the module.
    self::assertArrayHasKey('heavy_assault', $duck_types);
    self::assertArrayHasKey('moral_support', $duck_types);
  }

}
