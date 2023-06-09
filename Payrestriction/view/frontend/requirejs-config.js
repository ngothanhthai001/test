var config = {
    map: {
        '*': {
            //fix Magento 2.3.5 error when selected payment method is restricted
            'Magento_SalesRule/js/action/select-payment-method-mixin':
                'Amasty_Payrestriction/js/action/select-payment-method-mixin'
        }
    }
};
