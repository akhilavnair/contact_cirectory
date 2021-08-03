<?php
namespace Drupal\contact_directory\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\contact_directory\Controller\MainController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Provides a form for contacts saving.
 *
 */
class ContactDirectoryForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contacts_directory';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
  */
  public function buildForm(array $form, FormStateInterface $form_state, $id =0) {
    $main_obj = new MainController();
    $contact_details = [
      'cdfname' => NULL,
      'cdlname' => NULL,
      'cdemail' => NULL,
      'cdphone' => NULL,
      'cdaddress' => NULL,
      'cdcomments' => NULL,
      'contact_id' => NULL,
    ];
    if ($id != 0) {
      $conditions  = ['field' => 'cdid', 'value' => $id, 'opt' => '='];
      $results = $main_obj->fetchContacts($conditions);
      $contact_details = $results->fetchAssoc();
    }
    if ($id != 0 && empty($contact_details)) {
      \Drupal::messenger()->addWarning(t('Record not found for update....!'));
    }
    $form['#cache']['max-age'] = 0;
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => 'First Name' ,
      '#required' => TRUE,
      '#default_value' => $contact_details['cdfname'],
    ];
    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => 'Last Name',
      '#required' => TRUE,
      '#default_value' => $contact_details['cdlname'],
    ];
    $form['client_email'] = [
      '#type' => 'email',
      '#title' => 'Email ID',
      '#required' => TRUE,
      '#default_value' => $contact_details['cdemail'],
    ];
    $form['client_phone'] = [
      '#type' => 'tel',
      '#title' => 'Contact Number',
      '#maxlength' => 10,
      '#required' => TRUE,
      '#default_value' => $contact_details['cdphone'],
    ];
    $form['client_address'] = [
      '#type' => 'textarea',
      '#title' => 'Address',
      '#maxlength' => 255,
      '#resizable' => 'none',
      '#default_value' => $contact_details['cdaddress'],
    ];
    $form['notes_comments'] = [
      '#type' => 'textarea',
      '#title' => 'Comments/Notes',
      '#maxlength' => 255,
      '#resizable' => 'none',
      '#default_value' => $contact_details['cdcomments'],
    ];
    $form['contact_id'] = [
      '#type' => 'hidden',
      '#value' => $id,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => ((($id !== 0) ? 'Edit' : 'Save') .' Contacts'),
      '#button_type' => 'primary',
    ];

    return $form;
  }



  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('client_phone'))) {
      $form_state->setErrorByName("Invalid Phone number");
    }
  }


  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = array(
      'first_name' => $form_state->getValue('first_name'),
      'last_name' => $form_state->getValue('last_name'),
      'client_email' => $form_state->getValue('client_email'),
      'client_phone' => $form_state->getValue('client_phone'),
      'client_address' => $form_state->getValue('client_address'),
      'notes_comments' => $form_state->getValue('notes_comments'),
    );
    $main_obj = new MainController();
    $main_obj->saveOrUpdateContact($values, $form_state->getValue('contact_id'));
    $message = t('Information saved successfully.' );
    $response = new RedirectResponse(Url::fromRoute('contact_directory.list')->toString());
    \Drupal::messenger()->addStatus($message);
     return $response->send();
  }
}
