<?php

namespace MageSuite\BulkGoodsMolliePayment\Observer;

class AddFeeToMollieOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\BulkGoodsMolliePayment\Service\BulkFeeOrderLine
     */
    protected $bulkFeeOrderLine;

    public function __construct(
        \MageSuite\BulkGoodsMolliePayment\Service\BulkFeeOrderLine $bulkFeeOrderLine
    ) {
        $this->bulkFeeOrderLine = $bulkFeeOrderLine;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getDataByKey('order');

        $orderLine = $this->bulkFeeOrderLine->createBulkFeeOrderLine($order);
        if (empty($orderLine)) {
            return $this;
        }

        /** @var \Magento\Framework\DataObject $orderData */
        $orderData = $observer->getDataByKey('order_data');
        $lines = $orderData->hasData('lines') ? $orderData->getDataByKey('lines') : [];
        $lines[] = $orderLine;
        $orderData->setData('lines', $lines);

        return $this;
    }
}
