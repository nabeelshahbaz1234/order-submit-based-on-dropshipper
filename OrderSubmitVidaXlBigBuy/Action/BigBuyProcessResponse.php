<?php
declare(strict_types=1);

namespace RltSquare\OrderSubmitVidaXlBigBuy\Action;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Psr\Http\Message\ResponseInterface;
use function json_decode;

/**
 * @class BigBuyProcessResponse
 */
class BigBuyProcessResponse
{

    /**
     * @throws LocalizedException
     */
    public function processResponse(ResponseInterface $response): ?string
    {
        $responseBody = (string)$response->getBody();
        try {
            $responseData = json_decode($responseBody, true);
        } catch (Exception $e) {
            $responseData = [];
        }
        if (isset($responseData['order_id'])) {
            // Process the order_id as needed, e.g., return it or perform some actions
            return $responseData['order_id'];
        } else {
            // No order_id in the response, show the error message
            $errorMsg = __($responseData['error']) ?? __('There was a problem: %1', $responseBody);

            if ($response->getStatusCode() !== 201) {
                throw new LocalizedException($errorMsg);
            }
        }
        return null; // Return null if no order_id is found in the response.

    }
}
