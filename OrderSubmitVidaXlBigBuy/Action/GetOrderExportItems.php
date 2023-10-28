<?php
declare(strict_types=1);

namespace RltSquare\OrderSubmitVidaXlBigBuy\Action;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use RltSquare\OrderSubmitVidaXlBigBuy\Logger\Logger;
use RltSquare\OrderSubmitVidaXlBigBuy\Model\Config;

/**
 * @class GetOrderExportItems
 */
class GetOrderExportItems
{
    /** @var array */
    private array $allowedTypes;
    private BigBuyPushDetailsToWebservice $pushDetailsToWebservice;
    private VidaXlPushDetailsToWebservice $vidaXlPushDetailsToWebservice;
    private Logger $logger;
    private Config $config;

    /**
     * @param BigBuyPushDetailsToWebservice $pushDetailsToWebservice
     * @param VidaXlPushDetailsToWebservice $vidaXlPushDetailsToWebservice
     * @param Logger $logger
     * @param Config $config
     * @param array $allowedTypes
     */
    public function __construct(
        BigBuyPushDetailsToWebservice $pushDetailsToWebservice,
        VidaXlPushDetailsToWebservice $vidaXlPushDetailsToWebservice,
        Logger                        $logger,
        Config                        $config,
        array                         $allowedTypes = []
    )
    {
        $this->allowedTypes = $allowedTypes;
        $this->pushDetailsToWebservice = $pushDetailsToWebservice;
        $this->vidaXlPushDetailsToWebservice = $vidaXlPushDetailsToWebservice;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param OrderInterface $order
     * @return array
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute(OrderInterface $order): array
    {
        $results = [];
        $bigBuyItems = [];
        $vidaXlItems = [];
        foreach ($order->getItems() as $orderItem) {
            if (in_array($orderItem->getProductType(), $this->allowedTypes)) {
                $product = $orderItem->getProduct();
                $dropShipper = $product->getCustomAttribute('DropShipper');

                if ($dropShipper) {
                    $dropShipperValue = $dropShipper->getValue();

                    if ($dropShipperValue == 1) {
                        $bigBuyItems[] = $orderItem;
                    } elseif ($dropShipperValue == 2) {
                        $vidaXlItems[] = $orderItem;
                    }
                }
            }
        }
        if ($this->config->isModuleEnabled()) {
            if (!empty($bigBuyItems)) {

                $carrierName = ''; // Initialize with an empty string or a default value

                $shippingMethod = $order->getShippingDescription();
                if ($shippingMethod) {
                    $carrierParts = explode('-', $shippingMethod, 2); // Split into parts using the first hyphen
                    if (count($carrierParts) > 1) {
                        $carrierName = trim($carrierParts[1]); // Extract the part after the hyphen and trim any spaces
                    }
                }

                $products = array();

                foreach ($bigBuyItems as $orderItem) {
                    $products[] = array(
                        "reference" => $orderItem->getSku(),
                        "quantity" => intval($orderItem->getQtyOrdered()), // Convert float to integer using interval(),
                    );
                }

                $orderPayload = array(
                    "order" => array(
                        "internalReference" => $order->getEntityId(),
                        "language" => "en",
                        "paymentMethod" => "moneybox",
                        "carriers" => array(
                            array(
                                "name" => $carrierName
                            )
                        ),
                        "shippingAddress" => array(
                            "firstName" => $order->getBillingAddress()->getFirstname(),
                            "lastName" => $order->getBillingAddress()->getLastname(),
                            "country" => $order->getBillingAddress()->getCountryId(),
                            "postcode" => $order->getBillingAddress()->getPostcode(),
                            "town" => $order->getBillingAddress()->getCity(),
                            "address" => $order->getBillingAddress()->getStreet() ? implode(', ', $order->getBillingAddress()->getStreet()) : '',
                            "phone" => $order->getBillingAddress()->getTelephone(),
                            "email" => $order->getBillingAddress()->getEmail(),
                            "vatNumber" => "Null",
                            "companyName" => $order->getBillingAddress()->getCompany(),
                            "comment" => "Comment example"
                        ),
                        "products" => $products
                    )
                );
                try {
                    $results['order_id'] = $this->pushDetailsToWebservice->execute($orderPayload);
                } catch (GuzzleException|LocalizedException $e) {
                }
                // Save big buy order ID to $bigBuyOrderId
                if (isset($results['order_id'])) {
                    $bigBuyOrderId = $results['order_id'];
                    $order->setData('big_buy_order_id', $bigBuyOrderId);
                    $order->save();

                    if ($bigBuyOrderId) {
                        $output = __('Successfully exported BigBuy order ') . $bigBuyOrderId;
                        $this->logger->notice($output);

                        // Log the saved order ID
                        $this->logger->info('Saved BigBuy Order ID: ' . $bigBuyOrderId);
                    } else {
                        $msg = $result['error'] ?? null;
                        if ($msg === null) {
                            $msg = __('Unexpected errors occurred');
                        }
                        $this->logger->warning($msg);
                    }
                }
            } else {
                // Log a message indicating that there were no Big Buy items in the order
                $this->logger->error('No Big Buy items found in the order');
            }
        } else {
            $output = __('Big Buy order export Module is Disable ');
            $this->logger->error($output);
        }
        if ($this->config->VidaXlIsEnabled()) {
            if (!empty($vidaXlItems)) {

                $customer_order_reference = $order->getEntityId();
                $comments_customer = "Please deliver asap";
                $order_products = [];
                foreach ($vidaXlItems as $vidaXlOrderItem) {
                    $sku = intval($vidaXlOrderItem->getSku()); // Get SKU of the product
                    $quantity = intval($vidaXlOrderItem->getQtyOrdered()); // Get quantity of the product

                    // Assuming you have some way to get the shipping address for each item
                    $shippingAddress = [
                        "address" => $order->getBillingAddress()->getStreet() ? implode(', ', $order->getBillingAddress()->getStreet()) : '',
                        "address2" => "",
                        "city" => $order->getBillingAddress()->getCity(),
                        "province" => $order->getBillingAddress()->getRegion(),
                        "postal_code" => $order->getBillingAddress()->getPostcode(),
                        "country" => $order->getBillingAddress()->getCountryId(),
                        "name" => $this->concatenateFullName($order),
                        'phone' => $order->getBillingAddress()->getTelephone(),
                        "comments" => ""
                    ];

                    $order_products[] = [
                        "product_code" => $sku,
                        "quantity" => $quantity,
                        "addressbook" => $shippingAddress
                    ];
                }
                $payload = [
                    "customer_order_reference" => $customer_order_reference,
                    "comments_customer" => $comments_customer,
                    "order_products" => $order_products
                ];

                $results['vidaXL_order_id'] = $this->vidaXlPushDetailsToWebservice->execute($payload);
                if (isset($results['vidaXL_order_id'])) {
                    $vidaXLOrderId = $results['vidaXL_order_id']['order_id'];
                    $order->setData('vidaXl_order_id', $vidaXLOrderId);
                    $order->save();
                }
            } else {
                // Log a message indicating that there were no VidaXL items in the order
                $this->logger->error('No vidaXL items found in the order');
            }
        } else {
            $output = __('VidaXL order export Module is Disable ');
            $this->logger->error($output);
        }
        return $results;
    }

    private function concatenateFullName($order): string
    {
        $firstName = $order->getBillingAddress()->getFirstname();
        $lastName = $order->getBillingAddress()->getLastname();
        return $firstName . ' ' . $lastName;
    }
}
