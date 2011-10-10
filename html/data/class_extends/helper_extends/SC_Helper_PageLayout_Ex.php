<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2011 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// {{{ requires
require_once CLASS_REALDIR . 'helper/SC_Helper_PageLayout.php';

/**
 * Webページのレイアウト情報を制御するヘルパークラス(拡張).
 *
 * SC_Helper_PageLayout をカスタマイズする場合は, このクラスを編集する.
 *
 * @package Helper
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class SC_Helper_PageLayout_Ex extends SC_Helper_PageLayout {
    
    /**
     * ページのレイアウト情報を取得し, 設定する.
     *
     * 現在の URL に応じたページのレイアウト情報を取得し, LC_Page インスタンスに
     * 設定する.
     *
     * @access public
     * @param LC_Page $objPage LC_Page インスタンス
     * @param boolean $preview プレビュー表示の場合 true
     * @param string $url ページのURL($_SERVER['PHP_SELF'] の情報)
     * @param integer $device_type_id 端末種別ID
     * @return void
     */
    function sfGetPageLayout(&$objPage, $preview = false, $url = "", $device_type_id = DEVICE_TYPE_PC) {

        // URLを元にページ情報を取得
        if ($preview === false) {
            $url = preg_replace('|^' . preg_quote(ROOT_URLPATH) . '|', '', $url);
            $arrPageData = $this->getPageProperties($device_type_id, null, 'url = ?', array($url));
        }
        // プレビューの場合は, プレビュー用のデータを取得
        else {
            $arrPageData = $this->getPageProperties($device_type_id, 0);
        }

        $objPage->tpl_mainpage = $this->getTemplatePath($device_type_id) . $arrPageData[0]['filename'] . ".tpl";
        $objPage->arrPageLayout =& $arrPageData[0];

        $objBlob = new SC_Helper_Blob_Ex();
        $containerName = $objBlob->getTemplateContainerName($device_type_id);
        /** pageのテンプレートがなければBlobから取得する */
        if(!is_file($objPage->tpl_mainpage)) {
            $objBlob->getBlob($containerName, $arrPageData[0]['filename'] . ".tpl", $objPage->tpl_mainpage);
        }
        // ページタイトルを設定
        if (SC_Utils_Ex::isBlank($objPage->tpl_title)) {
            $objPage->tpl_title = $objPage->arrPageLayout['page_name'];
        }

        // 該当ページのブロックを取得し, 配置する
        $masterData = new SC_DB_MasterData();
        $arrTarget = $masterData->getMasterData("mtb_target");
        $arrBlocs = $this->getBlocPositions($device_type_id, $objPage->arrPageLayout['page_id']);
        // php_path, tpl_path が存在するものを, 各ターゲットに配置
        foreach (array_keys($arrTarget) as $target_id) {
            foreach ($arrBlocs as $arrBloc) {
                if ($arrBloc['target_id'] != $target_id) {
                    continue;
                }
                /* Blobからすでにファイルを取得している場合*/
                if (is_file($arrBloc['tpl_path'])) {
                    $objPage->arrPageLayout[$arrTarget[$target_id]][] = $arrBloc;
                } else if ($objBlob->blobExists($containerName, $arrBloc['filename'].".tpl")) {
                    $localFilename = $arrBloc['tpl_path'];
                    $objBlob->getBlob($containerName, $arrBloc['filename']. ".tpl", $localFilename);
                    $objPage->arrPageLayout[$arrTarget[$target_id]][] = $arrBloc;
                } else {
                    $error = "ブロックが見つかりません\n"
                        . "tpl_path: " . $arrBloc['tpl_path'] . "\n"
                        . "php_path: " . $arrBloc['php_path'];
                    GC_Utils_Ex::gfPrintLog($error);
                }
            }
        }
        // カラム数を取得する
        $objPage->tpl_column_num = $this->getColumnNum($objPage->arrPageLayout);
    }
}
?>
