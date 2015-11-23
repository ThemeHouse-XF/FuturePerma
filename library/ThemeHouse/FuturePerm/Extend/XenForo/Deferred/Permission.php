<?php

/**
 *
 * @see XenForo_Deferred_Permission
 */
class ThemeHouse_FuturePerm_Extend_XenForo_Deferred_Permission extends XFCP_ThemeHouse_FuturePerm_Extend_XenForo_Deferred_Permission
{

    /**
     *
     * @see XenForo_Deferred_Permission
     */
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $return = parent::execute($deferred, $data, $targetRunTime, $status);
        
        if ($return === false) {
            XenForo_Application::defer('ThemeHouse_FuturePerm_Deferred_FuturePermission', array(), 'FuturePermission',
                true);
        }
        
        return $return;
    }
}