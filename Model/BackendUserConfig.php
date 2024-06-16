<?php

declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 */

namespace Weline\Backend\Model;

use Weline\Backend\Session\BackendSession;
use Weline\Framework\App\Env;
use Weline\Framework\Database\AbstractModel;
use Weline\Framework\Database\Api\Db\TableInterface;
use Weline\Framework\Database\Db\Ddl\Table;
use Weline\Framework\Manager\ObjectManager;
use Weline\Framework\Setup\Data\Context;
use Weline\Framework\Setup\Db\ModelSetup;

class BackendUserConfig extends \Weline\Framework\Database\Model
{
    public const fields_ID = 'backend_user_id';
    public const fields_backend_user_id = 'backend_user_id';
    public const fields_user_id = 'backend_user_id';
    public const fields_value = 'value';
    public const fields_key = 'key';
    public const fields_module = 'module';
    public const fields_name = 'name';

    private $config = [];
    private $defaul_tconfig = [];

    public array $_index_sort_keys = [self::fields_ID, self::fields_key, self::fields_name, self::fields_module];
    public array $_unit_primary_keys = [self::fields_ID, self::fields_key];

    /**
     * @inheritDoc
     */
    public function setup(ModelSetup $setup, Context $context): void
    {
        //        $setup->dropTable();
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
//        $setup->dropTable();
        if (!$setup->tableExist()) {
            $setup->createTable()
                ->addColumn(self::fields_ID, Table::column_type_INTEGER, null, 'PRIMARY KEY auto_increment', '管理员ID')
                ->addColumn(self::fields_key, Table::column_type_VARCHAR, 255, 'not null', '配置key')
                ->addColumn(self::fields_value, Table::column_type_TEXT, 0, '', '配置信息')
                ->addColumn(self::fields_module, Table::column_type_VARCHAR, 255, 'not null', '模组')
                ->addColumn(self::fields_name, Table::column_type_VARCHAR, 255, 'not null', '配置名')
                # 建立联合索引
                ->addAdditional(
                    'PRIMARY KEY (`' . self::fields_ID . '`,`' . self::fields_key . '`) USING BTREE'
                )
                ->addIndex(TableInterface::index_type_KEY, 'idx_module', self::fields_module, '模组索引')
                ->addIndex(TableInterface::index_type_KEY, 'idx_name', self::fields_name, '配置名')
                ->addAdditional('ENGINE=MyIsam;')
                ->create();
        }
    }

    /** 返回配置
     * @param string $key
     * @param bool $real
     * @return string
     */
    public function getConfig(string $key, bool $real = false): string
    {
        if (CLI) {
            return $this->getDefaultConfig($key);
        }
        if ($real) {
            /**@var BackendSession $userSession */
            $userSession = ObjectManager::getInstance(BackendSession::class);
            return $this->clear()->where(self::fields_user_id, $userSession->getLoginUserID())
                ->where(self::fields_key, $key)
                ->find()
                ->fetchOrigin()['value'] ?? '';
        }
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        # 读取用户全部配置
        /**@var BackendSession $userSession */
        $userSession = ObjectManager::getInstance(BackendSession::class);
        $configs = $this->clear()
            ->where(self::fields_user_id, $userSession->getLoginUserID())
            ->select()
            ->fetchOrigin();
        foreach ($configs as $config) {
            $this->config[$config['key']] = $config['value'];
        }
        return $this->config[$key] ?? '';
    }

    public function getDefaultConfig(string $key): string
    {
        if (isset($this->defaul_tconfig[$key])) {
            return $this->defaul_tconfig[$key];
        }
        # 读取默认配置
        try {
            $configs = $this->clear()
                ->where(self::fields_user_id, 0)
                ->select()
                ->fetchOrigin();
        } catch (\Throwable $e) {
            $configs = [];
        }
        foreach ($configs as $config) {
            $this->defaul_tconfig[$config['key']] = $config['value'];
        }
        return $this->defaul_tconfig[$key] ?? '';
    }

    /**
     * 设置用户配置
     * @param string $key
     * @param string $value
     * @param string $module
     * @throws \Exception
     */
    public function setConfig(string $key, string $value, string $module, string $name, $check = true): bool
    {
        if (CLI) {
            return $this->setDefaultConfig($key, $value, $module, $name);
        }
        if ($check) {
            # 检测模组
            $moduleInfo = Env::getInstance()->getModuleInfo($module);
            if (!$moduleInfo) {
                if (DEV) {
                    throw new \Exception('找不到模组' . $module);
                }
                return false;
            }
        }

        # 设置用户配置
        /**@var BackendSession $userSession */
        $userSession = ObjectManager::getInstance(BackendSession::class);
        return (bool)$this->clear()
            ->setData(self::fields_key, $key, true)
            ->setData(self::fields_value, $value, true)
            ->setData(self::fields_user_id, $userSession->getLoginUserID(), true)
            ->setData(self::fields_module, $module, true)
            ->setData(self::fields_name, $name, true)
            ->save(true);
    }

    /**
     * 设置默认配置
     * @param string $key
     * @param string $value
     * @param string $module
     * @return bool
     * @throws \Exception
     */
    public function setDefaultConfig(string $key, string $value, string $module, string $name, $check = true): bool|int
    {
        if ($check) {
            # 检测模组
            $moduleInfo = Env::getInstance()->getModuleInfo($module);
            if (!$moduleInfo) {
                if (DEV) {
                    throw new \Exception('找不到模组' . $module);
                }
                return false;
            }
        }
        # 设置默认配置
        return (bool)$this->clear()
            ->setData(self::fields_key, $key, true)
            ->setData(self::fields_value, $value)
            ->setData(self::fields_user_id, 0, true)
            ->setData(self::fields_module, $module, true)
            ->setData(self::fields_name, $name, true)
            ->save(true);
    }

    public function save(array|bool|AbstractModel $data = [], string|array $sequence = null): bool|int
    {
        $this->forceCheck();
        return parent::save($data, $sequence);
    }
}
