import Chart from 'chart.js';

if (typeof adminpage !== 'undefined' && adminpage === 'toplevel_page_isset-video-overview') {
    jQuery(($) => {
        const {loggedIn} = IssetVideoPublisherAjax;

        if (!loggedIn) {
            return;
        }

        const baseOptions = {
            responsive: true,
            maintainAspactRatio: false,
            legend: {
                display: false,
            },
            tooltips: {
                callbacks: {
                    title: row => `${row[0].xLabel.getFullYear()}-${row[0].xLabel.getMonth() + 1}-${row[0].xLabel.getDate()}`,
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        fontColor: '#fff',
                    },
                    gridLines: {
                        color: 'rgba(200, 200, 200, .15)',
                        tickMarkLength: 5,
                    }
                }],
                xAxes: [{
                    ticks: {
                        callback: row => row.getDate(),
                        fontColor: '#fff',
                    },
                    gridLines: {
                        color: 'rgba(200, 200, 200, .25)',
                        tickMarkLength: 5,
                    }
                }],

            },
        };

        const baseBgColor = [
            'rgba(46, 112, 157, .2)'
        ];

        const baseBorderColor = [
            'rgb(80,158,210)'
        ];

        if (typeof adminpage !== 'undefined' && adminpage === "toplevel_page_isset-video-overview") {
            let streamingViewsData = $('#videoPublisherStreamingViews').children();
            let streamingBytesData = $('#videoPublisherStreamingData').children();

            let chartViews = new Chart('videoPublisherViewsChart', {
                type: 'line',
                data: {
                    labels: streamingViewsData.map(function () {
                        return new Date($(this).data('key'))
                    }),
                    datasets: [{
                        label: 'Views',
                        data: streamingViewsData.map(function () {
                            return $(this).data('value')
                        }),
                        backgroundColor: baseBgColor,
                        borderColor: baseBorderColor,
                        borderWidth: 1
                    }]
                },
                options: baseOptions
            });

            chartViews.height = 200;

            let dataOptions = Object.assign({}, baseOptions);
            dataOptions.scales.yAxes[0].ticks.callback = row => `${row.toFixed(2)} GB`;
            dataOptions.tooltips.callbacks.label = row => `${parseFloat(row.value).toFixed(2)} GB`;

            let chartDatea = new Chart('videoPublisherDataChart', {
                type: 'line',
                data: {
                    labels: streamingBytesData.map(function () {
                        return new Date($(this).data('key'))
                    }),
                    datasets: [{
                        label: 'Data send',
                        data: streamingBytesData.map(function () {
                            return $(this).data('value') / 1e+9
                        }),
                        backgroundColor: baseBgColor,
                        borderColor: baseBorderColor,
                        borderWidth: 1
                    }]
                },
                options: dataOptions
            });

            $('#videoPublisherChartToggleViews').on('click', function () {
                $(this).removeClass('btn-inactive');
                if (!$('#videoPublisherChartToggleData').hasClass('btn-inactive')) {
                    $('#videoPublisherChartToggleData').addClass('btn-inactive')
                }

                $('#videoPublisherViewsChart').show();
                $('#videoPublisherDataChart').hide();
            });

            $('#videoPublisherChartToggleData').on('click', function () {
                $(this).removeClass('btn-inactive');
                if (!$('#videoPublisherChartToggleViews').hasClass('btn-inactive')) {
                    $('#videoPublisherChartToggleViews').addClass('btn-inactive')
                }

                $('#videoPublisherDataChart').show();
                $('#videoPublisherViewsChart').hide();
            });
        }
    });
}