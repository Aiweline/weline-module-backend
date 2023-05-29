<?php

declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 */

namespace Weline\Backend\Model;

use Weline\Acl\Model\Acl;
use Weline\Acl\Model\Role;
use Weline\Acl\Model\RoleAccess;
use Weline\Backend\Cache\BackendCache;
use Weline\Framework\App\Env;
use Weline\Framework\Cache\CacheInterface;
use Weline\Framework\Database\Api\Db\Ddl\TableInterface;
use Weline\Framework\Database\Db\Ddl\Table;
use Weline\Framework\Exception\Core;
use Weline\Framework\Http\Url;
use Weline\Framework\Manager\ObjectManager;
use Weline\Framework\Setup\Data\Context;
use Weline\Framework\Setup\Db\ModelSetup;

class Menu extends \Weline\Framework\Database\Model
{
    public const fields_ID = 'menu_id';

    public array $_unit_primary_keys = ['menu_id', 'source'];
    public array $_index_sort_keys = ['menu_id', 'source'];

    public const fields_NAME          = 'name';
    public const fields_TITLE         = 'title';
    public const fields_PID           = 'pid';
    public const fields_SOURCE        = 'source';
    public const fields_LEVEL         = 'level';
    public const fields_PATH          = 'path';
    public const fields_PARENT_SOURCE = 'parent_source';
    public const fields_ACTION        = 'action';
    public const fields_MODULE        = 'module';
    public const fields_ICON          = 'icon';
    public const fields_ORDER         = 'order';
    public const fields_IS_SYSTEM     = 'is_system';
    public const fields_IS_ENABLE     = 'is_enable';
    public const fields_IS_BACKEND    = 'is_backend';

    private Url $url;

    public function __init()
    {
        parent::__init();
        if (!isset($this->url)) {
            $this->url = ObjectManager::getInstance(Url::class);
        }
    }

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
    }

    /**
     * @inheritDoc
     */
    public function install(ModelSetup $setup, Context $context): void
    {
        $setup->getPrinting()->setup('安装数据表...' . self::table);
//        $setup->dropTable();
        if (!$setup->tableExist()) {
            $setup->createTable('后端菜单表')
                  ->addColumn(self::fields_ID, TableInterface::column_type_INTEGER, null, 'primary key auto_increment', 'ID')
                  ->addColumn(self::fields_NAME, TableInterface::column_type_VARCHAR, 60, 'not null', '菜单名')
                  ->addColumn(self::fields_TITLE, TableInterface::column_type_VARCHAR, 60, 'not null', '菜单标题')
                  ->addColumn(self::fields_PID, TableInterface::column_type_INTEGER, 0, '', '父级ID')
                  ->addColumn(self::fields_SOURCE, TableInterface::column_type_VARCHAR, 255, 'unique', '资源')
                  ->addColumn(self::fields_LEVEL, TableInterface::column_type_INTEGER, 0, 'default 0 ', '层级')
                  ->addColumn(self::fields_PATH, TableInterface::column_type_VARCHAR, 255, '', '路径')
                  ->addColumn(self::fields_PARENT_SOURCE, TableInterface::column_type_VARCHAR, 255, 'not null', '父级资源')
                  ->addColumn(self::fields_ACTION, TableInterface::column_type_VARCHAR, 255, 'not null', '动作URL')
                  ->addColumn(self::fields_MODULE, TableInterface::column_type_VARCHAR, 255, 'not null', '模块')
                  ->addColumn(self::fields_ICON, TableInterface::column_type_VARCHAR, 60, 'not null', 'Icon图标类')
                  ->addColumn(self::fields_ORDER, TableInterface::column_type_INTEGER, null, 'not null', '排序')
                  ->addColumn(self::fields_IS_BACKEND, TableInterface::column_type_INTEGER, 1, 'default 1', '是否后台菜单')
                  ->addColumn(self::fields_IS_SYSTEM, TableInterface::column_type_INTEGER, 1, 'default 0', '是否系统菜单')
                  ->addColumn(self::fields_IS_ENABLE, TableInterface::column_type_INTEGER, 1, 'default 1', '是否启用')
                  ->addIndex(Table::index_type_KEY, 'idx_source', self::fields_SOURCE, '资源唯一')
                  ->addAdditional('ENGINE=MyIsam;')
                  ->create();
        } else {
            $setup->getPrinting()->warning('数据表存在，跳过安装数据表...' . self::table);
        }
    }

    public function getName(): string
    {
        return parent::getData(self::fields_NAME) ?? '';
    }

    public function setName(string $name): static
    {
        return parent::setData(self::fields_NAME, $name);
    }

    public function getLevel(): int
    {
        return parent::getData(self::fields_LEVEL) ?? 0;
    }

    public function setLevel(int $level): static
    {
        return parent::setData(self::fields_LEVEL, $level);
    }

    public function getPath(): string
    {
        return parent::getData(self::fields_PATH) ?? '';
    }

    public function setPath(string $path): static
    {
        return parent::setData(self::fields_PATH, $path);
    }

    public function getPid()
    {
        return parent::getData(self::fields_PID);
    }

    public function setPid(string $pid): static
    {
        return parent::setData(self::fields_NAME, $pid);
    }

    public function getSource(): string
    {
        return parent::getData(self::fields_SOURCE) ?? '';
    }

    public function setSource(string $source): static
    {
        return parent::setData(self::fields_SOURCE, $source);
    }

    public function getParentSource(): string
    {
        return parent::getData(self::fields_PARENT_SOURCE) ?? '';
    }

    public function setParentSource(string $source): static
    {
        return parent::setData(self::fields_PARENT_SOURCE, $source);
    }

    public function getAction(): string
    {
        return parent::getData(self::fields_ACTION) ?? '';
    }

    public function setAction(string $url): static
    {
        return parent::setData(self::fields_ACTION, $url);
    }

    public function getIcon(): string
    {
        return parent::getData(self::fields_ICON) ?? '';
    }

    public function setIcon(string $css_icon_class): static
    {
        return parent::setData(self::fields_ICON, $css_icon_class);
    }

    public function getTitle(): string
    {
        return parent::getData(self::fields_TITLE) ?? '';
    }

    public function setTitle(string $title): static
    {
        return parent::setData(self::fields_ICON, $title);
    }

    public function getOrder(): int
    {
        return intval(parent::getData(self::fields_ORDER));
    }

    public function setOrder(int $order): static
    {
        return parent::setData(self::fields_ORDER, $order);
    }

    public function getModule(): string
    {
        return $this->getData(self::fields_MODULE) ?? '';
    }

    public function setModule(string $module_name): static
    {
        return $this->setData(self::fields_MODULE, $module_name);
    }

    public function isSystem(): bool
    {
        return (bool)$this->getData(self::fields_IS_SYSTEM);
    }

    public function setIsSystem(bool $is_system): static
    {
        return $this->setData(self::fields_IS_SYSTEM, $is_system);
    }

    public function isEnable(): bool
    {
        return (bool)$this->getData(self::fields_IS_ENABLE);
    }

    public function setIsEnable(bool $is_enable): static
    {
        return $this->setData(self::fields_IS_ENABLE, $is_enable);
    }

    public function isBackend(): bool
    {
        return (bool)$this->getData(self::fields_IS_BACKEND);
    }

    public function setIsBackend(bool $is_backend): static
    {
        return $this->setData(self::fields_IS_BACKEND, $is_backend);
    }

    /*----------------------助手函数区-------------------------*/
    public function getUrl(): string
    {
        if (!$this->isBackend()) {
            $url = '/' . trim($this->getAction(), '/');
        } else {
            $url = $this->url->getBackendUrl('/' . trim($this->getAction(), '/'));
        }
        return $url ?? '';
    }

    /**
     * @DESC          # 获取菜单树
     *
     * @AUTH    秋枫雁飞
     * @EMAIL aiweline@qq.com
     * @DateTime: 2022/2/9 0:38
     * 参数区：
     */
    public function getMenuTree(): mixed
    {
        $top_menus = $this->clear()->where($this::fields_PID, 0)->order('order', 'ASC')->select()->fetch()->getItems();
        /**@var \Weline\Backend\Model\Menu $top_menu */
        foreach ($top_menus as &$top_menu) {
            $top_menu = $this->getSubMenus($top_menu);
        }
        return $top_menus;
    }

    public function getSubMenus(\Weline\Backend\Model\Menu &$menu): Menu
    {
        if ($sub_menus = $this->clear()
                              ->where($this::fields_PID, $menu->getData('id'))
                              ->order('order', 'ASC')
                              ->select()
                              ->fetch()
                              ->getItems()
        ) {
            /**@var \Weline\Backend\Model\Menu $sub_menu */
            foreach ($sub_menus as &$sub_menu) {
                $has_sub_menu = $this
                    ->clear()
                    ->where($this::fields_PID, $sub_menu->getData('id'))
                    ->order('order', 'ASC')
                    ->find()
                    ->fetch();
                if ($has_sub_menu->getData('id')) {
                    $sub_menu = $this->getSubMenus($sub_menu);
                }
            }
            $menu = $menu->setData('sub_menu', $sub_menus);
        } else {
            $menu = $menu->setData('sub_menu', []);
        }
        return $menu;
    }

    static private function Acl(): Acl
    {
        return ObjectManager::getInstance(Acl::class);
    }

    /**
     * @DESC          # 获取角色菜单树
     *
     * @AUTH    秋枫雁飞
     * @EMAIL aiweline@qq.com
     * @DateTime: 2022/2/9 0:38
     * 参数区：
     */
    public function getMenuTreeByRole(Role $role): array
    {
        $model = self::Acl();
        if ($role->getId() !== 1) {
            $roleAccessSources = $this->getRoleAccessSources($role);
            // 以子权限扫描所有权限的父级
            $roleAccesses = $model->clear()
                                  ->joinModel(Menu::class, 'menu', 'main_table.source_id=menu.source', 'right')
                                  ->where('menu.source', $roleAccessSources, '=', 'or')
                                  ->select()
                                  ->fetch()
                                  ->getItems();
            // 归并所有相同父级的权限,同时筛选出父级权限资源递归出子权限
            $mergerParentAcl = [];
            $top_menus       = [];
            $checked_menus   = [];
            /**@var Acl[] $roleAccesses */
            foreach ($roleAccesses as $roleAccess) {
                $source = $roleAccess['parent_source'];
                if (empty($source)) {
                    $roleAccess                                = $this->getSubMenusByRole($roleAccess, $role, $roleAccessSources);
                    $top_menus[$roleAccess->getSourceId()]     = $roleAccess;
                    $checked_menus[$roleAccess->getSourceId()] = $roleAccess;
                } else {
                    // 归并需要查找父级的子权限
                    if (!isset($mergerParentAcl[$source])) {
                        /**@var Acl $menu */
                        $menu                                = clone $model->clear()
                                                                           ->joinModel(RoleAccess::class, 'ra', 'ra.source_id=main_table
                                                                           .source_id', 'right')
                                                                           ->joinModel(Menu::class, 'menu', 'ra.source_id=menu.source', 'right')
                                                                           ->where('main_table.source_id', $source)
                                                                           ->order('menu.order', 'asc')
                                                                           ->find()
                                                                           ->fetch();
                        $roleAccess                          = $this->getSubMenusByRole($menu, $role);
                        $checked_menus[$menu->getSourceId()] = $roleAccess;
                        $mergerParentAcl[$source]            = $roleAccess;
                    }
                }
            }
            foreach ($mergerParentAcl as $parentSource => $acls) {
                # 父级可能会相同，相同则合并
                $menu                            = $this->findTopMenu($acls, $role, $checked_menus);
                $top_menus[$menu->getSourceId()] = $menu;
            }
        } else {
            $top_menus = $model->clear()
                               ->joinModel(Menu::class, 'menu', 'main_table.source_id=menu.source')
                               ->where('main_table.parent_source is null or main_table.parent_source=""')
                               ->order('menu.order', 'asc')
                               ->group('main_table.acl_id')
                               ->select()->fetch()
                               ->getItems();
            /**@var Acl $top_menu */
            foreach ($top_menus as &$top_menu) {
                $top_menu = $this->getSubMenusByRole($top_menu, $role);
            }
        }
        return $top_menus;
    }

    /**
     * @DESC          # 查找顶层菜单
     *
     * @AUTH    秋枫雁飞
     * @EMAIL aiweline@qq.com
     * @DateTime: 2023/1/31 23:25
     * 参数区：
     *
     * @param Acl   $acl
     * @param Role  $role
     * @param array $checked_menus
     *
     * @return Acl
     * @throws Core
     * @throws \ReflectionException
     */
    private function findTopMenu(Acl &$acl, Role &$role, array &$checked_menus): Acl
    {
        if ($acl->getParentSource() === '' || $acl->getParentSource() === null) {
            if (isset($checked_menus[$acl->getSourceId()])) {
                /**@var \Weline\Acl\Model\Acl $existAcl */
                $existAcl     = $checked_menus[$acl->getSourceId()];
                $existSubAcls = $existAcl->getSub();
                $sub          = $acl->getSub();
                foreach ($existSubAcls as $existSubAcl) {
                    foreach ($sub as $key => $item) {
                        if ($existSubAcl->getId() === $item->getId()) {
                            unset($sub[$key]);
                        }
                    }
                }
                $acl->setData('sub', array_merge($sub, $existSubAcls));
                $acl->setData('sub_menu_by_role', array_merge($sub, $existSubAcls));
            }
            return $acl;
        } else {
            $parent = clone
            self::Acl()->clear()->joinModel(RoleAccess::class, 'ra', 'ra.source_id=main_table.source_id')
                ->joinModel(Menu::class, 'menu', 'ra.source_id=menu.source')
                ->where('main_table.source_id', $acl->getParentSource())
                ->order('menu.order', 'asc')
                ->find()
                ->fetch();
            # 如果角色没有该父级分类的权限，展示时要保证每级分类都有子分类。否则会造成顶级分类下的子分类没有权限而不展示，但是子分类下确实有权限的问题
            $sub = [$acl];
            if (isset($checked_menus[$parent->getSourceId()])) {
                /**@var \Weline\Acl\Model\Acl $existAcl */
                $existAcl     = $checked_menus[$parent->getSourceId()];
                $existSubAcls = $existAcl->getSub();
                foreach ($existSubAcls as $existSubAcl) {
                    foreach ($sub as $key => $item) {
                        if ($existSubAcl->getId() === $item->getId()) {
                            unset($sub[$key]);
                        }
                    }
                }
                $parent->setData('sub', array_merge($sub, $existSubAcls));
                $parent->setData('sub_menu_by_role', array_merge($sub, $existSubAcls));
            } else {
                $parent->setData('sub', $sub)
                       ->setData('sub_menu_by_role', $sub);
            }
            $checked_menus[$parent->getId()] = $parent;
            return $this->findTopMenu($parent, $role, $checked_menus);
        }
    }

    /**
     * @DESC          # 获取角色权限子菜单
     *
     * @AUTH    秋枫雁飞
     * @EMAIL aiweline@qq.com
     * @DateTime: 2022/2/20 23:18
     * 参数区：
     * @return \Weline\Backend\Model\Menu[]
     */
    public function getSubMenuByRole(): array
    {
        return $this->getData('sub_menu_by_role') ?? [];
    }

    public function getSubMenusByRole(Acl &$acl, Role &$role): Acl
    {
        $model = self::Acl()->clear()
                     ->joinModel(RoleAccess::class, 'ra', 'main_table.source_id=ra.source_id', 'left')
                     ->joinModel(Menu::class, 'menu', 'ra.source_id=menu.source', 'left')
                     ->where('main_table.parent_source', $acl->getId(''))
                     ->group('main_table.source_id');
        if ($role->getId() !== 1) {
            $model->where('ra.role_id', $role->getId(0));
        }
        // 有权限的
        if ($sub_acls = $model->order('menu.order', 'ASC')->select()->fetch()->getItems()) {
            /**@var Acl $sub_acl */
            foreach ($sub_acls as &$sub_acl) {
                $sub_acl = $this->getSubMenusByRole($sub_acl, $role);
            }
            $acl = $acl->setData('sub_menu_by_role', $sub_acls)->setData('sub', $sub_acls);
        } else {
            $acl = $acl->setData('sub_menu_by_role', [])->setData('sub', []);
        }

        return $acl;
    }

    /**
     * @DESC          # 方法描述
     *
     * @AUTH    秋枫雁飞
     * @EMAIL aiweline@qq.com
     * @DateTime: 2023/5/14 11:44
     * 参数区：
     *
     * @param \Weline\Acl\Model\Role $role
     *
     * @return array|mixed
     */
    protected function getRoleAccessSources(Role $role): mixed
    {
        /**@var RoleAccess $roleSourceModel */
        $roleSourceModel   = ObjectManager::getInstance(RoleAccess::class);
        $roleAccess        = $roleSourceModel->where(RoleAccess::fields_ROLE_ID, $role->getId(0))->select()->fetchOrigin();
        $roleAccessSources = [];
        foreach ($roleAccess as $roleAccess) {
            $roleAccessSources[] = $roleAccess[RoleAccess::fields_SOURCE_ID];
        }
        return $roleAccessSources;
    }
}
