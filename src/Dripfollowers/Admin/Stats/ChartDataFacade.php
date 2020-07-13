<?php
namespace DripFollowers\Admin\Stats;

use DripFollowers\DripFollowers;
use DripFollowers\Common\PacksTypes;

class ChartDataFacade {
    
    public static function get_chart_data_extractor($type, DripFollowers $plugin){
        $data = array();
        if($type=='sales' || $type=='all'){
            $salesChart = new SalesProgressChart($plugin);
            $data['sales'] = $salesChart->generate_chart_data();
        } 
        if ($type=='earnings' || $type=='all'){
            $earningsChart = new EarningsProgressChart($plugin);
            $data['earnings'] = $earningsChart->generate_chart_data();
        }
        if ($type=='stackedEarnings' || $type=='all'){
            $earningsStackedChart = new EarningsStackedProgressChart($plugin);
            $data['stackedEarnings'] = $earningsStackedChart->generate_chart_data();
        }
        if ($type=='expressPie' || $type=='all'){
            $expressPieChart = new PackSharesChart($plugin, PacksTypes::Instant_Followers);
            $data['expressPie'] = $expressPieChart->generate_chart_data();
        }
        if ($type=='dailyPie' || $type=='all'){
            $dailyPieChart = new PackSharesChart($plugin, PacksTypes::Automatic_Followers);
            $data['dailyPie'] = $dailyPieChart->generate_chart_data();
        }
        if ($type=='likesPie' || $type=='all'){
            $likesPieChart = new PackSharesChart($plugin, PacksTypes::Instant_Likes);
            $data['likesPie'] = $likesPieChart->generate_chart_data();
        }
        if ($type=='autoLikesPie' || $type=='all'){
            $autoLikesPieChart = new PackSharesChart($plugin, PacksTypes::Automatic_Likes);
            $data['autoLikesPie'] = $autoLikesPieChart->generate_chart_data();
        }
        if ($type=='viewsPie' || $type=='all'){
            $viewsPieChart = new PackSharesChart($plugin, PacksTypes::Instant_Views);
            $data['viewsPie'] = $viewsPieChart->generate_chart_data();
        }
        if ($type=='expressEarningPie' || $type=='all'){
            $expressPieChart = new PackSharesChart($plugin, PacksTypes::Instant_Followers, array('earning'=>true));
            $data['expressEarningPie'] = $expressPieChart->generate_chart_data();
        }
        if ($type=='dailyEarningPie' || $type=='all'){
            $dailyPieChart = new PackSharesChart($plugin, PacksTypes::Automatic_Followers, array('earning'=>true));
            $data['dailyEarningPie'] = $dailyPieChart->generate_chart_data();
        }
        if ($type=='likesEarningPie' || $type=='all'){
            $likesPieChart = new PackSharesChart($plugin, PacksTypes::Instant_Likes, array('earning'=>true));
            $data['likesEarningPie'] = $likesPieChart->generate_chart_data();
        }
        if ($type=='autoLikesEarningPie' || $type=='all'){
            $autoLikesPieChart = new PackSharesChart($plugin, PacksTypes::Automatic_Likes, array('earning'=>true));
            $data['autoLikesEarningPie'] = $autoLikesPieChart->generate_chart_data();
        }
        if ($type=='viewsEarningPie' || $type=='all'){
            $viewsPieChart = new PackSharesChart($plugin, PacksTypes::Instant_Views, array('earning'=>true));
            $data['viewsEarningPie'] = $viewsPieChart->generate_chart_data();
        }
        // var_dump($data);
        return $data;
    }
    
}