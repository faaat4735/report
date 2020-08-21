<?php

require_once __DIR__ . '/../init.inc.php';

$date = $_GET['date'] ?? date('Y-m-d', strtotime('-1 day'));

// 获取巨量引擎推广数据
$ocean = new \Core\Ocean();
// 获取广告主列表
$advertiserList = $ocean->getAdvertiserList();
//var_dump($advertiserList);
// 保存巨量引擎推广数据
$sql = 'INSERT INTO r_ocean (`advertiser_id`, `campaign_id`, `ad_id`, `cost`, `show`, `avg_show_cost`, `click`, `avg_click_cost`, `ctr`, `convert`, `convert_cost`, `convert_rate`, `report_date`) VALUES ';
foreach ($advertiserList as $adClass) {
    $advertiserData = $ocean->getReport($adClass->advertiser_id, $date);
    foreach ($advertiserData as $dataInfo) {
        if ($dataInfo->cost > 0) {
            $temp = array($adClass->advertiser_id, $dataInfo->campaign_id, $dataInfo->ad_id, $dataInfo->cost, $dataInfo->show, $dataInfo->avg_show_cost, $dataInfo->click, $dataInfo->avg_click_cost, $dataInfo->ctr, $dataInfo->convert, $dataInfo->convert_cost, $dataInfo->convert_rate);
            //json_encode($dataInfo)
            $sql .= '(' . implode(', ', $temp) . ', "' . $date . '"),';
        }
    }
}
if ($advertiserList) {
    $sql = rtrim($sql, ',');
//    echo $sql;
    $locator->db->exec($sql);
}
// 聚合 推广和变现数据
