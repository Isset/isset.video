import Chart from 'chart.js';

jQuery(($) => {

    const baseOptions = {
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
                }
            }],
            xAxes: [{
                ticks: {
                    callback: row => row.getDate()
                }
            }]
        },
    };

    const baseBgColor = [
        'rgba(46, 112, 157, .2)'
    ];

    const baseBorderColor = [
        'rgba(46, 112, 157, 1)'
    ];

    if (adminpage === "edit-php" && typenow === "video-publisher") {
        // Dashboard
        $('.wp-header-end').after($('<div id="issetVideoDash" class="video-publisher-mb-2"></div>'));

        $.ajax({
            url: '/?rest_route=/isset-publisher/v1/dashboard',
        }).done(function (res) {
            $('#issetVideoDash').addClass('card').html(res.html);

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

            let dataOptions = Object.assign({}, baseOptions);
            dataOptions.scales.yAxes[0].ticks.callback = row => `${row} GB`;
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
        });
    }
});