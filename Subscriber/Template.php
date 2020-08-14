<?php
/**
 * Copyright (c) Web Loupe. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

namespace WeloInvoiceEmail\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager;
use Enlight_Event_EventArgs as EventArgs;

/**
 * Class Template
 *
 * @author    WEB LOUPE <shopware@webloupe.de>
 * @copyright Copyright (c) 2017-2020 WEB LOUPE
 * @package   WeloInvoiceEmail\Subscriber
 * @version   1
 */
class Template implements SubscriberInterface
{
    /**
     * @var Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @param string                   $pluginName
     * @param                          $pluginDir
     * @param Enlight_Template_Manager $templateManager
     */
    public function __construct(
        $pluginName,
        $pluginDir,
        Enlight_Template_Manager $templateManager
    ) {
        $this->templateManager = $templateManager;
        $this->pluginDir = $pluginDir;
        $this->pluginName = $pluginName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch' => 'onPreDispatch',
            'Theme_Inheritance_Template_Directories_Collected' => [
                'onTemplateDirectoriesCollect',
                1000
            ],
        ];
    }

    public function onPreDispatch()
    {
        $this->templateManager->addTemplateDir($this->pluginDir . '/Resources/views');
    }

    /**
     * @param EventArgs $args
     */
    public function onTemplateDirectoriesCollect(EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->pluginDir . '/Resources/views';
        $args->setReturn($dirs);
    }
}
