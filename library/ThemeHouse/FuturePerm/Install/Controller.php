<?php

class ThemeHouse_FuturePerm_Install_Controller extends ThemeHouse_Install
{

    protected $_resourceManagerUrl = 'https://xenforo.com/community/resources/future-permissions.4112/';

    protected $_minVersionId = 1020000;
    
    protected $_minVersionString = '1.2.0';
    
    protected function _getTables()
    {
        return array(
            'xf_future_permission_cache_content' => array(
                'permission_combination_id' => 'int UNSIGNED NOT NULL',
                'content_type' => 'varbinary(25) NOT NULL',
                'content_id' => 'int UNSIGNED NOT NULL',
                'cache_value' => 'mediumblob NOT NULL'
            ),
            'xf_future_permission_combination' => array(
                'permission_combination_id' => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'user_id' => 'int UNSIGNED NOT NULL',
                'user_group_list' => 'mediumblob NOT NULL',
                'cache_value' => 'mediumblob NOT NULL'
            ),
            'xf_future_permission_combination_user_group' => array(
                'user_group_id' => 'int UNSIGNED NOT NULL',
                'permission_combination_id' => 'int UNSIGNED NOT NULL'
            )
        );
    }

    protected function _getKeys()
    {
        return array(
            'xf_future_permission_combination' => array(
                'user_id' => array(
                    'user_id'
                )
            ),
            'xf_future_permission_combination_user_group' => array(
                'permission_combination_id' => array(
                    'permission_combination_id'
                )
            )
        );
    }

    protected function _getPrimaryKeys()
    {
        return array(
            'xf_future_permission_combination_user_group' => array(
                'user_group_id',
                'permission_combination_id'
            ),
            'xf_future_permission_cache_content' => array(
                'permission_combination_id',
                'content_type',
                'content_id'
            )
        );
    }

    protected function _getTableChanges()
    {
        return array(
            'xf_user' => array(
                'future_permission_combination_ids' => 'mediumtext'
            )
        );
    }
}