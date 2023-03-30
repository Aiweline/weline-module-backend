<?php

declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 */

namespace Weline\Backend\Controller\System;

use Weline\Framework\Manager\ObjectManager;

class Config extends \Weline\Framework\App\Controller\BackendController
{
    public function set($key, $value, $type = 'json')
    {
        /**@var \Weline\Backend\Model\Config $config */
        $config = ObjectManager::getInstance(\Weline\Backend\Model\Config::class);
        $config->setConfig($key, $value, 'Weline_Backend');
        $fetchName = 'fetch' . ucfirst($type);
        try {
            return $this->$fetchName($this->success());
        } catch (\Exception $exception) {
            return $this->$fetchName($this->exception($exception));
        }
    }
}
