<?php

/**
 *
 * @see XenForo_Deferred_User
 */
class ThemeHouse_FuturePerm_Extend_XenForo_Deferred_User extends XFCP_ThemeHouse_FuturePerm_Extend_XenForo_Deferred_User
{

    /**
     *
     * @see XenForo_Deferred_User
     */
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $GLOBALS['XenForo_Deferred_User'] = $this;
        
        return parent::execute($deferred, $data, $targetRunTime, $status);
    }
}