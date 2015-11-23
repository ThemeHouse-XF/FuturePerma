<?php

class ThemeHouse_FuturePerm_Permission
{

    protected static $_permCache = array();

    protected static $_contentPermCache = array();

    /**
     * Private constructor.
     * Use statically.
     */
    private function __construct()
    {
    }

    /**
     * Gets the value (true, false, or int) of the specified permission if it
     * exists.
     * Used for global permissions which have a group and an individual
     * permission.
     *
     * @param array $viewingUser
     * @param int $time Time in the future
     * @param string $group Permission group
     * @param string $permission Permission ID
     *
     * @return true|false|int False if the permission isn't found; the value of
     * the permission otherwise
     */
    public static function hasPermissionInFuture(array $viewingUser, $time, $group, $permission)
    {
        if ($time < XenForo_Application::$time) {
            return true;
        }
        
        if (!empty($viewingUser['future_permission_combination_ids'])) {
            $combinationId = 0;
            
            $futurePermissionCombinationIds = unserialize($viewingUser['future_permission_combination_ids']);
            
            foreach ($futurePermissionCombinationIds as $expiryDate => $expiryCombinationId) {
                if ($expiryDate < $time) {
                    $combinationId = $expiryCombinationId;
                } else {
                    break;
                }
            }
            if ($combinationId) {
                $db = XenForo_Application::get('db');
                
                $permissions = self::getPermCache($combinationId);
                
                return XenForo_Permission::hasPermission($permissions, $group, $permission);
            }
        }
        
        return true;
    }

    /**
     * Gets the value (true, false, or int) of the specified content permission,
     * if it exists.
     * This differs from {@link hasPermission()} in that there is no group
     * specified. The first dimension has the permissions.
     *
     * If the specified permission exists but is an array, an exception will be
     * thrown.
     *
     * @param array $viewingUser
     * @param int $time Time in the future
     * @param string $contentType Content type
     * @param int $contentId Content ID
     * @param string $permission Permission ID
     *
     * @return true|false|int False if the permission isn't found; the value of
     * the permission otherwise
     */
    public static function hasContentPermissionInFuture(array $viewingUser, $time, $contentType, $contentId, $permission)
    {
        if ($time < XenForo_Application::$time) {
            return true;
        }
        
        if (!empty($viewingUser['future_permission_combination_ids'])) {
            $combinationId = 0;
            
            $futurePermissionCombinationIds = unserialize($viewingUser['future_permission_combination_ids']);
            
            foreach ($futurePermissionCombinationIds as $expiryDate => $expiryCombinationId) {
                if ($expiryDate < $time) {
                    $combinationId = $expiryCombinationId;
                } else {
                    break;
                }
            }
            if ($combinationId) {
                $db = XenForo_Application::get('db');
                
                $contentPermissions = self::getContentPermCache($combinationId, $contentType, $contentId);
                
                return XenForo_Permission::hasContentPermission($contentPermissions, $permission);
            }
        }
        
        return true;
    }

    public static function getPermCache($combinationId)
    {
        if (!isset(self::$_permCache[$combinationId])) {
            $permissionCacheModel = XenForo_Model::create('XenForo_Model_PermissionCache');
            
            $db = XenForo_Application::get('db');
            
            self::$_permCache[$combinationId] = XenForo_Permission::unserializePermissions(
                $db->fetchOne(
                    '
                    SELECT cache_value
                    FROM xf_future_permission_combination
                    WHERE permission_combination_id = ?
                ', $combinationId));
        }
        
        return self::$_permCache[$combinationId];
    }

    public static function getContentPermCache($combinationId, $contentType, $contentId)
    {
        if (!isset(self::$_contentPermCache[$combinationId][$contentType][$contentId])) {
            $permissionCacheModel = XenForo_Model::create('XenForo_Model_PermissionCache');
            
            self::$_contentPermCache[$combinationId][$contentType][$contentId] = $permissionCacheModel->getContentFuturePermissionsForItem(
                $combinationId, $contentType, $contentId);
        }
        
        return self::$_contentPermCache[$combinationId][$contentType][$contentId];
    }
}