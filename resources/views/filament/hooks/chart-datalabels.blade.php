<script data-navigate-once>
(function () {
    window.filamentChartJsPlugins = window.filamentChartJsPlugins || [];

    if (window.filamentChartJsPlugins.some(function (p) { return p.id === 'barDataLabels'; })) {
        return;
    }

    var barDataLabelsPlugin = {
        id: 'barDataLabels',
        afterDatasetsDraw: function (chart, args, pluginOptions) {
            if (chart.config.type !== 'bar') {
                return;
            }

            if (pluginOptions && pluginOptions.display === false) {
                return;
            }

            var ctx = chart.ctx;
            var prefix = (pluginOptions && pluginOptions.prefix !== undefined) ? pluginOptions.prefix : '$';
            var fontSize = (pluginOptions && pluginOptions.fontSize) ? pluginOptions.fontSize : 11;

            chart.data.datasets.forEach(function (dataset, datasetIndex) {
                var meta = chart.getDatasetMeta(datasetIndex);
                if (meta.hidden) {
                    return;
                }

                meta.data.forEach(function (bar, index) {
                    var val = dataset.data[index];
                    if (val === null || val === undefined || val === 0) {
                        return;
                    }

                    ctx.save();

                    var isDark = document.documentElement.classList.contains('dark');
                    ctx.fillStyle = isDark ? '#e2e8f0' : '#334155';
                    ctx.font = 'bold ' + fontSize + 'px Inter, system-ui, -apple-system, sans-serif';
                    ctx.textAlign = 'center';

                    var formattedNumber = typeof val === 'number'
                        ? val.toLocaleString('en-US', {
                            minimumFractionDigits: (val % 1 !== 0) ? 2 : 0,
                            maximumFractionDigits: 2,
                        })
                        : val;

                    var text = prefix ? (val < 0 ? '-' + prefix + Math.abs(val) : prefix + formattedNumber) : formattedNumber;

                    if (val >= 0) {
                        ctx.textBaseline = 'bottom';
                        ctx.fillText(text, bar.x, bar.y - 4);
                    } else {
                        ctx.textBaseline = 'top';
                        ctx.fillText(text, bar.x, bar.y + 4);
                    }

                    ctx.restore();
                });
            });
        }
    };

    window.filamentChartJsPlugins.push(barDataLabelsPlugin);
})();
</script>

