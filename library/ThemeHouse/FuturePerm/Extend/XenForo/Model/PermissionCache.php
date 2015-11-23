<?php

/**
 *
 * @see XenForo_Model_PermissionCache
 */
class ThemeHouse_FuturePerm_Extend_XenForo_Model_PermissionCache extends XFCP_ThemeHouse_FuturePerm_Extend_XenForo_Model_PermissionCache
{

    /**
     * Gets the content permissions for a specified item.
     *
     * @param integer $permissionCombinationId Permission combination to read
     * @param string $contentType Permission content type
     * @param integer $contentId
     *
     * @return array
     */
    public function getContentFuturePermissionsForItem($permissionCombinationId, $contentType, $contentId)
    {
        return XenForo_Permission::unserializePermissions(
            $this->_getDb()->fetchOne(
                '
			SELECT cache_value
			FROM xf_future_permission_cache_content
			WHERE permission_combination_id = ?
				AND content_type = ?
				AND content_id = ?
		', 
                array(
                    $permissionCombinationId,
                    $contentType,
                    $contentId
                )));
    }
}