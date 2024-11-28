<?php

declare(strict_types=1);

namespace Drupal\Tests\dp_import_solutions\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\dp_import_solutions\Batch\ImportCSVBatch;
use Drupal\dp_import_solutions\Form\ImportCSVForm;
use Drupal\file\Entity\File;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the Import CSV form.
 *
 * @coversDefaultClass \Drupal\dp_import_solutions\Form\ImportCSVForm
 */
#[Group('dp_import_solutions')]
class ImportCSVFormTest extends KernelTestBase {

  /**
   * The Import CSV form object to test against.
   *
   * @var \Drupal\dp_import_solutions\Form\ImportCSVForm
   */
  protected $importCSVForm;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'file',
    'field',
    'text',
    'node',
  ];

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(static::$modules);

    $this->installEntitySchema('user');
    $this->installEntitySchema('file');
    $this->installEntitySchema('node');

    $this->importCSVForm = new ImportCSVForm(
      $this->container->get('file_system'),
      $this->container->get('extension.path.resolver')
    );
  }

  /**
   * Tests the structure of \Drupal\dp_import_solutions\Form\ImportCSVForm.
   *
   * This is a cheap way to verify the form structure and make sure that all
   * fields and their setup remain as intended.
   *
   * The downside however, is that this structure may be hooked into from the
   * other locations and further altered, which could invalidate the tests.
   *
   * Normally this isn't the case within an isolated ecosystem of a testing
   * suite, and everything that happens to the original code in a 3rd party
   * hook is quite literally outside the scope of a given module test.
   *
   * The generally accepted workflow is to make direct changes to the custom
   * code, instead of altering it in 3rd party hooks, and thus such simplified
   * testing approach can be deemed sufficient.
   */
  public function testFormStructureSimple() {
    // Make sure Form ID remains unchanged.
    $this->assertEquals('dp_import_solutions_csv', $this->importCSVForm->getFormId());

    $form_structure = $this->importCSVForm->buildForm([], new FormState());

    // Form encoding must be defined to support file uploading.
    $this->assertEquals('multipart/form-data', $form_structure['#attributes']['enctype']);

    // There must be a file upload element.
    $this->assertArrayHasKey('spreadsheet_file', $form_structure);
    $this->assertEquals('file', $form_structure['spreadsheet_file']['#type']);
    $this->assertEquals('csv', $form_structure['spreadsheet_file']['#upload_validators']['FileExtension']['extensions']);

    // There must be a submit button.
    $this->assertArrayHasKey('actions', $form_structure);
    $this->assertEquals('actions', $form_structure['actions']['#type']);
    $this->assertEquals('submit', $form_structure['actions']['submit']['#type']);
  }

  /**
   * Tests structure of \Drupal\dp_import_solutions\Form\ImportCSVForm.
   *
   * Here instead of calling the buildForm() method directly, we are going
   * to use the Form Builder service from the Drupal Core.
   *
   * The getForm() method of the core Form Builder service is calling the
   * buildForm() method from its scope, that in turn calls the prepareForm()
   * method, that invokes the form alteration hooks.
   *
   * This, in theory, allows us to retrieve the final altered version of the
   * form in question, however to achieve that within the scope of a test
   * it would be necessary to also enable modules with the relevant hooks.
   */
  public function testFormStructureThorough() {
    // Get a form render array.
    $form = \Drupal::formBuilder()->getForm(ImportCSVForm::class);

    // Make sure the original Form ID remains unchanged.
    $this->assertEquals('dp_import_solutions_csv', $form['#form_id']);

    // Form encoding must be defined to support file uploading.
    $this->assertEquals('multipart/form-data', $form['#attributes']['enctype']);

    // There must be a file upload element.
    $this->assertArrayHasKey('spreadsheet_file', $form);
    $this->assertEquals('file', $form['spreadsheet_file']['#type']);
    $this->assertEquals('csv', $form['spreadsheet_file']['#upload_validators']['FileExtension']['extensions']);

    // There must be a submit button.
    $this->assertArrayHasKey('actions', $form);
    $this->assertEquals('actions', $form['actions']['#type']);
    $this->assertEquals('submit', $form['actions']['submit']['#type']);
  }

  /**
   * Tests the CSV data import procedure.
   *
   * Simulates an equivalent sequence of steps within a Kernel test environment,
   * without running an actual batch.
   */
  public function testFormFileImport() {
    // Prepare a query to run the asserts against.
    $query = \Drupal::entityQuery('node')
      ->sort('created', 'DESC')
      ->range(0, 10);
    $query->accessCheck(FALSE);

    // There should be no nodes initially.
    $this->assertEquals(0, $query->count()->execute());

    // Use the core ExtensionPathResolver service to consistently retrieve an
    // actual source fixture location in the filesystem.
    $file_name = 'articles.csv';
    $file_path = \Drupal::service('extension.path.resolver')->getPath('module', 'dp_import_solutions') . '/tests/fixtures/' . $file_name;

    // Register our fixture in the database.
    $file = File::create([
      'uri' => $file_path,
      'status' => 1,
    ]);
    $file->save();

    // File entity must have an ID assigned after a successful save.
    $this->assertNotNull($file->id());

    // Create a reflection of a protected method on the form to extract rows.
    $extractRowsReflection = new \ReflectionMethod($this->importCSVForm, 'extractRows');
    $rows = $extractRowsReflection->invoke($this->importCSVForm, $file);

    // Node storage is retrieved just once and then passed to create a node.
    $node_storage = \Drupal::service('entity_type.manager')->getStorage('node');

    // Batch substitute to have the nodes created.
    foreach ($rows as $row) {
      ImportCSVBatch::createNode($node_storage, $row);
    }

    // After the simulated batch we should have 3 nodes.
    $this->assertEquals(3, $query->count()->execute());
  }

}
