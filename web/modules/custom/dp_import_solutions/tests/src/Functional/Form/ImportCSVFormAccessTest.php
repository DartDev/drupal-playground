<?php

declare(strict_types=1);

namespace Drupal\Tests\dp_import_solutions\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the Import CSV form access.
 *
 * @coversDefaultClass \Drupal\dp_import_solutions\Form\ImportCSVForm
 */
#[Group('dp_import_solutions')]
final class ImportCSVFormAccessTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dp_import_solutions'];

  /**
   * Tests the form access based on permissions.
   */
  public function testFormAccess(): void {
    $form_path = Url::fromRoute('dp_import_solutions.csv')->toString();

    // Test form access from the perspective of a basic user.
    $basic_user = $this->drupalCreateUser();
    $this->drupalLogin($basic_user);
    $this->drupalGet($form_path);
    $this->assertSession()->statusCodeEquals(403);

    // Test form access from the perspective of a user with admin permissions.
    $admin_user = $this->drupalCreateUser(['access administration pages']);
    $this->drupalLogin($admin_user);
    $this->drupalGet($form_path);
    $this->assertSession()->statusCodeEquals(200);
  }

}
