<?php

namespace Drupal\utexas_block_social_links\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\utexas_block_social_links\Services\UTexasSocialLinkOptions;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Registers a form for uploading linked social media icons.
 */
class UtexasSocialLinksDataForm extends EntityForm {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs the UTexasAnnouncementIconForm object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file handler.
   */
  public function __construct(RendererInterface $renderer, MessengerInterface $messenger, ?FileSystemInterface $file_system = NULL) {
    $this->renderer = $renderer;
    $this->messenger = $messenger;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('messenger'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $utexas_block_social_links = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $utexas_block_social_links->label(),
      '#description' => $this->t("Label for the social account network."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $utexas_block_social_links->id(),
      '#machine_name' => [
        'exists' => '\Drupal\utexas_block_social_links\Entity\UtexasSocialLinksData::load',
      ],
      '#disabled' => !$utexas_block_social_links->isNew(),
    ];
    $form['icon'] = [
      '#type' => 'file',
      '#title' => $this->t('SVG Icon'),
      '#description' => $this->t('Upload an SVG Icon to set as the default icon for this social account. This upload will replace the active SVG icon if one is already set below.'),
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'svg'],
        'FileSizeLimit' => ['fileLimit' => 25600000],
      ],
    ];

    // Get current icon to render as markup on form if an existing account.
    if ($form['id']['#default_value'] !== NULL) {
      $icons = UTexasSocialLinkOptions::getIcons();
      $account = $form['id']['#default_value'];
      $icon = $icons[$account];
      if ($icon && $icon_contents = file_get_contents($icon)) {
        $icon_markup = Markup::create($icon_contents);
        $form['active_icon'] = [
          '#type' => 'texfield',
          '#markup' => $icon_markup,
          '#prefix' => $this->t('<label>Active SVG Icon</label>'),
        ];
      }
    }
    $form['#attached']['library'][] = 'utexas_block_social_links/form';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for a newly uploaded logo.
    $file = $this->saveFromForm($form['icon'], $form_state, 0);
    if ($file) {
      // Put the temporary file in form_values so we can save it on submit.
      $form_state->setValue('icon', $file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var Drupal\utexas_block_social_links\Entity\UtexasSocialLinksData $utexas_block_social_links */
    $utexas_block_social_links = $this->entity;
    if ($temp_image_file = $form_state->getValue('icon')) {
      // The user is uploading a new SVG.
      $temp_image_data = file_get_contents($temp_image_file->getFileUri());
      $destination = 'public://social_icons/';
      $unmanaged_file = $this->fileSystem->saveData($temp_image_data, $destination . $temp_image_file->getFilename());
      $utexas_block_social_links->set('icon', $unmanaged_file);
      $temp_image_file->delete();
    }
    else {
      // No change has been made to the SVG.
      // Ensure that the existing SVG is maintained.
      $icons = UTexasSocialLinkOptions::getIcons();
      $account = $form_state->getValue('id');
      $icon = $icons[$account];
      $utexas_block_social_links->set('icon', $icon);
    }
    $status = $utexas_block_social_links->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created configuration for %label.', [
          '%label' => $utexas_block_social_links->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved configuration for %label.', [
          '%label' => $utexas_block_social_links->label(),
        ]));
    }
    $form_state->setRedirectUrl($utexas_block_social_links->toUrl('collection'));
    Cache::invalidateTags(['utexas_social_links_block']);
  }

  /**
   * Saves form file uploads.
   *
   * The files will be added to the {file_managed} table as temporary files.
   * Temporary files are periodically cleaned. Use the 'file.usage' service to
   * register the usage of the file which will automatically mark
   * it as permanent.
   *
   * @param array $element
   *   The FAPI element whose values are being saved.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param null|int $delta
   *   (optional) The delta of the file to return the file entity.
   *   Defaults to NULL.
   * @param int $replace
   *   (optional) The replace behavior when the destination file already exists.
   *   Possible values include:
   *   - EXISTS_REPLACE: Replace the existing file.
   *   - EXISTS_RENAME: (default) Append _{incrementing number} until the
   *     filename is unique.
   *   - EXISTS_ERROR: Do nothing and return FALSE.
   *
   * @return array|\Drupal\file\FileInterface|null|false
   *   An array of file entities or a single file entity if $delta != NULL. Each
   *   array element contains the file entity if the upload succeeded or FALSE
   *   if there was an error. Function returns NULL if no file was uploaded.
   *
   * @internal
   *   This function wraps file_save_upload() to allow correct error handling in
   *   forms.
   */
  private function saveFromForm(array $element, FormStateInterface $form_state, $delta = NULL, $replace = FileExists::Rename) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    // phpcs:ignore
    $file_system = \Drupal::service('file_system');
    $filedir = 'public://social_icons/';
    $file_system->prepareDirectory($filedir, FileSystemInterface::CREATE_DIRECTORY);
    // Get all errors set before calling this method. This will also clear them
    // from $_SESSION.
    $errors_before = $this->messenger()->deleteByType(MessengerInterface::TYPE_ERROR);

    $upload_location = array_key_exists('#upload_location', $element) ? $element['#upload_location'] : $filedir;
    $upload_name = implode('_', $element['#parents']);
    $upload_validators = is_array($element['#upload_validators']) ? $element['#upload_validators'] : [];

    $result = file_save_upload($upload_name, $upload_validators, $upload_location, $delta, $replace);

    // Get new errors that are generated while trying to save the upload. This
    // will also clear them from $_SESSION.
    $errors_new = $this->messenger()->deleteByType(MessengerInterface::TYPE_ERROR);
    if (!empty($errors_new)) {

      if (count($errors_new) > 1) {
        // Render multiple errors into a single message.
        // This is needed because only one error per element is supported.
        $render_array = [
          'error' => [
            '#markup' => $this->t('One or more files could not be uploaded.'),
          ],
          'item_list' => [
            '#theme' => 'item_list',
            '#items' => $errors_new,
          ],
        ];
        $error_message = $this->renderer->renderInIsolation($render_array);
      }
      else {
        $error_message = reset($errors_new);
      }

      $form_state->setError($element, $error_message);
    }

    // Ensure that errors set prior to calling this method are still shown to
    // the user.
    if (!empty($errors_before)) {
      foreach ($errors_before as $error) {
        $this->messenger()->addError($error);
      }
    }

    return $result;
  }

}
