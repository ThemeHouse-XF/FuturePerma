<?php

class ThemeHouse_FuturePerm_Listener_FileHealthCheck
{

    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes = array_merge($hashes,
            array(
                'library/ThemeHouse/FuturePerm/Deferred/FuturePermission.php' => '5d18fe7bc96b187cb5ba38c9756a5b18',
                'library/ThemeHouse/FuturePerm/Extend/XenForo/DataWriter/User.php' => '6f7b8ae70ae32f09d3576f0c98aedd54',
                'library/ThemeHouse/FuturePerm/Extend/XenForo/Deferred/Permission.php' => '3d0e7ffd910a2633795169b33b0faaa5',
                'library/ThemeHouse/FuturePerm/Extend/XenForo/Deferred/User.php' => 'b52d56321e549d72ba2ac6844efef9ca',
                'library/ThemeHouse/FuturePerm/Extend/XenForo/Model/Permission.php' => 'f562450aa1b20e7031aa8e0e03a6e978',
                'library/ThemeHouse/FuturePerm/Extend/XenForo/Model/PermissionCache.php' => '77fbb19e56de1d92e35b8f5fd26aba01',
                'library/ThemeHouse/FuturePerm/Extend/XenForo/Model/UserUpgrade.php' => '9a8482c0086f0cb215e9cf96bcba35e4',
                'library/ThemeHouse/FuturePerm/Install/Controller.php' => '1103bc95eb202b5a59ad6daed6e2b6f0',
                'library/ThemeHouse/FuturePerm/Listener/LoadClass.php' => 'cc0a9212b25177fdfe10d88ebde65811',
                'library/ThemeHouse/FuturePerm/Permission.php' => 'db7be2c29af26c1dc1fc0c2783362009',
                'library/ThemeHouse/Install.php' => '18f1441e00e3742460174ab197bec0b7',
                'library/ThemeHouse/Install/20151109.php' => '2e3f16d685652ea2fa82ba11b69204f4',
                'library/ThemeHouse/Deferred.php' => 'ebab3e432fe2f42520de0e36f7f45d88',
                'library/ThemeHouse/Deferred/20150106.php' => 'a311d9aa6f9a0412eeba878417ba7ede',
                'library/ThemeHouse/Listener/ControllerPreDispatch.php' => 'fdebb2d5347398d3974a6f27eb11a3cd',
                'library/ThemeHouse/Listener/ControllerPreDispatch/20150911.php' => 'f2aadc0bd188ad127e363f417b4d23a9',
                'library/ThemeHouse/Listener/InitDependencies.php' => '8f59aaa8ffe56231c4aa47cf2c65f2b0',
                'library/ThemeHouse/Listener/InitDependencies/20150212.php' => 'f04c9dc8fa289895c06c1bcba5d27293',
                'library/ThemeHouse/Listener/LoadClass.php' => '5cad77e1862641ddc2dd693b1aa68a50',
                'library/ThemeHouse/Listener/LoadClass/20150518.php' => 'f4d0d30ba5e5dc51cda07141c39939e3',
            ));
    }
}