<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 *
 */

/**
 * This class contains the functions that are called using AJAX (jQuery)
 */
class CRM_Group_Page_AJAX {
  /**
   * Get list of groups.
   */
  public static function getGroupList() {
    $params = $_GET;
    if (isset($params['parent_id'])) {
      // requesting child groups for a given parent
      $params['page'] = 1;
      $params['rp'] = 0;
      $groups = CRM_Contact_BAO_Group::getGroupListSelector($params);
    }
    else {
      $requiredParams = array();
      $optionalParams = array(
        'title' => 'String',
        'created_by' => 'String',
        'group_type' => 'String',
        'visibility' => 'String',
        'component_mode' => 'String',
        'status' => 'Integer',
        'parentsOnly' => 'Integer',
        'showOrgInfo' => 'Boolean',
        // Ignore 'parent_id' as that case is handled above
      );
      $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
      $params += CRM_Core_Page_AJAX::validateParams($requiredParams, $optionalParams);

      // get group list
      $groups = CRM_Contact_BAO_Group::getGroupListSelector($params);

      // if no groups found with parent-child hierarchy and logged in user say can view child groups only (an ACL case),
      // go ahead with flat hierarchy, CRM-12225
      if (empty($groups)) {
        $groupsAccessible = CRM_Core_PseudoConstant::group();
        $parentsOnly = CRM_Utils_Array::value('parentsOnly', $params);
        if (!empty($groupsAccessible) && $parentsOnly) {
          // recompute group list with flat hierarchy
          $params['parentsOnly'] = 0;
          $groups = CRM_Contact_BAO_Group::getGroupListSelector($params);
        }
      }
    }

    if (!empty($_GET['is_unit_test'])) {
      return $groups;
    }

    CRM_Utils_JSON::output($groups);
  }

}
