<?php

namespace MageSuite\BulkGoodsMolliePayment\Service;

class BulkFeeOrderLineCreator
{
    protected \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods;
    protected \Mollie\Payment\Helper\General $mollieHelper;
    protected \Mollie\Payment\Service\Order\Lines\OrderLinesProcessor $orderLinesProcessor;
    protected \Mollie\Payment\Model\OrderLinesFactory $orderLinesFactory;

    public function __construct(
        \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods,
        \Mollie\Payment\Helper\General $mollieHelper,
        \Mollie\Payment\Service\Order\Lines\OrderLinesProcessor $orderLinesProcessor,
        \Mollie\Payment\Model\OrderLinesFactory $orderLinesFactory
    ) {
        $this->bulkGoods = $bulkGoods;
        $this->mollieHelper = $mollieHelper;
        $this->orderLinesFactory = $orderLinesFactory;
        $this->orderLinesProcessor = $orderLinesProcessor;
    }

    public function createBulkFeeOrderLine($order)
    {
        $fee = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $tax = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_CODE);

        if (empty($fee) || $fee == 0) {
            return [];
        }

        $forceBaseCurrency = (bool)$this->mollieHelper->useBaseCurrency($order->getStoreId());
        $currency = $forceBaseCurrency ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode();
        $vatRate = $tax ? round(($tax / ($fee - $tax)) * 100, 2) : 0;

        $orderLineData = [
            'type' => 'surcharge',
            'name' => __('Bulk Goods Fee'),
            'quantity' => 1,
            'unitPrice' => $this->mollieHelper->getAmountArray($currency, $fee),
            'totalAmount' => $this->mollieHelper->getAmountArray($currency, $fee),
            'vatRate' => $vatRate,
            'vatAmount' => $this->mollieHelper->getAmountArray($currency, $tax),
        ];

        $orderLineDataProcessed = $this->orderLinesProcessor->process($orderLineData, $order);
        $orderLine = $this->orderLinesFactory->create();
        $orderLine->addData($orderLineDataProcessed)->setOrderId($order->getId())->save();
        unset($orderLineDataProcessed['item_id']);

        return $orderLineDataProcessed;
    }
}
