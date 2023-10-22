<?php

namespace Weline\Backend\Model;

use Weline\Framework\Database\Model;
use Weline\Framework\Setup\Data\Context;
use Weline\Framework\Setup\Db\ModelSetup;

class BackendUserData extends Model
{
    public const fields_ID = 'backend_user_data_id';
    public const fields_BACKEND_USER_ID = 'backend_user_id';
    public const fields_scope = 'scope';
    public const fields_JSON = 'json';

    /**
     * @inheritDoc
     */
    public function setup(ModelSetup $setup, Context $context): void
    {
        $this->install($setup, $context);
    }

    /**
     * @inheritDoc
     */
    public function upgrade(ModelSetup $setup, Context $context): void
    {
        // TODO: Implement upgrade() method.
    }

    /**
     * @inheritDoc
     */
    public function install(ModelSetup $setup, Context $context): void
    {
        # 检查表存在否
//        $setup->dropTable();
        if (!$setup->tableExist()) {
            $setup->createTable()
                ->addColumn(
                    self::fields_ID,
                    'INT',
                    0,
                    'primary key auto_increment',
                    '后台用户数据ID'
                )
                ->addColumn(
                    self::fields_BACKEND_USER_ID,
                    'INT',
                    0,
                    'not null',
                    '后台用户ID'
                )
                ->addColumn(
                    self::fields_scope,
                    'VARCHAR',
                    255,
                    'not null',
                    '作用域'
                )
                ->addColumn(
                    self::fields_JSON,
                    'json',
                    0,
                    "not null default '{}'",
                    'json数据'
                )
                ->create();
        }
    }
}
