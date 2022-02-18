<?php

namespace MageSuite\BulkGoodsMolliePayment\Service;

class BulkFeeOrderLine
{
    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    /**
     * @var \Mollie\Payment\Helper\General
     */
    protected $mollieHelper;

    /**
     * @var \Mollie\Payment\Service\Order\Lines\OrderLinesProcessor
     */
    protected $orderLinesProcessor;

    /** @var \Mollie\Payment\Model\OrderLinesFactory */
    protected $orderLinesFactory;

    public function createBulkFeeOrderLine($order)
    {
        $fee = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $tax = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_CODE);

        if (empty($fee)) {
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
