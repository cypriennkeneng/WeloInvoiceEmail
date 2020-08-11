<?php
/**
 * Copyright (c) Web Loupe. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

namespace WeloInvoiceEmail;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class WeloInvoiceEmail
 *
 * @author    Cyprien Nkeneng <cyprien.nkeneng@webloupe.de>
 * @copyright Copyright (c) 2017-2020 WEB LOUPE
 * @package   WeloInvoiceEmail
 * @version   1
 */
class WeloInvoiceEmail extends Plugin
{
    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('welo_invoice_email.plugin_dir', $this->getPath());
        $container->setParameter('welo_invoice_email.namespace', $this->getName());
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        // Clear only cache when switching from active state to uninstall
        if ($context->getPlugin()->getActive()) {
            $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        parent::activate($context);
        $context->scheduleMessage('Please configure the plugin and compile the theme.');
    }
}
