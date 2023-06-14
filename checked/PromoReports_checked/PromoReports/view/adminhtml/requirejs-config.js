var config = {
    map: {
        '*': {
            amasty_promo_reports: 'Amasty_PromoReports/js/reports'
        }
    },

    shim: {
        'Amasty_PromoReports/vendor/amcharts/charts': {
            deps: ['Amasty_PromoReports/vendor/amcharts/core.min']
        },

        'Amasty_PromoReports/vendor/amcharts/animated': {
            deps: ['Amasty_PromoReports/vendor/amcharts/core.min']
        }
    }
};
