define([
    'jquery',
    'Amasty_PromoReports/vendor/amcharts/core.min',
    'Amasty_PromoReports/vendor/amcharts/charts',
    'Amasty_PromoReports/vendor/amcharts/animated'
], function ($) {
    'use strict';

    $.widget('amasty_promo.Charts', {
        /**
         *  Amcharts graphics initialization
         *
         *  @am4core - amcharts/core.js
         *  @am4charts - amcharts/charts.js
         *  @am4themes_animated - amcharts/animated.js
         */

        renderClusteredBarChart: function (data, dataFields, containerId) {
            am4core.ready(function() {
                am4core.useTheme(am4themes_animated);

                var chart = am4core.create(containerId, am4charts.XYChart);

                chart.data = data;

                var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = dataFields['category'];
                categoryAxis.numberFormatter.numberFormat = "#";
                categoryAxis.renderer.inversed = true;
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.labels.template.fontSize = 10;
                categoryAxis.renderer.cellStartLocation = 0.1;
                categoryAxis.renderer.cellEndLocation = 0.9;

                var  valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
                valueAxis.renderer.opposite = true;

                function createSeries(field, name) {
                    var series = chart.series.push(new am4charts.ColumnSeries());
                    series.dataFields.valueX = field;
                    series.dataFields.categoryY = dataFields['category'];
                    series.name = name;
                    series.columns.template.tooltipText = "{name}: [bold]{valueX}[/]";
                    series.columns.template.height = am4core.percent(100);
                    series.sequencedInterpolation = true;

                    var valueLabel = series.bullets.push(new am4charts.LabelBullet());
                    valueLabel.label.text = "[font-size: 10px]{valueX}";
                    valueLabel.label.horizontalCenter = "left";
                    valueLabel.label.dx = 10;
                    valueLabel.label.hideOversized = false;
                    valueLabel.label.truncate = false;
                    valueAxis.renderer.labels.template.fontSize = 10;
                }

                $.each(dataFields['dataSeries'], function (key, value) {
                    createSeries(value.name, value.label);
                });

                chart.legend = new am4charts.Legend();
                chart.legend.useDefaultMarker = true;
                chart.legend.position = "top";

                chart.legend.itemContainers.template.paddingTop = 0;
                chart.legend.itemContainers.template.paddingBottom = 0;

                chart.legend.markers.template.width = 15;
                chart.legend.markers.template.height = 15;
                chart.legend.markers.template.children.getIndex(0).cornerRadius(0, 0, 0, 0);

                // adaptive height
                var cellSize = 40;
                chart.events.on("datavalidated", function(ev) {
                    var chart = ev.target;
                    var categoryAxis = chart.yAxes.getIndex(0);
                    var adjustHeight = chart.data.length * cellSize - categoryAxis.pixelHeight;
                    var targetHeight = chart.pixelHeight + adjustHeight;
                    chart.svgContainer.htmlElement.style.height = targetHeight + "px";
                });
            });
        }
    });

    return $.amasty_promo.Charts;
});
