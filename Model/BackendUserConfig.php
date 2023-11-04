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
    public const fields_ID = 'user_id';
    public const fields_value = 'value';
    public const fields_key = 'key';
    public const fields_module = 'module';
    public const fields_name = 'name';

    private $config = [];
    private $defaul_tconfig = [];

    public array $_index_sort_keys = [self::fields_ID, self::fields_key];

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
                ->addColumn(self::fields_ID, Table::column_type_INTEGER, null, 'default 0', '管理员ID:0表示默认全局配置')
                ->addColumn(self::fields_key, Table::column_type_VARCHAR, 255, 'not null', '配置key')
                ->addColumn(self::fields_value, Table::column_type_TEXT, 0, '', '配置信息')
                ->addColumn(self::fields_module, Table::column_type_VARCHAR, 255, 'not null', '模组')
                ->addColumn(self::fields_name, Table::column_type_VARCHAR, 255, 'not null', '配置名')
                # 建立联合索引
                ->addAdditional(
                    'PRIMARY KEY (`' . self::fields_ID . '`,`' . self::fields_key . '`) USING BTREE'
                )
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
            return $this->clear()->where(self::fields_ID, $userSession->getLoginUserID())
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
            ->where(self::fields_ID, $userSession->getLoginUserID())
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
                ->where(self::fields_ID, 0)
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
    public function setConfig(string $key, string $value, string $module, string $name): bool
    {
        if (CLI) {
            return $this->setDefaultConfig($key, $value, $module, $name);
        }
        # 检测模组
        $moduleInfo = Env::getInstance()->getModuleInfo($module);
        if (!$moduleInfo) {
            if (DEV) {
                throw new \Exception('找不到模组' . $module);
            }
            return false;
        }
        # 设置用户配置
        /**@var BackendSession $userSession */
        $userSession = ObjectManager::getInstance(BackendSession::class);
        return $this->clear()
            ->setData(self::fields_key, $key)
            ->setData(self::fields_value, $value)
            ->setData(self::fields_ID, $userSession->getLoginUserID())
            ->setData(self::fields_module, $module)
            ->setData(self::fields_name, $name)
            ->save(true) ? true : false;
    }

    /**
     * 设置默认配置
     * @param string $key
     * @param string $value
     * @param string $module
     * @return bool
     * @throws \Exception
     */
    public function setDefaultConfig(string $key, string $value, string $module, string $name): bool|int
    {
        # 检测模组
        $moduleInfo = Env::getInstance()->getModuleInfo($module);
        if (!$moduleInfo) {
            if (DEV) {
                throw new \Exception('找不到模组' . $module);
            }
            return false;
        }
        # 设置默认配置
        return $this->clear()
            ->setData(self::fields_key, $key)
            ->setData(self::fields_value, $value)
            ->setData(self::fields_ID, 0)
            ->setData(self::fields_module, $module)
            ->setData(self::fields_name, $name)
            ->save(true) ? true : false;
    }

    public function save(array|bool|AbstractModel $data = [], string|array $sequence = null): bool|int
    {
        $this->forceCheck();
        return parent::save($data, $sequence);
    }
}
