jQuery(function ($) {

	Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
	    return {
	        radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
	        stops: [
	            [0, color],
	            [1, Highcharts.Color(color).brighten(0.1).get('rgb')]
	        ]
	    };
	});
	
	var defaultPieConfig = {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        tooltip: {
        	formatter: function () {
                return this.point.name + ': <b>' + this.y + ' Orders</b>';
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage, 2) +' %';
                    }
                }
            }
        },
        credits: {
            enabled: false
        }
	};
	
	var getPackPieChartConfig = function(config){
		
		var pieConfig = {
			title: {
	            text: config.title
	        },
	        series: [{
	            type: 'pie',
	            name: config.series_name,
	            data: config.series_data
	        }]
		}
		
		return $.extend({}, pieConfig, defaultPieConfig);
		
	}
	
	var getEarningPackPieChartConfig = function(config){
		
		var earningTooltipFormatter = { 
				tooltip: {
		        	formatter: function () {
		                return this.point.name + ': <b>' + this.y + ' $</b>';
		            }
				}
		};
		var pieConfig = getPackPieChartConfig(config);
		
		return $.extend({}, pieConfig, earningTooltipFormatter);
		
	}

	console.log(data);
	
	var expressPieConfig = getPackPieChartConfig(data.expressPie);
    $('#express-pie').highcharts(expressPieConfig);
    
    var dailyPieConfig = getPackPieChartConfig(data.dailyPie);
    $('#daily-pie').highcharts(dailyPieConfig);
    
    var likesPieConfig = getPackPieChartConfig(data.likesPie);
    $('#likes-pie').highcharts(likesPieConfig);

    var autoLikesPieConfig = getPackPieChartConfig(data.autoLikesPie);
    $('#auto-likes-pie').highcharts(autoLikesPieConfig);

    var viewsPieConfig = getPackPieChartConfig(data.viewsPie);
    $('#views-pie').highcharts(viewsPieConfig);
    
    var expressEarningPieConfig = getEarningPackPieChartConfig(data.expressEarningPie);
    $('#express-earning-pie').highcharts(expressEarningPieConfig);
    
    var dailyEarningPieConfig = getEarningPackPieChartConfig(data.dailyEarningPie);
    $('#daily-earning-pie').highcharts(dailyEarningPieConfig);
    
    var likesEarningPieConfig = getEarningPackPieChartConfig(data.likesEarningPie);
    $('#likes-earning-pie').highcharts(likesEarningPieConfig);

    var autoLikesEarningPieConfig = getEarningPackPieChartConfig(data.autoLikesEarningPie);
    $('#auto-likes-earning-pie').highcharts(autoLikesEarningPieConfig);

    var viewsEarningPieConfig = getEarningPackPieChartConfig(data.viewsEarningPie);
    $('#views-earning-pie').highcharts(viewsEarningPieConfig);


});