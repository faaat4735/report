<?php

require_once '../init.inc.php';
// 获取topon变现数据

$startDate = strtotime('-2 day');
$endDate =  max(strtotime('-30 day'), strtotime('20200815'));


// 获取巨量引擎推广数据
$topon = new \Core\Topon();
$i = 1;
while (true) {
    $reportDate = date('Y-m-d',$startDate);

    $sql = 'SELECT ad_id, convert_cost, `convert`, advertiser_id, campaign_id FROM r_ocean WHERE report_date = ?';
    $reportList = $locator->db->getAll($sql, $reportDate);
    $roi = 'roi_' . $i;
    $ltv = 'ltv_day_' . $i;
    foreach ($reportList as $reportInfo) {
        $sql = 'SELECT new_user, ' . $ltv . ' FROM r_topon WHERE report_date = ? AND ad_id = ?';
        $toponInfo = $locator->db->getRow($sql, $reportDate, $reportInfo['ad_id']);
        if ($toponInfo) {
            $roiVal = $reportInfo['convert_cost'] ? ($toponInfo[$ltv] / $reportInfo['convert_cost']) : 0;

            $sql = 'REPLACE INTO r_report SET report_date = ?, advertiser_id = ?, campaign_id = ?, ad_id = ?, ' . $roi . ' = ?, new_user_topon = ?, new_user_ocean = ?';
            $locator->db->exec($sql, $reportDate, $reportInfo['advertiser_id'], $reportInfo['campaign_id'], $reportInfo['ad_id'], $roiVal, $toponInfo['new_user'], $reportInfo['convert']);
        }
    }
    $startDate = strtotime('-1 day', $startDate);
    if ($startDate <= $endDate) {
        break;
    }
    $i++;
}
exit;


// 聚合 推广和变现数据
