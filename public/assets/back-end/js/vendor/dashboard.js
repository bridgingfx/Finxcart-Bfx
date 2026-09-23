"use strict";
let isMonthCheckedForEarningStatistic = false;
let countLabel = 0;
let currencySymbol = $('#get-currency-symbol').data('currency-symbol')

$(document).ready(function () {
    let method_id = $('#withdraw_method').val();

    if (method_id) {
        withdraw_method_toggle(method_id);
    }

    $("#statistics_type").on("change", function () {

        let type = $(this).val();
        let url = $('#order-status-url').data('url');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        console.log(url.replace(':type', type))
        $.get({
            url: url.replace(':type', type),
            success: function (data) {
                $('#order_stats').html(data.view)
            },
        });
    });
});

$('#withdraw_method').on('change', function () {
    withdraw_method_toggle(this.value);
});

$(document).on('change', '#withdraw_bank_id', function () {
    load_bank_preview(this.value);
});

function withdraw_method_toggle(method_id) {
    let isBank = $('#withdraw_method option[value="' + method_id + '"]').data('is-bank') == 1;
    if (isBank) {
        $('#method-filed__div').html('').addClass('d-none');
        $('#withdraw-bank-select-div').removeClass('d-none');
        let bankId = $('#withdraw_bank_id').val();
        if (bankId) {
            load_bank_preview(bankId);
        }
    } else {
        $('#withdraw-bank-select-div').addClass('d-none');
        $('#withdraw-bank-preview').html('');
        $('#method-filed__div').removeClass('d-none');
        withdraw_method_field(method_id);
    }
}

function load_bank_preview(bank_id) {
    if (!bank_id) {
        $('#withdraw-bank-preview').html('');
        return;
    }
    let url = $('#withdraw-bank-preview-url').data('url').replace(':id', bank_id);
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.get({
        url: url,
        success: function (response) {
            if (!response.success) {
                $('#withdraw-bank-preview').html('');
                return;
            }
            let bank = response.content;
            let rows = [
                [translate_js('bank_name'), bank.bank_name],
                [translate_js('holder_name'), bank.holder_name],
                [translate_js('account_no'), bank.account_no],
            ];
            if (bank.branch) rows.push([translate_js('branch'), bank.branch]);
            if (bank.swift_code) rows.push([translate_js('swift_code'), bank.swift_code]);
            if (bank.ifsc_code) rows.push([translate_js('ifsc_code'), bank.ifsc_code]);

            let html = rows.map(function (row) {
                return `<div class="d-flex justify-content-between"><span class="text-muted">${row[0]}</span><span>${row[1]}</span></div>`;
            }).join('');
            $('#withdraw-bank-preview').html(html);
        },
        error: function () {
            $('#withdraw-bank-preview').html('');
        }
    });
}

function translate_js(key) {
    return key.replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function withdraw_method_field(method_id){
    let url = $('#withdraw-method-url').data('url');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: url + "?method_id=" + method_id,
        data: {},
        processData: false,
        contentType: false,
        type: 'get',
        success: function (response) {
            let method_fields = response.content.method_fields;
            $("#method-filed__div").html("");
            method_fields.forEach((element, index) => {
                $("#method-filed__div").append(`
                    <div class="mt-3">
                        <label for="wr_num" class="fz-16 c1 mb-2" style="color: #5b6777 !important;">${element.input_name.replaceAll('_', ' ')}</label>
                        <input type="${element.input_type}" class="form-control" name="${element.input_name}" placeholder="${element.placeholder}" ${element.is_required === 1 ? 'required' : ''}>
                    </div>
                `);
            })
        },
        error: function () {

        }
    });
}

function earningStatistics(){
    $('.earn-statistics').on('click', function () {
        let value = $(this).attr('data-date-type');
        let url = $('#earn-statistics').data('action');
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                type: value
            },
            beforeSend: function () {
                $('#loading').fadeIn();
            },
            success: function (data) {
                $('#earn-statistics-div').empty().html(data.view);
                setMonthResponsiveDataForEarningStatistic();
                countLabel = parseInt($('input[name=earn_statistics_label_count]').val());
                earningStatisticsApexChart();
                earningStatistics();
            },
            complete: function () {
                $('#loading').fadeOut();
            }
        });
    });
}
earningStatistics();
function setMonthResponsiveDataForEarningStatistic() {
    $('.earn-statistics-option input:radio[name="statistics"]').change(function() {
        isMonthCheckedForEarningStatistic = $('input:radio[name="statistics"][value="MonthEarn"]').is(':checked');
    });
}
setMonthResponsiveDataForEarningStatistic();
let windowSize = getWindowSize();

function earningStatisticsApexChart(){
    let earnStatisticsData = $('#earn-statistics-data');
    const vendorEarn = earnStatisticsData.data('vendor-earn');
    const commissionEarn = earnStatisticsData.data('commission-earn');
    let label = earnStatisticsData.data('label');
    if (windowSize.width < 767){
        label =  getLabelData(label,countLabel,isMonthCheckedForEarningStatistic)
    }
    var options = {
        series: [
            {
                name: earnStatisticsData.data('vendor-text'),
                data: Object.values(vendorEarn)
            },
            {
                name: earnStatisticsData.data('commission-text'),
                data: Object.values(commissionEarn)
            }
        ],
        chart: {
            height: 386,
            type: 'line',
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 0.2
            },
            toolbar: {
                show: false
            }
        },
        yaxis: {
            labels: {
                offsetX: 0,
                formatter: function(value) {
                    return  currencySymbol+value
                }
            },
        },
        colors: ['#4FA7FF', '#82C662'],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: 'smooth',
        },
        grid: {
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            borderColor: '#CAD2FF',
            strokeDashArray: 5,
        },
        markers: {
            size: 1
        },
        theme: {
            mode: 'light',
        },
        xaxis: {
            categories: Object.values(label)
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            floating: false,
            offsetY: -10,
            offsetX: 0,
            itemMargin: {
                horizontal: 10,
                vertical: 10
            },
        },
        padding: {
            top: 0,
            right: 0,
            bottom: 200,
            left: 10
        },
    };
    var chart = new ApexCharts(document.getElementById("earning-apex-line-chart"), options);
    chart.render();
}
earningStatisticsApexChart();

function getLabelData(label,count,status){
    let mod = (count % 5);
    if (status === true ) {
        label.forEach((val, index) => {
            if (val % 5 === 0 || val === count) {
                label[index] = (val !== count && mod <= 1 && (count - mod === val) ? '' : val);
            }else{
                label[index]='';
            }
        });
    }else {
        label.forEach((val, index) => {
            label[index]=val.substring(0,3);
        });
    }
    return label;
}
