define([
    'jquery',
    'Amasty_PromoReports/js/ampromo-charts'
], function ($, ampromoCharts) {
    'use strict';

    $.widget('amasty_promo.Reports', {
        options: {
            ajaxUrl: '',
            reportError: '[data-ampromo-js="report-error"]',
            dataRange: '[data-ampromo-js="data-select"]',
            dataHiddenRange: '[data-ampromo-js="hidden-range"]',
            dataRangeCustomOptionValue: 0
        },

        _create: function () {
            $('[data-ampromo-js="date-range"]').parent().attr('data-ampromo-js', 'hidden-range');

            $(this.options.dataRange).on('change', this.checkPeriodVisibility.bind(this));
            $('[data-ampromo-js="report-submit"]').on('click', this.refresh.bind(this));

            this.refresh();
        },

        checkPeriodVisibility: function () {
            $(this.options.dataHiddenRange).toggle($(this.options.dataRange).val() == this.options.dataRangeCustomOptionValue);
        },

        refresh: function () {
            var self = this;

            $.ajax({
                showLoader: true,
                url: self.options.ajaxUrl,
                dataType: 'JSON',
                data: $('.entry-edit.form-inline :input').serializeArray(),
                type: "POST",
                success: function (response) {
                    if (response.type === 'success') {
                        $(self.options.reportError).hide();
                        self.setData(response.data['statisticsData']);
                        ampromoCharts().renderClusteredBarChart(response.data['averageCheckData'], response.data['checkDataFields'], self.options.chartContainerId);
                    }

                    if (response.type === 'warning') {
                        $(self.options.reportError).text(response.message).show();
                        $(self.options.reportError).removeClass('message-error error').addClass('message-info info');
                    }

                    if (response.type === 'error') {
                        $(self.options.reportError).text(response.message).show();
                        $(self.options.reportError).removeClass('message-info info').addClass('message-error error');
                    }
                }
            });
        },

        setData: function (data) {
            Object.keys(data).map(function (objectKey, index) {
                $('[data-ampromo-js="' + objectKey + '"]').html(data[objectKey]);
            });
        }
    });

    return $.amasty_promo.Reports;
});
