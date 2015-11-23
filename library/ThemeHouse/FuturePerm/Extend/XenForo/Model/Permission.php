<?php

/**
 *
 * @see XenForo_Model_Permission
 */
class ThemeHouse_FuturePerm_Extend_XenForo_Model_Permission extends XFCP_ThemeHouse_FuturePerm_Extend_XenForo_Model_Permission
{

    /**
     *
     * @see XenForo_Model_Permission::rebuildPermissionCacheForUserId()
     */
    public function rebuildPermissionCacheForUserId($userId)
    {
        $this->rebuildFuturePermissionCacheForUserId($userId);
        
        return parent::rebuildPermissionCacheForUserId($userId);
    }

    /**
     *
     * @see XenForo_Model_Permission::rebuildPermissionCacheForUserGroup()
     */
    public function rebuildPermissionCacheForUserGroup($userGroupId)
    {
        $this->rebuildFuturePermissionCacheForUserGroup($userGroupId);
        
        return parent::rebuildPermissionCacheForUserGroup($userGroupId);
    }

    /**
     * Gets information about all permission combinations.
     * Note that this function
     * does not return the cached permission data!
     *
     * @return array Format: [] => permission combo info (id, user, user group
     * list)
     */
    public function getAllFuturePermissionCombinations()
    {
        return $this->_getDb()->fetchAll(
            '
			SELECT permission_combination_id, user_id, user_group_list
			FROM xf_future_permission_combination
			ORDER BY permission_combination_id
		');
    }

    /**
     * Gets the specified permission combination, including permission cache.
     *
     * @param integer $combinationId
     *
     * @return false|array Permission combination if, it it exists
     */
    public function getFuturePermissionCombinationById($combinationId)
    {
        if (!$combinationId) {
            return false;
        }
        
        return $this->_getDb()->fetchRow(
            '
			SELECT *
			FROM xf_future_permission_combination
			WHERE permission_combination_id = ?
		', $combinationId);
    }

    /**
     * Gets the permission combination that applies to a user.
     * Returns false if
     * no user ID is specified.
     *
     * @param integer $userId
     *
     * @return false|array Permission combo info
     */
    public function getFuturePermissionCombinationsByUserId($userId)
    {
        if (!$userId) {
            return false;
        }
        
        return $this->_getDb()->fetchAll(
            '
			SELECT *
			FROM xf_future_permission_combination
			WHERE user_id = ?
		', $userId);
    }

    /**
     * Gets all permission combinations that involve the specified user group.
     *
     * @param integer $userGroupId
     *
     * @return array Format: [permission_combination_id] => permission
     * combination info
     */
    public function getFuturePermissionCombinationsByUserGroupId($userGroupId)
    {
        return $this->fetchAllKeyed(
            '
			SELECT combination.permission_combination_id, combination.user_id, combination.user_group_list
			FROM xf_future_permission_combination_user_group AS combination_user_group
			INNER JOIN xf_future_permission_combination AS combination ON
				(combination.permission_combination_id = combination_user_group.permission_combination_id)
			WHERE combination_user_group.user_group_id = ?
		', 'permission_combination_id', $userGroupId);
    }

    /**
     * Gets a permission combination ID based on a specific user role (user ID
     * if there are specific
     * permissions and a list of user group ID).
     *
     * @param integer $userId
     * @param array $userGroupIds
     *
     * @return integer|false Combination ID or false
     */
    public function getFuturePermissionCombinationIdByUserRole($userId, array $userGroupIds)
    {
        $userGroupList = $this->_prepareCombinationUserGroupList($userGroupIds);
        
        return $this->_getDb()->fetchOne(
            '
			SELECT permission_combination_id
			FROM xf_future_permission_combination
			WHERE user_id = ? AND user_group_list = ?
		', array(
                $userId,
                $userGroupList
            ));
    }

    /**
     * Finds an existing permission combination or creates a new one from a user
     * info array.
     *
     * @param array $user User info
     * @param boolean $buildOnCreate Build the permission combo cache if it must
     * be created
     * @param boolean $checkForUserPerms If false, assumes there are no user
     * perms (optimization)
     *
     * @return integer Permission combination ID
     */
    public function findOrCreateFuturePermissionCombinationFromUser(array $user, $buildOnCreate = true, 
        $checkForUserPerms = true)
    {
        $userId = $user['user_id'];
        if ($checkForUserPerms) {
            $userIdForPermissions = ($this->permissionsForUserExist($userId) ? $userId : 0);
        } else {
            $userIdForPermissions = 0;
        }
        
        if (isset($user['secondary_group_ids']) && $user['secondary_group_ids'] != '') {
            $userGroups = explode(',', $user['secondary_group_ids']);
        } else {
            $userGroups = array();
        }
        $userGroups[] = $user['user_group_id'];
        
        return $this->findOrCreateFuturePermissionCombination($userIdForPermissions, $userGroups, $buildOnCreate);
    }

    /**
     * Finds or creates a permission combination using the specified combination
     * parameters.
     * The user ID should only be provided if permissions exist for that user.
     *
     * @param integer $userId User ID, if there are user-specific permissions
     * @param array $userGroupIds List of user group IDs
     * @param boolean $buildOnCreate Build permission combo cache if created
     *
     * @return integer Permission combination ID
     */
    public function findOrCreateFuturePermissionCombination($userId, array $userGroupIds, $buildOnCreate = true)
    {
        $permissionCombinationId = $this->getFuturePermissionCombinationIdByUserRole($userId, $userGroupIds);
        if ($permissionCombinationId) {
            return $permissionCombinationId;
        }
        
        $db = $this->_getDb();
        
        $userGroupList = $this->_prepareCombinationUserGroupList($userGroupIds);
        
        $combination = array(
            'user_id' => $userId,
            'user_group_list' => $userGroupList,
            'cache_value' => ''
        );
        
        $db->insert('xf_future_permission_combination', $combination);
        $combination['permission_combination_id'] = $db->lastInsertId('xf_future_permission_combination', 
            'permission_combination_id');
        
        foreach (explode(',', $userGroupList) as $userGroupId) {
            $db->insert('xf_future_permission_combination_user_group', 
                array(
                    'user_group_id' => $userGroupId,
                    'permission_combination_id' => $combination['permission_combination_id']
                ));
        }
        
        if ($buildOnCreate) {
            $entries = $this->getAllPermissionEntriesGrouped();
            $permissionsGrouped = $this->getAllPermissionsGrouped();
            $this->rebuildFuturePermissionCombination($combination, $permissionsGrouped, $entries);
        }
        
        return $combination['permission_combination_id'];
    }

    /**
     * Rebuilds the permission cache for the specified user ID.
     * A combination with
     * this user ID must exist for a rebuild to be triggered.
     *
     * @param integer $userId
     *
     * @return boolean True on success (false if no cache needs to be updated)
     */
    public function rebuildFuturePermissionCacheForUserId($userId)
    {
        $combinations = $this->getFuturePermissionCombinationsByUserId($userId);
        if (!$combinations) {
            return false;
        }
        
        $entries = $this->getAllPermissionEntriesGrouped();
        $permissionsGrouped = $this->getAllPermissionsGrouped();
        
        foreach ($combinations as $combination) {
            $this->rebuildFuturePermissionCombination($combination, $permissionsGrouped, $entries);
        }
        
        return true;
    }

    /**
     * Rebuilds all permission cache data for combinations that involve the
     * specified
     * user group.
     *
     * @param integer $userGroupId
     *
     * @return boolean True on success
     */
    public function rebuildFuturePermissionCacheForUserGroup($userGroupId)
    {
        $combinations = $this->getFuturePermissionCombinationsByUserGroupId($userGroupId);
        if (!$combinations) {
            return false;
        }
        
        $entries = $this->getAllPermissionEntriesGrouped();
        $permissionsGrouped = $this->getAllPermissionsGrouped();
        
        foreach ($combinations as $combination) {
            $this->rebuildFuturePermissionCombination($combination, $permissionsGrouped, $entries);
        }
        
        return true;
    }

    /**
     * Rebuilds all permission cache entries.
     *
     * @param integer $maxExecution Limit execution time
     * @param integer $startCombinationId If specified, starts the rebuild at
     * the specified combination ID
     *
     * @return boolean|integer True when totally complete; the next combination
     * ID to start with otherwise
     */
    public function rebuildFuturePermissionCache($maxExecution = 0, $startCombinationId = 0)
    {
        $entries = $this->getAllPermissionEntriesGrouped();
        $permissionsGrouped = $this->getAllPermissionsGrouped();
        $combinations = $this->getAllFuturePermissionCombinations();
        
        $startTime = microtime(true);
        $restartCombinationId = false;
        
        foreach ($combinations as $combination) {
            if ($combination['permission_combination_id'] < $startCombinationId) {
                continue;
            }
            
            $this->rebuildFuturePermissionCombination($combination, $permissionsGrouped, $entries);
            
            if ($maxExecution && (microtime(true) - $startTime) > $maxExecution) {
                $restartCombinationId = $combination['permission_combination_id'] + 1; // next
                                                                                       // one
                break;
            }
        }
        
        return ($restartCombinationId ? $restartCombinationId : true);
    }

    /**
     * Rebuilds the specific permission combination.
     *
     * @param integer $combinationId
     *
     * @return array|bool False if combination is not found, global permissions
     * otherwise
     */
    public function rebuildFuturePermissionCombinationById($combinationId)
    {
        $combination = $this->getFuturePermissionCombinationById($combinationId);
        if (!$combination) {
            return false;
        }
        
        $entries = $this->getAllPermissionEntriesGrouped();
        $permissionsGrouped = $this->getAllPermissionsGrouped();
        
        return $this->rebuildFuturePermissionCombination($combination, $permissionsGrouped, $entries);
    }

    /**
     * Rebuilds the specified permission combination and updates the cache.
     *
     * @param array $combination Permission combination info
     * @param array $permissionsGrouped List of valid permissions, grouped
     * @param array $entries List of permission entries, with keys
     * system/users/userGroups
     *
     * @return array Permission cache for this combination.
     */
    public function rebuildFuturePermissionCombination(array $combination, array $permissionsGrouped, array $entries)
    {
        $userGroupIds = explode(',', $combination['user_group_list']);
        $userId = $combination['user_id'];
        
        $groupEntries = array();
        foreach ($userGroupIds as $userGroupId) {
            if (isset($entries['userGroups'][$userGroupId])) {
                $groupEntries[$userGroupId] = $entries['userGroups'][$userGroupId];
            }
        }
        
        if ($userId && isset($entries['users'][$userId])) {
            $userEntries = $entries['users'][$userId];
        } else {
            $userEntries = array();
        }
        
        $db = $this->_getDb();
        
        $combinationIdQuoted = $db->quote($combination['permission_combination_id']);
        
        $permCache = $this->buildPermissionCacheForCombination($permissionsGrouped, $entries['system'], $groupEntries, 
            $userEntries);
        
        $finalCache = $this->canonicalizePermissionCache($permCache);
        
        XenForo_Db::beginTransaction($db);
        
        $db->update('xf_future_permission_combination', 
            array(
                'cache_value' => serialize($finalCache)
            ), 'permission_combination_id = ' . $combinationIdQuoted);
        
        $this->rebuildFutureContentPermissionCombination($combination, $permissionsGrouped, $permCache);
        
        XenForo_Db::commit($db);
        
        return $permCache;
    }

    /**
     * Rebuilds the content permission cache for the specified combination.
     * This
     * function will rebuild permissions for all types of content and all pieces
     * of content for that type.
     *
     * @param array $combination Array of combination information
     * @param array $permissionsGrouped List of permissions, grouped
     * @param array $permCache Global permission cache for this combination,
     * with values of unset, etc. May be modified by ref.
     */
    public function rebuildFutureContentPermissionCombination(array $combination, array $permissionsGrouped, 
        array &$permCache)
    {
        $userGroups = explode(',', $combination['user_group_list']);
        $db = $this->_getDb();
        
        $contentHandlers = $this->getContentPermissionTypeHandlers();
        
        foreach ($contentHandlers as $contentTypeId => $handler) {
            $cacheEntries = $handler->rebuildContentPermissions($this, $userGroups, $combination['user_id'], 
                $permissionsGrouped, $permCache);
            
            if (!is_array($cacheEntries)) {
                continue;
            }
            
            $rows = array();
            $rowLength = 0;
            
            foreach ($cacheEntries as $contentId => $entry) {
                $row = '(' . $db->quote($combination['permission_combination_id']) . ', ' . $db->quote($contentTypeId) .
                     ', ' . $db->quote($contentId) . ', ' . $db->quote(serialize($entry)) . ')';
                
                $rows[] = $row;
                $rowLength += strlen($row);
                
                if ($rowLength > 500000) {
                    $db->query(
                        '
						INSERT INTO xf_future_permission_cache_content
							(permission_combination_id, content_type, content_id, cache_value)
						VALUES
							' . implode(', ', $rows) . '
						ON DUPLICATE KEY UPDATE cache_value = VALUES(cache_value)
					');
                    $rows = array();
                    $rowLength = 0;
                }
            }
            
            if ($rows) {
                $db->query(
                    '
					INSERT INTO xf_future_permission_cache_content
						(permission_combination_id, content_type, content_id, cache_value)
					VALUES
						' . implode(', ', $rows) . '
					ON DUPLICATE KEY UPDATE cache_value = VALUES(cache_value)
				');
            }
        }
    }
}