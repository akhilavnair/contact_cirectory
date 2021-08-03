<?php
namespace Drupal\contact_directory\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\contact_directory\Controller\MainController;

/**
 * Controller class for display the contacts saved.
 */
class ListContacts {

  /**
   * Page callback to display the list of contacts saved.
   *
   * @return an rendered array
   *   $output.
   */
  public function contactList($status = 1) {
    $output ['#markup'] = t("Sorry, you didn't created any contacts.");
    if (\Drupal::currentUser()->hasPermission('contact access content')) {
      $gtstatus = ($status == 1)? 'Active' : 'Inactive';
      $main_obj = new MainController();
      $conditions  = ['field' => 'cdstatus', 'value' => $gtstatus, 'opt' => '='];
      $results = $main_obj->fetchContacts($conditions);
      $header = [
          [
            'data' => t('Sl. No.'),
          ],
          [
            'data' => t('Name'),
            'field' => 'cdfname',
            'sort' => 'asc',
          ],
          [
            'data' => t('Email'),
            'field' => 'cdemail',
          ],
          [
            'data' => t('Phone'),
          ],
          [
            'data' => t('Address'),
          ],
          [
            'data' => t('Comments/Notes'),
          ],
          [
            'data' => t('Status'),
          ],
          [
            'data' => t('Operation'),
          ],
        ];
      $rows = [];
      $key =1;
       $remove_link = "";
      while ($content = $results->fetchAssoc()) {
        if ($content['cdstatus'] == 'Active') {
          $opt_text = t('Inactivate');
          $status = '0';
          $url = Url::fromRoute('contact_directory.form.edit', ['id' => $content['cdid']]);
          $edit_link = Link::fromTextAndUrl(t('Edit'), $url)->toString();
        }
        else {
          $opt_text = t('Activate');
          $status = '1';
          $remove_url = Url::fromRoute('contact_directory.delete', ['id' => $content['cdid'], ]);
          $remove_link = (Link::fromTextAndUrl(t('Remove'), $remove_url))->toString();
        }

        $url = Url::fromRoute('contact_directory.update_status', ['status' => $status, 'id' => $content['cdid'], ]);
        $update_link = Link::fromTextAndUrl($opt_text, $url)->toString();

        if ($content['cdstatus'] == 'Inactive') {
          $update_link = ['#markup' => $update_link . ' / ' . $remove_link];
          $update_link = \Drupal::service('renderer')->render($update_link);
       }
       else {
          $update_link = ['#markup' => $update_link . ' / ' . $edit_link];
          $update_link = \Drupal::service('renderer')->render($update_link);
       }

        $rows[$key] = [
          'data' => [
            'slno' => $key,
            'name' => $content['cdfname'] . " " . $content['cdlname'],
            'email' => $content['cdemail'],
            'phone' => $content['cdphone'],
            'address' => ($content['cdaddress'] ? t($content['cdaddress']) : "-"),
            'omments' => ($content['cdcomments'] ? t($content['cdcomments']) : "-"),
            'status' => $content['cdstatus'],
            'opt' => $update_link,
          ],
        ];
        $key++;
      }
      $table  =[
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
      ];
      $output['#markup'] = \Drupal::service('renderer')->render($table);;
    }

    return $output;
  }




    /**
   * Page callback to display the list of inactive contacts.
   *
   * @return an rendered array
   *   $output.
   */
  public function contactListInactive() {
     return self::contactList(0);
  }
}
