<?php

class ThemeHouse_FuturePerm_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{

    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_FuturePerm' => array(
                'datawriter' => array(
                    'XenForo_DataWriter_User'
                ),
                'deferred' => array(
                    'XenForo_Deferred_User',
                    'XenForo_Deferred_Permission'
                ),
                'model' => array(
                    'XenForo_Model_Permission',
                    'XenForo_Model_PermissionCache',
                    'XenForo_Model_UserUpgrade'
                ),
            ),
        );
    }

    public static function loadClassDataWriter($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_FuturePerm_Listener_LoadClass', $class, $extend, 'datawriter');
    }

    public static function loadClassDeferred($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_FuturePerm_Listener_LoadClass', $class, $extend, 'deferred');
    }

    public static function loadClassModel($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_FuturePerm_Listener_LoadClass', $class, $extend, 'model');
    }
}