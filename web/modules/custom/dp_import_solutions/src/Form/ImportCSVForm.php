<?php

declare(strict_types=1);

namespace Drupal\dp_import_solutions\Form;

use Drupal\Component\Utility\Environment;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\file\FileInterface;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an import solution form for the CSV file format.
 */
class ImportCSVForm extends FormBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The extension path resolver.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * ImportCSVForm constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extension_path_resolver
   *   The extension path resolver.
   */
  public function __construct(FileSystemInterface $file_system, ExtensionPathResolver $extension_path_resolver) {
    $this->fileSystem = $file_system;
    $this->extensionPathResolver = $extension_path_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ImportCSVForm {
    return new static(
      $container->get('file_system'),
      $container->get('extension.path.resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dp_import_solutions_csv';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Define our method of form encoding to support file uploading.
    // Read more: https://stackoverflow.com/a/4526286
    // Read more: https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fs-enctype
    $form['#attributes'] = [
      'enctype' => 'multipart/form-data',
    ];

    // Supported file extensions should be defined as a plain text list.
    // For example: 'xlsx xls csv'; in our case it is simply 'csv'.
    $supported_extensions = 'csv';

    // Define the description part separately to keep things tidy.
    $description = $this->t('Supported file extensions:') . ' ' . $supported_extensions . '<br>';
    $description .= ($max_size = Environment::getUploadMaxSize()) ? $this->t(
      'The maximum size of the uploaded file is <strong>@max_size</strong>. Files that exceed this limit will be rejected.',
      ['@max_size' => ByteSizeMarkup::create($max_size)]
    ) : '';

    // File form element where a user can submit a file.
    $form['spreadsheet_file'] = [
      '#title' => $this->t('CSV File'),
      '#type' => 'file',
      '#description' => $description,
      '#upload_validators' => [
        'FileExtension' => ['extensions' => $supported_extensions],
      ],
      '#element_validate' => ['::validateFile'],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Import'),
      ],
    ];

    return $form;
  }

  /**
   * Validate the file upload.
   */
  public static function validateFile(&$element, FormStateInterface $form_state, &$complete_form): void {
    // Attempt to save CSV in the filesystem with error handling on failure.
    if ($file = _file_save_upload_from_form($element, $form_state, NULL, FileExists::Replace)) {
      // We store the file reference on the form state for a later use.
      $form_state->setValue('spreadsheet', $file);
    }
    else {
      // By referencing $element['#parents'][0] we automatically pick up the
      // topmost element name in the nested hierarchy in a simple way.
      // This is good for single-level of hierarchy form elements, but may
      // require customization for a deeper hierarchy.
      $form_state->setErrorByName($element['#parents'][0], t('Something went wrong while trying to upload the file.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($file = $form_state->getValue('spreadsheet')[0]) {
      $rows = $this->extractRows($file);
      $this->executeBatch($rows);
    }
    else {
      // Adding extra logic for the missing source file can be done here.
      $this->messenger()->addWarning($this->t('The CSV file has not been processed.'));
      $form_state->setRedirect('dp_import_solutions.csv');
    }
  }

  /**
   * Extracts an array of rows from CSV source for importing.
   *
   * We are using PhpSpreadsheet library to read our CSV files.
   * Find out more: https://github.com/PHPOffice/PhpSpreadsheet.
   */
  protected function extractRows(FileInterface $file): array {
    $absolute_path = $this->fileSystem->realpath($file->getFileUri());

    // Our form validation step verifies that the file comes in CSV format,
    // and PhpSpreadsheet has its own exception handling.
    // It is possible to go deeper and implement extra validation,
    // but we are going to assume the source content is valid by default.
    $reader = new Csv();
    $spreadsheet = $reader->load($absolute_path);
    $active_sheet = $spreadsheet->getActiveSheet();
    $rows = [];

    foreach ($active_sheet->getRowIterator() as $row) {
      $cell_iterator = $row->getCellIterator();
      $cell_iterator->setIterateOnlyExistingCells(FALSE);
      $cells = [];

      foreach ($cell_iterator as $cell) {
        $cells[] = $cell->getValue();
      }

      $rows[] = $cells;
    }

    return $rows;
  }

  /**
   * Configures and sets a batch operation.
   */
  protected function executeBatch(array $rows): void {
    $batch_builder = new BatchBuilder();

    // These properties are set by default when the batch object is constructed.
    // This is a good place to customize them according to your specifics.
    $batch_builder
      ->setTitle($this->t('Processing CSV file'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));

    $batch_filename = $this->extensionPathResolver->getPath('module', 'dp_import_solutions') . '/src/Batch/ImportCSVBatch.php';
    $batch_builder->setFile($batch_filename);

    $batch_operation = [
      'Drupal\dp_import_solutions\Batch\ImportCSVBatch',
      'batchProcess',
    ];
    $batch_arguments = [$rows];
    $batch_builder->addOperation($batch_operation, $batch_arguments);

    // The batch finalization callback is defined separately and can point to
    // a different location, allowing for easy code reuse.
    $batch_finished = [
      'Drupal\dp_import_solutions\Batch\DefaultBatch',
      'batchFinished',
    ];
    $batch_builder->setFinishCallback($batch_finished);

    batch_set($batch_builder->toArray());
  }

}
