<?php

namespace MageSuite\BulkGoodsMolliePayment\Observer;

class AddFeeToMollieOrder implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\BulkGoodsMolliePayment\Service\BulkFeeOrderLineCreator $bulkFeeOrderLine;

    public function __construct(
        \MageSuite\BulkGoodsMolliePayment\Service\BulkFeeOrderLineCreator $bulkFeeOrderLine
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
