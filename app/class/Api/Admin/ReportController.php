<?php

namespace Api\Admin;

use Core\Controller;

Class ReportController extends Controller {

    // app列表
    public function roiAction() {
        $whereArr = array();
        $dataArr = array();
        if (isset($_POST['advertiser_id']) && $_POST['advertiser_id']) {
            $whereArr[] = 'advertiser_id = ' . $_POST['advertiser_id'];
        }
        if (isset($_POST['ad_id']) && $_POST['ad_id']) {
            $whereArr[] = 'ad_id = ' . $_POST['ad_id'];
        }
        if (!$whereArr) {
            $whereArr[] = 'report_date > ' . date('Y-m-d', strtotime('-7 day'));
        }

        $where = 'WHERE ' . implode(' AND ', $whereArr);

        $sql = "SELECT COUNT(*) FROM r_report " . $where;
        $totalCount = $this->locator->db->getOne($sql, $dataArr);

        $list = array();
        if ($totalCount) {
            $sql = "SELECT * FROM r_report $where ORDER BY report_date DESC LIMIT " . $this->page;
            $list = $this->locator->db->getAll($sql, $dataArr);
        }
        return array(
            'totalCount' => (int) $totalCount,
            'list' => $list
        );
    }
}

