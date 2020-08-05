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
use Enlight_Hook_HookArgs;
use ReflectionException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Plugin\Plugin;
use Shopware_Components_Document;
use Smarty_Data;
use WeloInvoiceEmail\Components\Configuration;

/**
 * Class Document
 *
 * @author    Cyprien Nkeneng <cyprien.nkeneng@webloupe.de>
 * @copyright Copyright (c) 2017-2020 WEB LOUPE
 * @package   WeloExtendInvoice\Subscriber
 * @version   1
 */
class Document implements SubscriberInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * Document constructor.
     * @param ModelManager  $modelManager
     * @param Configuration $configuration
     */
    public function __construct(ModelManager $modelManager, Configuration $configuration)
    {
        $this->modelManager = $modelManager;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Components_Document::assignValues::after' => 'onBeforeRenderDocument',
        ];
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     * @throws ReflectionException
     */
    public function onBeforeRenderDocument(Enlight_Hook_HookArgs $args)
    {
        /* @var Shopware_Components_Document $document */
        $document = $args->getSubject();
        $documentTypeId = (int)$this->getDocumentTypeId($document);

        /* @var Smarty_Data $view */
        $view = $document->_view;

        if (1 != $documentTypeId || !(bool)$this->configuration->getPluginConfig('DisplayEmail')) {
            return;
        }

        $order = $view->getTemplateVars('Order');
        $userId = $order['_order']['userID'];

        if (0 == (int)$userId) {
            return;
        }

        /** @var Customer $user */
        $user = $this->modelManager->find(Customer::class, $userId);

        $weloOrderData = [
            'wDocumentType' => $documentTypeId,
            'isWeloPhoneNumberEnabled' => $this->isWeloPhoneNumberEnabled(),
            'DisplayEmail' => $this->configuration->getPluginConfig('DisplayEmail'),
            'DisplayLabel' => $this->configuration->getPluginConfig('DisplayLabel'),
            'email' => $user ? $user->getEmail() : '',
        ];

        $view->assign('weloOrderData', $weloOrderData);
    }

    /**
     * @param \Shopware_Components_Document $documentComponent
     * @return int
     * @throws ReflectionException
     */
    private static function getDocumentTypeId(Shopware_Components_Document $documentComponent)
    {
        $reflectionObject = new \ReflectionObject($documentComponent);
        $reflectionProperty = $reflectionObject->getProperty('_typID');
        $reflectionProperty->setAccessible(true);

        return intval($reflectionProperty->getValue($documentComponent));
    }

    /**
     * Check if the other plugin is activated
     * @return bool
     */
    public function isWeloPhoneNumberEnabled()
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select('plugin')
            ->from(Plugin::class, 'plugin')
            ->where('plugin.name like :name')
            ->andWhere('plugin.active = :plugin_active')
            ->setParameter('name', '%WeloInvoicePhoneNumber%')
            ->setParameter('plugin_active', true)
        ;

        return $builder->getQuery()->getArrayResult() !== [] ? true : false;
    }
}