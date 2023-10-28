<?php
declare(strict_types=1);

namespace RltSquare\OrderSubmitVidaXlBigBuy\Observer;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use RltSquare\OrderSubmitVidaXlBigBuy\Action\GetOrderExportItems;
use RltSquare\OrderSubmitVidaXlBigBuy\Logger\Logger;

/**
 * Executes on order status change
 *
 * Class AfterPlaceOrder
 */
class SubmitOrderObserver implements ObserverInterface

{
    private ManagerInterface $manager;
    private Logger $logger;
    private GetOrderExportItems $getOrderExportItems;

    /**
     * @param ManagerInterface $manager
     * @param GetOrderExportItems $getOrderExportItems
     * @param Logger $logger
     */
    public function __construct(

        ManagerInterface    $manager,
        GetOrderExportItems $getOrderExportItems,
        Logger              $logger,
    )
    {
        $this->manager = $manager;
        $this->logger = $logger;
        $this->getOrderExportItems = $getOrderExportItems;
    }

    /**
     * @param Observer $observer
     * @return array
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute(Observer $observer): array
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        $orderId = (int)$order->getEntityId();
        if (!$orderId) {
            $this->manager->addErrorMessage(__('Order ID is missing.'));
        }

        $result = $this->getOrderExportItems->execute($order);

        if (isset($result['vidaXL_order_id'])) {
            $vidaXL_order_id = $result['vidaXL_order_id']['order_id']; // Extracting the ID from the array
            $order->setData('vidaXL_order_id', $vidaXL_order_id);
            $order->save();
            if ($vidaXL_order_id) {
                $output = __('Successfully exported vidaXL order ') . $vidaXL_order_id;
                $this->logger->notice($output); // Log the success message
            } else {
                $msg = $result['error'] ?? null;
                if ($msg === null) {
                    $msg = __('Unexpected errors occurred');
                }
                $this->logger->warning($msg);
            }
        }

        return $result;
    }
}
