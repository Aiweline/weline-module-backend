<?php

declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 */

namespace Weline\Backend\Model\Html;

use Weline\Backend\Model\Config;
use Weline\Framework\View\Data\HtmlInterface;
use Weline\Framework\View\Template;

class Header implements HtmlInterface
{
    public const key    = 'header';
    public const module = 'Weline_Backend';
    private Config $backendConfig;
    private string $_html = '';

    public function __construct(
        Config $backendConfig
    ) {
        $this->backendConfig = $backendConfig;
    }

    /**
     * @DESC          # 返回Html头部配置
     *
     * @AUTH    秋枫雁飞
     * @EMAIL aiweline@qq.com
     * @DateTime: 2021/9/14 21:51
     * 参数区：
     * @return mixed
     * @throws \Weline\Framework\Exception\Core
     */
    public function getHtml(): string
    {
        return Template::getInstance()->tmp_replace(($this->backendConfig->getConfig(self::key, self::module) ?? '') . $this->_html);
    }

    /**
     * @DESC          # 设置头部Html代码
     *
     * @AUTH    秋枫雁飞
     * @EMAIL aiweline@qq.com
     * @DateTime: 2021/9/14 21:52
     * 参数区：
     *
     * @param string $html
     *
     * @return Header
     * @throws \Weline\Framework\App\Exception
     */
    public function setHtml(string $html): static
    {
        $this->_html = $html;
        $this->backendConfig->setConfig(self::key, $html, self::module);
        return $this;
    }

    public function addHtml(string $html): static
    {
        $this->_html .= $html;
        return $this;
    }
}
