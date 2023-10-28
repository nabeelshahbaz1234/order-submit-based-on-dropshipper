<?php
declare(strict_types=1);

namespace RltSquare\OrderSubmitVidaXlBigBuy\Action;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use RltSquare\OrderSubmitVidaXlBigBuy\Logger\Logger;
use RltSquare\OrderSubmitVidaXlBigBuy\Model\Config;
use function json_encode;


/**
 * @class PushDetailsToWebservice
 */
class BigBuyPushDetailsToWebservice
{
    private Config $config;
    private Logger $logger;
    private BigBuyProcessResponse $bigBuyProcessResponse;

    /**
     * @param Config $config
     * @param Logger $logger
     * @param BigBuyProcessResponse $bigBuyProcessResponse
     */
    public function __construct(
        Config                $config,
        Logger                $logger,
        BigBuyProcessResponse $bigBuyProcessResponse
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->bigBuyProcessResponse = $bigBuyProcessResponse;
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function execute(array $exportDetails): string
    {
        $apiUrl = $this->config->BigBuyGetApiUrl();
        $apiToken = $this->config->BigBuyGetApiToken();

        // Use GuzzleHttp (http://docs.guzzlephp.org/en/stable/) to send the data to our webservice.
        $client = new Client();
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiToken,
            ],
            'body' => json_encode($exportDetails),
        ];

        try {
            $response = $client->post($apiUrl, $options);
            $BigBuyOrderId = $this->bigBuyProcessResponse->processResponse($response); // Call the processResponse function and get the order ID
        } catch (GuzzleException|LocalizedException $ex) {
            $this->logger->error($ex->getMessage(), [
                'details' => $exportDetails
            ]);

            throw $ex;
        }

        return $BigBuyOrderId;
    }


}
