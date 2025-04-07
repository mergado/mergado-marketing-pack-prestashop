<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service\External\Facebook;

use Mergado;
use Mergado\Helper\PrestashopVersionHelper;
use Mergado\Helper\ProductHelper;
use Mergado\Service\AbstractBaseService;
use Mergado\Service\CookieService;
use Mergado\Utility\SmartyTemplateLoader;
use Throwable;
use Tools;

class FacebookServiceIntegration extends AbstractBaseService
{
    /**
     * @var FacebookService
     */
    private $facebookService;

    /**
     * @var CookieService
     */
    private $cookieService;

    public const TEMPLATES_PATH = 'views/templates/services/Facebook/';
    public const JS_PATH = 'views/js/services/Facebook/';

    protected function __construct()
    {
        $this->facebookService = FacebookService::getInstance();
        $this->cookieService = CookieService::getInstance();

        parent::__construct();
    }

    public function search($module, $smarty, $context, $path): string
    {
        try {
            if (!$this->facebookService->isActive()) {
                return '';
            }

            $context->controller->addJS($path . self::JS_PATH . 'fbpixel.js');

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'search.tpl',
                $smarty,
                [
                    'fbPixelCode' => $this->facebookService->getCode(),
                    'searchQuery' => Tools::getValue('search_query'),
                    'fbPixel_advertisement_consent' => $this->cookieService->advertismentEnabled(),
                    'psVersion17AndHigher' => PrestashopVersionHelper::is17AndHigher(),
                    'psVersion16AndLower' => PrestashopVersionHelper::is16AndLower(),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function addToCart(): string
    {
        try {
            // There is an ajax call when user changes variant... jQuery parser cant handle that
            if ($this->facebookService->isActive() && !(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] === '1')) {
                ?>
                <script>
                  // In product detail and modal in PS1.7
                  if (typeof $ !== 'undefined') {
                    $('.add-to-cart').on('click', function () {
                      var $_defaultInitialId = '<?php echo Tools::getValue('id_product_attribute') ?>';

                      var $_currency = $('.product-price').find('[itemprop="priceCurrency"]').attr('content');
                      var $_id = $(this).closest('form').find('#product_page_product_id').val();
                      var $_name = $('h1[itemprop="name"]').text();
                      var $_price = $('.product-price').find('[itemprop="price"]').attr('content');
                      var $_quantity = $(this).closest('form').find('#quantity_wanted').val();

                      if ($_name === '') {
                        $_name = $('.modal-body h1').text();
                      }

                      if ($(this).closest('form').find('#idCombination').length > 0) {
                        $_id = $_id + '-' + $(this).closest('form').find('#idCombination').val();
                      } else if ($_defaultInitialId && $_defaultInitialId !== '') {
                        $_id = $_id + '-' + $_defaultInitialId;
                      }

                      fbq('track', 'AddToCart', {
                        content_name: $_name,
                        content_ids: [$_id],
                        contents: [{'id': $_id, 'quantity': $_quantity}],
                        content_type: 'product',
                        value: $_price,
                        currency: $_currency,
                        consent: window.mmp.cookies.sections.advertisement.onloadStatus,
                      });
                    });
                  }
                </script>
                <?php
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    public function purchase(Mergado $module, $smarty, $params, $products): string
    {
        try {
            if (!$this->facebookService->isActive()) {
                return '';
            }

            return SmartyTemplateLoader::render(
                $module,
                self::TEMPLATES_PATH . 'purchase.tpl',
                $smarty,
                [
                    'fbPixelData' => $this->getFbPixelData($params, $products),
                ]
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return '';
    }

    private function getFbPixelData($params, $products): array
    {
        $withVat = $this->facebookService->getConversionVatIncluded();

        if (PrestashopVersionHelper::is16AndLower()) {
            if ($withVat) {
                $orderValue = $params['objOrder']->total_products_wt;
            } else {
                $orderValue = $params['objOrder']->total_products;
            }

        } else {
            if ($withVat) {
                $orderValue = $params['order']->total_products_wt;
            } else {
                $orderValue = $params['order']->total_products;
            }
        }

        return [
            'products' => $this->getProducts($products),
            'productsWithQuantity' => $this->getProductsWithQuantity($products),
            'orderValue' => $orderValue,
        ];
    }

    private function getProducts($products): array
    {
        $fbProducts = [];

        foreach ($products as $product) {
            $fbProducts[] = ProductHelper::getProductId($product);
        }

        return $fbProducts;
    }

    private function getProductsWithQuantity($products): array
    {
        $fbProducts = [];

        foreach ($products as $product) {
            $fbProducts[] = json_encode(['id' => Mergado\Helper\ProductHelper::getProductId($product), 'quantity' => $product['quantity']]);
        }

        return $fbProducts;
    }
}
