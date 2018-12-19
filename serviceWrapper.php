<?php

namespace drupal_ex\service_ex;

use drupal_ex\service_ex\serviceEx;

class serviceWrapperEx {

  public static function lastestCompanyData($cid) {
    $db_select = db_select('data_log_sgf', 'd')
            ->fields('d', ['full_data'])
            ->condition('entity_id', $cid)
            ->range(0, 1)
            ->orderBy('lid', 'DESC')
            ->execute()
            ->fetchField();
    return $db_select;
  }

  public static function companyFull($cid = 0) {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($cid);
    $node_arr = [];
    $node_arr = [
        'id' => $node->id(),
        'title' => $node->get('title')->value,
        'body' => $node->get('body')->value,
        'field_address_line_1' => $node->get('field_address_line_1')->value,
        'field_address_line_2' => $node->get('field_address_line_2')->value,
        'field_alternative_company_name' => $node->get('field_alternative_company_name')->value,
        'field_alternative_company_type' => serviceEx::termbyId($node->get('field_alternative_company_type')->getValue()),
        'field_application_id' => $node->get('field_application_id')->value,
        'field_company_type' => serviceEx::termbyId($node->get('field_company_type')->getValue()),
        'field_financial_year_end' => $node->get('field_financial_year_end')->value,
        'field_no_director' => $node->get('field_no_director')->value,
        'field_postcode' => $node->get('field_postcode')->value,
        'field_company_status' => serviceEx::termbyId($node->get('field_company_status')->getValue())
    ];
    foreach ($node->get('field_company_ssic')->getValue() as $field_company_ssic) {
      $node_arr['field_company_ssic'][$field_company_ssic['target_id']] = serviceEx::termbyId($field_company_ssic['target_id']);
    }
    foreach ($node->get('field_capital')->getValue() as $field_capital) {
      $paragraphs_temp = \Drupal::entityTypeManager()->getStorage('paragraph')->load($field_capital['target_id']);
      $field_premium_paid_or_payable = $paragraphs_temp->get('field_premium_paid_or_payable')->getValue();
      $field_paid_or_due_on_each = $paragraphs_temp->get('field_paid_or_due_on_each')->getValue();
      $field_nominal_amount_each_share = $paragraphs_temp->get('field_nominal_amount_each_share')->getValue();
      $field_number_of_shares = $paragraphs_temp->get('field_number_of_shares')->getValue();
      $field_share_paid_due_and_payable = $paragraphs_temp->get('field_share_paid_due_and_payable')->getValue();
      $node_arr['field_capital'][$field_capital['target_id']] = [
          'field_premium_paid_or_payable' => serviceEx::paragraphbyId($field_premium_paid_or_payable[0]['target_id']),
          'field_paid_or_due_on_each' => serviceEx::paragraphbyId($field_paid_or_due_on_each[0]['target_id']),
          'field_nominal_amount_each_share' => serviceEx::paragraphbyId($field_nominal_amount_each_share[0]['target_id']),
          'field_number_of_shares' => serviceEx::paragraphbyId($field_number_of_shares[0]['target_id']),
          'field_share_paid_due_and_payable' => serviceEx::paragraphbyId($field_share_paid_due_and_payable[0]['target_id'])
      ];
    }
    foreach ($node->get('field_package_paragraphs')->getValue() as $field_package_paragraphs) {
      $paragraphs_temp = \Drupal::entityTypeManager()->getStorage('paragraph')->load($field_package_paragraphs['target_id']);
      $field_package_list = $paragraphs_temp->get('field_package_list')->getValue();
      $node_arr['field_package_paragraphs'][$field_package_paragraphs['target_id']] = [
          'field_package_date' => $paragraphs_temp->get('field_package_date')->value,
          'field_package_application_status' => serviceEx::termbyId($paragraphs_temp->get('field_package_application_status')->getValue()),
          'field_package_list' => serviceEx::nodebyId($field_package_list[0]['target_id']),
          'field_package_status' => serviceEx::termbyId($paragraphs_temp->get('field_package_status')->getValue())
      ];
    }
    foreach ($node->get('field_secratary')->getValue() as $field_secratary) {
      $paragraphs_temp = \Drupal::entityTypeManager()->getStorage('paragraph')->load($field_secratary['target_id']);
      $node_arr['field_secratary'][$field_secratary['target_id']] = serviceEx::paragraphbyId($field_secratary['target_id']);
      $node_arr['field_secratary'][$field_secratary['target_id']]['field_country_of_birth'] = serviceEx::termbyId($paragraphs_temp->get('field_country_of_birth')->getValue());
      $node_arr['field_secratary'][$field_secratary['target_id']]['field_gender'] = serviceEx::termbyId($paragraphs_temp->get('field_gender')->getValue());
      $node_arr['field_secratary'][$field_secratary['target_id']]['field_id_type'] = serviceEx::termbyId($paragraphs_temp->get('field_id_type')->getValue());
      $node_arr['field_secratary'][$field_secratary['target_id']]['field_nationality'] = serviceEx::termbyId($paragraphs_temp->get('field_nationality')->getValue());
      $node_arr['field_secratary'][$field_secratary['target_id']]['field_position'] = serviceEx::termbyId($paragraphs_temp->get('field_position')->getValue());
    }
    foreach ($node->get('field_director_shareholder')->getValue() as $field_director_shareholder) {
      $paragraphs_temp = \Drupal::entityTypeManager()->getStorage('paragraph')->load($field_director_shareholder['target_id']);
      $node_arr['field_director_shareholder'][$field_director_shareholder['target_id']] = serviceEx::paragraphbyId($field_director_shareholder['target_id']);
      $node_arr['field_director_shareholder'][$field_director_shareholder['target_id']]['field_country_of_birth'] = serviceEx::termbyId($paragraphs_temp->get('field_country_of_birth')->getValue());
      $node_arr['field_director_shareholder'][$field_director_shareholder['target_id']]['field_gender'] = serviceEx::termbyId($paragraphs_temp->get('field_gender')->getValue());
      $node_arr['field_director_shareholder'][$field_director_shareholder['target_id']]['field_id_type'] = serviceEx::termbyId($paragraphs_temp->get('field_id_type')->getValue());
      $node_arr['field_director_shareholder'][$field_director_shareholder['target_id']]['field_nationality'] = serviceEx::termbyId($paragraphs_temp->get('field_nationality')->getValue());
      $node_arr['field_director_shareholder'][$field_director_shareholder['target_id']]['field_position'] = serviceEx::termbyId($paragraphs_temp->get('field_position')->getValue());
    }
    foreach ($node->get('field_service_paragraphs')->getValue() as $field_service_paragraphs) {
      $paragraphs_temp = \Drupal::entityTypeManager()->getStorage('paragraph')->load($field_service_paragraphs['target_id']);
      $field_company_additional_service = $paragraphs_temp->get('field_company_additional_service')->getValue();
      $field_service_date = $paragraphs_temp->get('field_service_date')->value;
      $node_arr['field_service_paragraphs'][$field_service_paragraphs['target_id']] = [
          'field_application_status' => serviceEx::termbyId($paragraphs_temp->get('field_application_status')->getValue()),
          'field_company_additional_service' => TrackingWrapper::nodebyId($field_company_additional_service['0']['target_id']),
          'field_service_date' => $field_service_date,
          'field_service_status' => serviceEx::termbyId($paragraphs_temp->get('field_service_status')->getValue())
      ];
    }
    foreach ($node->get('field_company_document')->getValue() as $field_company_document) {
      $file_storage = \Drupal::entityTypeManager()->getStorage('file');
      $file = $file_storage->load($field_company_document['target_id']);
      $node_arr['field_company_document'][$field_company_document['target_id']] = [
          'filename' => $file->filename->value,
          'url' => file_url_transform_relative(file_create_url($file->getFileUri())),
          'filemime' => $file->filemime->value
      ];
    }
    return $node_arr;
  }

}
