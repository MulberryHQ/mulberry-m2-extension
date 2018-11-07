/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

var config = {
    map: {
        '*': {
            "mulberryLibrary": 'Mulberry_Warranty/js/mulberry-library',
            "mulberryProductPage": 'Mulberry_Warranty/js/mulberry-product-page'
        }
    },
    config: {
        mixins: {
            'Magento_Catalog/js/price-box': {
                'Mulberry_Warranty/js/mulberry-price-box-mixin': true
            },
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Mulberry_Warranty/js/mulberry-add-to-cart-mixin': true
            },
        }
    }
};
