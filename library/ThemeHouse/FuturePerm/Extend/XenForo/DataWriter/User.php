<?php

/**
 *
 * @see XenForo_DataWriter_User
 */
class ThemeHouse_FuturePerm_Extend_XenForo_DataWriter_User extends XFCP_ThemeHouse_FuturePerm_Extend_XenForo_DataWriter_User
{

    /**
     *
     * @see XenForo_DataWriter_User::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
        
        $fields['xf_user']['future_permission_combination_ids'] = array(
            'type' => self::TYPE_UNKNOWN,
            'default' => ''
        );
        
        return $fields;
    }

    /**
     *
     * @see XenForo_DataWriter_User::_preSave()
     */
    protected function _preSave()
    {
        parent::_preSave();
        
        if (isset($GLOBALS['XenForo_Deferred_User'])) {
            $this->rebuildFuturePermissionCombinationIds();
        }
    }

    public function rebuildFuturePermissionCombinationIds()
    {
        /* @var $userModel XenForo_Model_User */
        $userModel = $this->_getUserModel();
        
        $userGroupChanges = $userModel->getUserGroupChangesForUser($this->get('user_id'));
        
        $omittedGroupIds = array(
            $this->get('user_group_id') => $this->get('user_group_id')
        );
        foreach ($userGroupChanges as $changeKey => $userGroupIds) {
            if (substr($changeKey, 0, strlen('userUpgrade')) == 'userUpgrade') {
                continue;
            }
            foreach (explode(',', $userGroupIds) as $omittedGroupId) {
                $omittedGroupIds[$omittedGroupId] = $omittedGroupId;
            }
        }
        
        /* @var $userUpgradeModel XenForo_Model_UserUpgrade */
        $userUpgradeModel = $this->getModelFromCache('XenForo_Model_UserUpgrade');
        
        $activeUpgrades = $userUpgradeModel->getActiveUserUpgradeRecordsForUser($this->get('user_id'));
        
        $expiringGroupIds = array();
        foreach ($activeUpgrades as $userUpgradeRecordId => $activeUpgrade) {
            if ($activeUpgrade['recurring']) {
                continue;
            }
            if (!$activeUpgrade['end_date']) {
                continue;
            }
            
            $extraGroupIds = explode(',', $activeUpgrade['extra_group_ids']);
            
            foreach ($extraGroupIds as $extraGroupId) {
                if (isset($omittedGroupIds[$extraGroupId])) {
                    continue;
                }
                if (!isset($expiringGroupIds[$extraGroupId]) ||
                     $expiringGroupIds[$extraGroupId] < $activeUpgrade['end_date']) {
                    $expiringGroupIds[$extraGroupId] = $activeUpgrade['end_date'];
                }
            }
        }
        
        $secondaryGroupIds = explode(',', $this->get('secondary_group_ids'));
        
        asort($expiringGroupIds);
                
        /* @var $permissionModel XenForo_Model_Permission */
        $permissionModel = $this->_getPermissionModel();
        
        $expiryDates = array();
        foreach ($expiringGroupIds as $expiringGroupId => $expiryDate) {
            unset($secondaryGroupIds[array_search($expiringGroupId, $secondaryGroupIds)]);
            $expiryDates[$expiryDate] = $secondaryGroupIds;
        }
        
        $futurePermissionCombinationIds = array();
        foreach ($expiryDates as $expiryDate => $secondaryGroupIds) {
            $user = array(
                'user_id' => $this->get('user_id'),
                'user_group_id' => $this->get('user_group_id'),
                'secondary_group_ids' => implode(',', $secondaryGroupIds)
            );
            
            $combinationId = $permissionModel->findOrCreateFuturePermissionCombinationFromUser($user);
            
            $futurePermissionCombinationIds[$expiryDate] = $combinationId;
        }
        
        $this->set('future_permission_combination_ids', serialize($futurePermissionCombinationIds));
    }
}