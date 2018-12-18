<?php

namespace drupal_ex\service_ex;

class serviceEx {

  public static function sgf_track_insert($node, $action_type = 'add') {
    if ($node->bundle() == 'company') {
      $account = \Drupal::currentUser();
      $full_data = [];
      $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'company');
      foreach ($fields as $field_name => $field_definition) {
        if (!empty($field_definition->getTargetBundle())) {
          $fd = $field_definition->toArray();
          if ($fd['field_type'] != 'entity_reference_revisions') {
            if (isset($fd['settings']['handler'])) {
              foreach ($node->get($field_name)->getValue() as $field_name_value) {
                if ($fd['settings']['handler'] == "default:taxonomy_term")
                  $full_data[$field_name][] = [
                      'label' => $fd['label'],
                      'value' => serviceEx::termbyId($field_name_value['target_id'])
                  ];
                else if ($fd['settings']['handler'] == "default:node")
                  $full_data[$field_name] = [
                      'label' => $fd['label'],
                      'value' => serviceEx::nodebyId($field_name_value['target_id'])
                  ];
                else if ($fd['settings']['handler'] == "default:file")
                  $full_data[$field_name] = [
                      'label' => $fd['label'],
                      'value' => $node->get($field_name)->getValue()
                  ];
              }
            } else {
              $toArray = $node->get($field_name)->getFieldDefinition()->toArray();
              $full_data[$field_name] = [
                  'label' => $toArray['label'],
                  'value' => $node->get($field_name)->getValue()
              ];
            }
          } else {
            $count = 0;
            $service_paragraphs_arr = [];
            foreach ($node->get($field_name) as $para) {
              $para_label = $para->getFieldDefinition()->toArray();
              $para_label = $para_label['label'];
              $my_paragraphs = $para->entity;
              $temps = $my_paragraphs->toArray();
              foreach ($temps as $k => $temp) {
                if (strpos($k, 'field_') === false) {
                  
                } else {
                  if (strpos($k, '_field_') === false) {
                    $check_entity_types = $my_paragraphs->get($k)->getFieldDefinition()->toArray();
                    $para_details = $my_paragraphs->get($k)->getValue();
                    foreach ($para_details as $para_detail) {
                      if (isset($para_detail['value'])) {
                        $service_paragraphs_arr[$count][$k] = [
                            'label' => $check_entity_types['label'],
                            'value' => $para_detail['value']
                        ];
                      } else if (isset($para_detail['target_id'])) {
                        if (isset($para_detail['target_revision_id']))
                          $service_paragraphs_arr[$count][$k] = [
                              'label' => $check_entity_types['label'],
                              'value' => serviceEx::paragraphbyId($para_detail['target_id'], true)
                          ];
                        else {
                          if (isset($check_entity_types['settings']['handler'])) {
                            if ($check_entity_types['settings']['handler'] == "default:taxonomy_term")
                              $service_paragraphs_arr[$count][$k] = [
                                  'label' => $check_entity_types['label'],
                                  'value' => serviceEx::termbyId($para_detail['target_id'])
                              ];
                            else if ($check_entity_types['settings']['handler'] == "default:node")
                              $service_paragraphs_arr[$count][$k] = [
                                  'label' => $check_entity_types['label'],
                                  'value' => serviceEx::nodebyId($para_detail['target_id'])
                              ];
                          }
                        }
                      }
                    }
                  }
                }
                $service_paragraphs_arr[$count]['id'] = $my_paragraphs->id();
                $service_paragraphs_arr[$count]['label'] = $para_label;
              }
              $count++;
            }
            $full_data[$field_name] = $service_paragraphs_arr;
          }
        }
      }

      $log = array(
          'created' => REQUEST_TIME,
          'uid' => $account->id(),
          'ip' => \Drupal::request()->getClientIp(),
          'path' => Url::fromRoute('<current>')->getInternalPath(),
          'type' => 'node',
          'operation' => $action_type,
          'description' => t('%type: %title', array(
              '%type' => $node->getType(),
              '%title' => $node->getTitle(),
          )),
          'ref_numeric' => $node->id(),
          'entity_id' => $node->id(),
          'ref_char' => $node->getTitle(),
          'full_data' => json_encode($full_data)
      );

      \Drupal::database()->insert('data_log_sgf')
              ->fields($log)
              ->execute();
    }
  }

  public static function listOldData($nid) {
    $page = 0;
    $item_per_page = 50;

    $db_select = db_select('data_log_sgf', 'd')
            ->fields('d', ['ref_char', 'lid', 'operation', 'uid', 'ip', 'entity_id', 'created'])
            ->condition('entity_id', $nid)
            ->range($page * $item_per_page, $item_per_page)
            ->orderBy('lid', 'DESC')
            ->execute()
            ->fetchAll();
    return $db_select;
  }

  public static function showDetailLids($nid, $lids, $token) {
    $lids = explode('-', $lids);
    $db_selects = db_select('data_log_sgf', 'd')
            ->fields('d', ['lid', 'entity_id', 'full_data', 'created'])
            ->condition('entity_id', $nid)
            ->condition('lid', $lids, 'IN')
            ->orderBy('lid', 'DESC')
            ->execute()
            ->fetchAll();
    $full_datas = [];
    foreach ($db_selects as $db_select) {
      $details = [];
      foreach ($db_select as $key => $value) {
        if ($key != 'full_data')
          $details[$key] = $value;
        else {
          $details[$key] = json_decode($value, true);
        }//        
      }
      $full_datas[$db_select->lid] = $details;
    }
    return $full_datas;
  }

  public static function arrayFlatten($array) {

    $result = array();
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $result = array_merge($result, serviceEx::arrayFlatten($value));
      } else {
        $result[$key] = $value;
      }
    }
    return $result;
  }

  public static function paragraphbyId($pid, $is_parent = false) {
    $paragraphs = \Drupal::entityTypeManager()->getStorage('paragraph')->load($pid);
    if (is_null($paragraphs))
      return [];
    $temps = $paragraphs->toArray();
    $service_paragraphs_arr = [];
    foreach ($temps as $k => $temp) {
      if (strpos($k, 'field_') === false) {
        
      } else {
        if (strpos($k, '_field_') === false) {
          if ($is_parent) {
            $parent_arr = $paragraphs->get($k)->getFieldDefinition()->toArray();
            $service_paragraphs_arr[$k][] = [
                'label' => $parent_arr['label'],
                'value' => $paragraphs->get($k)->value
            ];
          } else
            $service_paragraphs_arr[$k] = $paragraphs->get($k)->value;
        }
      }
    }
    return $service_paragraphs_arr;
  }

  public static function termbyId($tid = 0) {
    if ($tid == 0 || empty($tid))
      return 'Not Set';
    if (isset($tid[0]['target_id']))
      $tid = $tid[0]['target_id'];
    $term = Term::load($tid);
    if ($term->getName())
      return $term->getName();
    return '';
  }

  public static function nodebyId($nid) {
    $db_select = db_select('node_field_data', 'n')
            ->fields('n', ['title'])
            ->condition('nid', $nid)
            ->execute()
            ->fetchField();
    return $db_select;
  }
}

?>
