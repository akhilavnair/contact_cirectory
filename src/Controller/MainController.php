<?php
namespace Drupal\contact_directory\Controller;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller class for display the contacts saved.
 */
class MainController {

  /**
   * Page callback to fetch the contacts saved.
   *
   * @param array $condition
   *   An associative array containing the field, value and condition.
   *
   * @return a query execution.
   */
public function fetchContacts($condition = []) {
  $query = \Drupal::database()->select('contact_directory', 'cd');
  $query->fields('cd', ['cdid', 'cdfname', 'cdlname', 'cdemail', 'cdphone','cdaddress', 'cdcomments', 'cdstatus'])
  ->condition($condition['field'], $condition['value'], $condition['opt'])
  ->orderBy('cd.cdid', 'DESC');
  return $query->execute();
}

  /**
   * Page callback to update the contacts status.
   *
   * @param array $condition
   *   An associative array containing the field, value and condition.
   *
   * @return response.
   */
  public function updateStatus() {
    $status = \Drupal::request()->query->get('status');
    $status = ((!empty($status) && $status == 1) ? 'Active' : 'Inactive');
    $id = \Drupal::request()->query->get('id');
    $query = \Drupal::database()->update('contact_directory')
    ->fields(['cdstatus' => $status,])
    ->condition('cdid', $id)
    ->execute();
    $message = t('Contact status updated as : ' . $status);
    \Drupal::messenger()->addStatus($message);
    if ($status == 'Active' ) {
      $response = new RedirectResponse(Url::fromRoute('contact_directory.list')->toString());
    }
    else {
      $response = new RedirectResponse(Url::fromRoute('contact_directory.list_inactive')->toString());
    }
    return $response;
  }


  /**
   * Page callback to delete the contacts saved.
   *
   * @param array $condition
   *   An associative array containing the field, value and condition.
   *
   * @return a response.
   */
  public function removeContact() {
    $id = \Drupal::request()->query->get('id');
    $query = \Drupal::database()->delete('contact_directory')
    ->condition('cdid', $id)
    ->execute();
    $response = new RedirectResponse(Url::fromRoute('contact_directory.list_inactive')->toString());
    $message = t('One contact removed.');
    \Drupal::messenger()->addStatus($message);
    return $response;
  }

  /**
   * Page callback to save or update the contacts saved.
   *
   * @param array $values
   *   An associative array containing the saveing field.
   *
   * @param array $condition
   *   An associative array containing the condition id.
   *
   * @return a response.
   */
  public function saveOrUpdateContact($values, $condition = 0) {
    $database = \Drupal::database();
    if ($condition !== 0) {
      $query = $database->update('contact_directory');
      $query->condition('cdid', $condition);
    }
    else {
      $query = $database->insert('contact_directory');
    }
    $query->fields([
      'cdfname' => $values['first_name'],
      'cdlname' => $values['last_name'],
      'cdemail' => $values['client_email'],
      'cdphone' => $values['client_phone'],
      'cdaddress' => $values['client_address'],
      'cdcomments' => $values['notes_comments'],
    ]);
    return $query->execute();
  }

}
