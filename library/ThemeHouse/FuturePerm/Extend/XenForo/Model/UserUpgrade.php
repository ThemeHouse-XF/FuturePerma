<?php

/**
 *
 * @see XenForo_Model_UserUpgrade
 */
class ThemeHouse_FuturePerm_Extend_XenForo_Model_UserUpgrade extends XFCP_ThemeHouse_FuturePerm_Extend_XenForo_Model_UserUpgrade
{
    
    public function updateActiveUpgradeEndDate($userUpgradeRecordId, $endDate)
    {
        parent::updateActiveUpgradeEndDate($userUpgradeRecordId, $endDate);
        
        $userUpgradeRecordId = $this->getActiveUserUpgradeRecordById($userUpgradeRecordId);
        
        if (!empty($userUpgradeRecordId['user_id'])) {
            $dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
            $dw->setExistingData($userUpgradeRecordId['user_id']);
            $dw->rebuildFuturePermissionCombinationIds();
            $dw->save();
        }
    }
    
    public function upgradeUser($userId, array $upgrade, $allowInsertUnpurchasable = false, $endDate = null)
    {
        $upgradeRecordId = parent::upgradeUser($userId, $upgrade, $allowInsertUnpurchasable, $endDate);
        
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
        $dw->setExistingData($userId);
        $dw->rebuildFuturePermissionCombinationIds();
        $dw->save();
        
        return $upgradeRecordId;
    }
}