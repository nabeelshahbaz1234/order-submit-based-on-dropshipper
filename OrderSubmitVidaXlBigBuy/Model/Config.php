<?php
declare(strict_types=1);

namespace RltSquare\OrderSubmitVidaXlBigBuy\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @class Config
 */
class Config
{
    /**
     * Const Big Buy Fields
     */
    const CONFIG_PATH_BIG_BUY_ENABLED = 'sales/order_export/enabled';
    const CONFIG_PATH_BIG_BUY_API_TOKEN = 'sales/order_export/api_token';
    const CONFIG_PATH_BIG_BUY__API_URL = 'sales/order_export/api_url';

    /**
     * Const VidaXL Fields
     */
    const CONFIG_PATH_VidaXL_ENABLED = 'sales/vida_xl_order_export/enabled';
    const CONFIG_PATH_VidaXL_API_TOKEN = 'sales/vida_xl_order_export/api_token';
    const CONFIG_PATH_VidaXL_API_USERNAME = 'sales/vida_xl_order_export/user_name';
    const CONFIG_PATH_VidaXL_API_URL = 'sales/vida_xl_order_export/api_url';

    private ScopeConfigInterface $scopeConfig;


    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $path
     * @return bool
     */
    public function getDefaultConfig($path): bool
    {
        return (bool)$this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return (bool)$this->getDefaultConfig(self::CONFIG_PATH_BIG_BUY_ENABLED);
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function BigBuyGetApiToken(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_BIG_BUY_API_TOKEN, $scopeType, $scopeCode);
        return ($value !== null) ? (string)$value : '';
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function BigBuyGetApiUrl(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_BIG_BUY__API_URL, $scopeType, $scopeCode);
        return ($value !== null) ? (string)$value : '';
    }

    /**
     * @return bool
     */
    public function VidaXlIsEnabled(): bool
    {
        return (bool)$this->getDefaultConfig(self::CONFIG_PATH_VidaXL_ENABLED);
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function VidaXlGetApiToken(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_VidaXL_API_TOKEN, $scopeType, $scopeCode);
        return ($value !== null) ? (string)$value : '';
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function VidaXlGetApiUserName(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_VidaXL_API_USERNAME, $scopeType, $scopeCode);
        return ($value !== null) ? (string)$value : '';
    }

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function VidaXlGetApiUrl(string $scopeType = ScopeInterface::SCOPE_STORE, ?string $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_VidaXL_API_URL, $scopeType, $scopeCode);
        return ($value !== null) ? (string)$value : '';
    }
}
