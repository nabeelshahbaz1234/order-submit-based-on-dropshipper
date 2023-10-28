<?php
declare(strict_types=1);

namespace RltSquare\OrderSubmitVidaXlBigBuy\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * @class Handler
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::NOTICE;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/bigBuyVidaXlOrder.log';
}
