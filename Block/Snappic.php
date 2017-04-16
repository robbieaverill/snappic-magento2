<?php

namespace AltoLabs\Snappic\Block;

class Snappic extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \AltoLabs\Snappic\Model\Connect
     */
    protected $connect;

    /**
     * @var \AltoLabs\Snappic\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \AltoLabs\Snappic\Model\Connect                  $connect
     * @param \AltoLabs\Snappic\Helper\Data                    $helper
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \AltoLabs\Snappic\Model\Connect $connect,
        \AltoLabs\Snappic\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->connect = $connect;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * Decides whether to show the conversion script given the current context
     *
     * @return bool
     */
    public function getShowConversionScript()
    {
        if (empty($this->getFacebookId())) {
            return false;
        }

        /** @var \Magento\Sales\Model\Order|null $order */
        $order = $this->getLastOrder();
        if (!$order->getIncrementId()) {
            return false;
        }

        return true;
    }

    /**
     * Decides whether to show the product script
     *
     * @return bool
     */
    public function getShowProductScript()
    {
        if (empty($this->getFacebookId()) || !$this->registry->registry('current_product')) {
            return false;
        }
        return true;
    }

    /**
     * Decides whether to show the visitor script
     *
     * @return bool
     */
    public function getShowVisitorScript()
    {
        return !empty($this->getFacebookId());
    }

    /**
     * Get the Facebook pixel ID
     *
     * @return string
     */
    public function getFacebookId()
    {
        return (string) $this->connect->getFacebookId();
    }

    /**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getLastOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Given an order, return a rounded grand total for it
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getOrderTotal(\Magento\Sales\Model\Order $order)
    {
        return round($order->getGrandTotal(), 2);
    }

    /**
     * Get the current store's currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return (string) $this->storeMananger->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Return an array of Snappic product IDs for all visible items in the given order
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getSnappicProductIds(\Magento\Sales\Model\Order $order)
    {
        $items = $order->getAllVisibleItems();
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $this->getSnappicProductId($item->getProduct());
        }
        return $productIds;
    }

    /**
     * Return a single Snappic product ID from the Magento registry (current product)
     *
     * @return string
     */
    public function getSnappicProductIdFromRegistry()
    {
        return $this->getSnappicProductId($this->registry->registry('current_product'));
    }

    /**
     * Given a product, return a Snappic product ID for it
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSnappicProductId(\Magento\Catalog\Model\Product $product)
    {
        return 'snappic_' . $product->getId();
    }

    /**
     * Get a product from the session, assuming it was added when following a landing page link etc
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    public function getProductFromSession()
    {
        $product = $this->checkoutSession->getCartProductJustAdded();
        if ($product && is_object($product) && $product instanceof \Magento\Catalog\Model\Product) {
            $this->checkoutSession->unsCartProductJustAdded();
            return $product;
        }
        return false;
    }
}