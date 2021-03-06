<?php

namespace AltoLabs\Snappic\Observer;

use Magento\Framework\Event\ObserverInterface;

class HandleStockChange extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->handleProductChanges((array) $observer->getEvent()->getProductIds());
    }
}
