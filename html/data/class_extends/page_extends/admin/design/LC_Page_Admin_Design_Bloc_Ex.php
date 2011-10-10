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
require_once CLASS_REALDIR . 'pages/admin/design/LC_Page_Admin_Design_Bloc.php';

/**
 * ブロック編集 のページクラス(拡張).
 *
 * LC_Page_Admin_Design_Bloc をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Design_Bloc_Ex extends LC_Page_Admin_Design_Bloc {

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        parent::process();
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

    /**
     * ブロックのテンプレートを取得する.
     *
     * @param integer $device_type_id 端末種別ID
     * @param integer $bloc_id ブロックID
     * @param SC_Helper_PageLayout $objLayout SC_Helper_PageLayout インスタンス
     * @return array ブロック情報の配列
     */
    function getBlocTemplate($device_type_id, $bloc_id, &$objLayout) {
        $arrBloc = $objLayout->getBlocs($device_type_id, 'bloc_id = ?', array($bloc_id));
        if (SC_Utils_Ex::isAbsoluteRealPath($arrBloc[0]['tpl_path'])) {
            $tpl_path = $arrBloc[0]['tpl_path'];
        } else {
            $tpl_path = SC_Helper_PageLayout_Ex::getTemplatePath($device_type_id) . BLOC_DIR . $arrBloc[0]['tpl_path'];
        }

        $objBlob = new SC_Helper_Blob_Ex();
        $containerName = $objBlob->getTemplateContainerName($device_type_id);
        if ($objBlob->blobExists($containerName, $arrBloc[0]['filename'].".tpl")) {
            $arrBloc[0]['bloc_html'] = $objBlob->getBlobData($containerName, BLOC_DIR . $arrBloc[0]['filename']. ".tpl");
        }
        return $arrBloc[0];
    }

    /**
     * 登録を実行する.
     *
     * ファイルの作成に失敗した場合は, エラーメッセージを出力し,
     * データベースをロールバックする.
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param SC_Helper_PageLayout $objLayout SC_Helper_PageLayout インスタンス
     * @return integer|boolean 登録が成功した場合, 登録したブロックID;
     *                         失敗した場合 false
     */
    function doRegister(&$objFormParam, &$objLayout) {
        $arrParams = $objFormParam->getHashArray();

        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();

        // blod_id が空の場合は新規登録
        $is_new = SC_Utils_Ex::isBlank($arrParams['bloc_id']);
        $bloc_dir = $objLayout->getTemplatePath($arrParams['device_type_id']) . BLOC_DIR;
        // 既存データの重複チェック
        if (!$is_new) {
            $arrExists = $objLayout->getBlocs($arrParams['device_type_id'], 'bloc_id = ?', array($arrParams['bloc_id']));

            // 既存のファイルが存在する場合は削除しておく
            $exists_file = $bloc_dir . $arrExists[0]['filename'] . '.tpl';
            if (file_exists($exists_file)) {
                unlink($exists_file);
            }
        }

        $table = 'dtb_bloc';
        $arrValues = $objQuery->extractOnlyColsOf($table, $arrParams);
        $arrValues['tpl_path'] = $arrParams['filename'] . '.tpl';
        $arrValues['update_date'] = 'CURRENT_TIMESTAMP';

        $objBlob = new SC_Helper_Blob_Ex();
        $containerName = $objBlob->getTemplateContainerName($arrParams['device_type_id']);

        // 新規登録
        if ($is_new || SC_Utils_Ex::isBlank($arrExists)) {
            $objQuery->setOrder('');
            $arrValues['bloc_id'] = 1 + $objQuery->max('bloc_id', $table, 'device_type_id = ?',
                                                       array($arrValues['device_type_id']));
            $arrValues['create_date'] = 'CURRENT_TIMESTAMP';
            $objQuery->insert($table, $arrValues);
        }
        // 更新
        else {
            $objQuery->update($table, $arrValues, 'bloc_id = ? AND device_type_id = ?',
                              array($arrValues['bloc_id'], $arrValues['device_type_id']));
        }

        $bloc_path = $bloc_dir . $arrValues['tpl_path'];

        if (!$objBlob->putBlobData($containerName, BLOC_DIR . $arrValues['tpl_path'], $arrParams['bloc_html'])) {
            $this->arrErr['err'] = '※ ブロックの書き込みに失敗しました<br />';
            $objQuery->rollback();
            return false;
        }

        $objQuery->commit();
        return $arrValues['bloc_id'];
    }

}
?>
