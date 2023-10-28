<?php
declare(strict_types=1);

namespace RltSquare\OrderSubmitVidaXlBigBuy\Action;


use Exception;
use Magento\Framework\Exception\LocalizedException;
use Psr\Http\Message\ResponseInterface;
use function json_decode;

class VidaXlProcessResponse
{
    /**
     * @throws LocalizedException
     */
    public function processResponse(ResponseInterface $response): ?array
    {
        $responseBody = (string)$response->getBody();
        try {
            $responseData = json_decode($responseBody, true);
        } catch (Exception $e) {
            $responseData = [];
        }
        if (isset($responseData['order']['id'])) {
            $orderId = $responseData['order']['id']; // Extract the order ID
            return ['order_id' => $orderId]; // Return an array with the order ID
        } else {
            // No order ID in the response, show the error message
            $errorMsg = __($responseData['error']) ?? __('There was a problem: %1', $responseBody);

            if ($response->getStatusCode() !== 201) {
                throw new LocalizedException($errorMsg);
            }
        }
        return null; // Return null if no order ID

    }

}
