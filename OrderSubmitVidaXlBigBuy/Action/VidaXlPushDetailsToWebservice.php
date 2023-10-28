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
class VidaXlPushDetailsToWebservice
{
    private Config $config;
    private Logger $logger;
    private VidaXlProcessResponse $vidaXlProcessResponse;

    /**
     * @param Config $config
     * @param Logger $logger
     * @param VidaXlProcessResponse $vidaXlProcessResponse
     */
    public function __construct(
        Config                $config,
        Logger                $logger,
        VidaXlProcessResponse $vidaXlProcessResponse
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->vidaXlProcessResponse = $vidaXlProcessResponse;
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function execute(array $exportDetails): array
    {
        $apiUrl = $this->config->VidaXlGetApiUrl();
        $apiUserName = $this->config->VidaXlGetApiUserName();
        $apiToken = $this->config->VidaXlGetApiToken();

        $authCredentials = [
            'Username' => $apiUserName,
            'Password' => $apiToken
        ];
        // Use GuzzleHttp (http://docs.guzzlephp.org/en/stable/) to send the data to our webservice.
        $client = new Client();
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($authCredentials['Username'] . ':' . $authCredentials['Password']),
            ],
            'body' => json_encode($exportDetails),
        ];

        try {
            $response = $client->post($apiUrl, $options);
            $VidaXLOrderId = $this->vidaXlProcessResponse->processResponse($response); // Call the processResponse function and get the order ID
        } catch (GuzzleException|LocalizedException $ex) {
            $this->logger->error($ex->getMessage(), [
                'details' => $exportDetails
            ]);

            throw $ex;
        }

        return $VidaXLOrderId;
    }


}
