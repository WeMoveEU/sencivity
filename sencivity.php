<?php

require_once 'sencivity.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function sencivity_civicrm_config(&$config) {
  _sencivity_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function sencivity_civicrm_xmlMenu(&$files) {
  _sencivity_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function sencivity_civicrm_install() {
  _sencivity_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function sencivity_civicrm_uninstall() {
  _sencivity_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function sencivity_civicrm_enable() {
  _sencivity_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function sencivity_civicrm_disable() {
  _sencivity_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function sencivity_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sencivity_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function sencivity_civicrm_managed(&$entities) {
  _sencivity_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function sencivity_civicrm_caseTypes(&$caseTypes) {
  _sencivity_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function sencivity_civicrm_angularModules(&$angularModules) {
  _sencivity_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function sencivity_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _sencivity_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Publishes a sensu check result reflecting the job execution result.
 * Status OK in case of success, WARNING in case of failure.
 */
function sencivity_civicrm_postJob($job, $params, $result) {
  if ($result['is_error']) {
    $status = 1;
    $output = "Job '$job->name' failed: " . CRM_Utils_Array::value('error_message', $result, 'no error message');
  }
  else {
    $status = 0;
    $output = "Job '$job->name' succeeded with value(s): " . CRM_Utils_Array::value('values', $result, 'no value');
  }

  $sensu_url = Civi::settings()->get('sensu_url');
  $source = Civi::settings()->get('sensu_client');
  $curl = curl_init("$sensu_url/results");
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  $jsonData = json_encode(array(
    'source' => $source,
    'name' => 'civicrm_jobs',
    'output' => $output,
    'status' => $status,
  ));
  curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);

  $response = curl_exec($curl);
  if ($response === FALSE) {
    CRM_Core_Error::createError("Could not push job execution result to Sensu.", 8000, 'Error');
    CRM_Core_Error::debug_var("sensu_response", curl_getinfo($curl));
  }
}