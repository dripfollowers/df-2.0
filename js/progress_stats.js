jQuery(function ($) {
	
	var chartDefaultConfig = {
			legend: {
                align: 'right',
                verticalAlign: 'top',
                y: 5,
                floating: true,
                backgroundColor: 'white',
                borderColor: '#CCC',
                borderWidth: 1,
                shadow: false
            },
	        credits: {
	            enabled: false
	        }
	};
	
	
	var getDataSeries = function(data, type){
		var series = [];
		for (var prop in data[type].series) {
			var obj = {
			  name: prop,
	  		  data: data[type].series[prop]
			}
			series.push(obj);
	    }
		return series;
	}
	
	var getLineChartConfig = function(config, series){
		var lineChartConfig = {
	        title: {
	            text: config.title
	        },
	        xAxis: {
	            categories: config.x_axis_categories
	        },
	        yAxis: {
	            title: {
	                text: config.y_axis_title
	            },
	            min: 0
	        },
	        tooltip: {
	            valueSuffix: config.tooltip_value_suffix
	        },
	        series: series
	    }
		$.extend(lineChartConfig, chartDefaultConfig);
		return lineChartConfig;
	}
	
	var earningsSeries = getDataSeries(data, 'earnings');
	var earningsConfig = getLineChartConfig(data.earnings, earningsSeries);
	
	var salesSeries = getDataSeries(data, 'sales');
	var salesConfig = getLineChartConfig(data.sales, salesSeries);
	
    $('#earnings').highcharts(earningsConfig);
    $('#sales').highcharts(salesConfig);
    
    //Stacked Earnings Chart
	var getEarningsStackedChartConfig = function(stackedConfig, lineConfig, series){
		
		var stackedChartConfig = {
            chart: {
                type: 'column'
            },
            title: {
                text: stackedConfig.title
            },
            xAxis: {
                categories: lineConfig.x_axis_categories
            },
            yAxis: {
                min: 0,
                title: {
                    text: stackedConfig.y_axis_title
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.x +'</b><br/>'+
                        this.series.name +': '+ this.y +'<br/>'+
                        'Total: '+ this.point.stackTotal;
                }
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true,
                        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                        style: {
                            textShadow: '0 0 3px black, 0 0 3px black'
                        }
                    }
                }
            },
            series: earningsSeries
		};
		
		var extraSpecificConf = {
				legend: {
	                layout: 'vertical',
	                align: 'right',
	                verticalAlign: 'middle',
	                borderWidth: 0
            }
		};
		$.extend(stackedChartConfig, chartDefaultConfig, extraSpecificConf);
		return stackedChartConfig;
	}
	var stackedEarningsConfig = getEarningsStackedChartConfig(data.stackedEarnings, data.earnings, earningsSeries);
	    
    $('#stacked-earnings').highcharts(stackedEarningsConfig);
    
    
    
});