<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Recent extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->getLatestOrders();
    }

    /**
     * Get latest placed orders per customer and current store limit by 5
     */
    protected function getLatestOrders()
    {
        $this->searchCriteriaBuilder->addFilter(
            'customer_id',
            $this->_customerSession->getCustomerId()
        );

        $this->searchCriteriaBuilder->addFilter(
            'status',
            $this->_orderConfig->getVisibleOnFrontStatuses(),
            'in'
        );

        $this->searchCriteriaBuilder->addFilter(
            'store_id',
            $this->_storeManager->getStore()
                ->getId()
        );

        $sortOrder = $this->sortOrderBuilder
            ->setField('created_at')
            ->setDirection('DESC')
            ->create();

        $this->searchCriteriaBuilder->setPageSize(5);

        $this->searchCriteriaBuilder->addSortOrder($sortOrder);

        $searchResults = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        $this->setOrders($searchResults);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getTrackUrl($order)
    {
        return $this->getUrl('sales/order/track', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (sizeof($this->getOrders()) > 0) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }
}
