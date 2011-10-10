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
require_once CLASS_REALDIR . 'pages/admin/design/LC_Page_Admin_Design_MainEdit.php';

/**
 * メイン編集 のページクラス(拡張).
 *
 * LC_Page_Admin_Design_MainEdit をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Design_MainEdit_Ex extends LC_Page_Admin_Design_MainEdit {

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
     * ページデータを取得する.
     *
     * @param integer $device_type_id 端末種別ID
     * @param integer $page_id ページID
     * @param SC_Helper_PageLayout $objLayout SC_Helper_PageLayout インスタンス
     * @return array ページデータの配列
     */
    function getTplMainpage($device_type_id, $page_id, &$objLayout){
        $arrPageData = $objLayout->getPageProperties($device_type_id, $page_id);
        $objBlob = new SC_Helper_Blob_Ex();
        $containerName = $objBlob->getTemplateContainerName($device_type_id);

        $templatePath = $objLayout->getTemplatePath($device_type_id);
        $filename = $templatePath . $arrPageData[0]['filename'] . ".tpl";
        if ($objBlob->blobExists($containerName, $arrPageData[0]['filename'] . ".tpl")) {
            $arrPageData[0]['tpl_data'] = $objBlob->getBlobData($containerName, $arrPageData[0]['filename']. ".tpl");
        }
        // ファイル名を画面表示用に加工しておく
        $arrPageData[0]['filename'] = preg_replace('|^' . preg_quote(USER_DIR) . '|', '', $arrPageData[0]['filename']);
        return $arrPageData[0];
    }

    /**
     * 登録を実行する.
     *
     * ファイルの作成に失敗した場合は, エラーメッセージを出力し,
     * データベースをロールバックする.
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param SC_Helper_PageLayout $objLayout SC_Helper_PageLayout インスタンス
     * @return integer|boolean 登録が成功した場合, 登録したページID;
     *                         失敗した場合 false
     */
    function doRegister(&$objFormParam, &$objLayout) {
        $filename = $objFormParam->getValue('filename');
        $arrParams['device_type_id'] = $objFormParam->getValue('device_type_id');
        $arrParams['page_id'] = $objFormParam->getValue('page_id');
        $arrParams['header_chk'] = intval($objFormParam->getValue('header_chk')) === 1 ? 1 : 2;
        $arrParams['footer_chk'] = intval($objFormParam->getValue('footer_chk')) === 1 ? 1 : 2;
        $arrParams['tpl_data'] = $objFormParam->getValue('tpl_data');
        $arrParams['page_name'] = $objFormParam->getValue('page_name');
        $arrParams['url'] = USER_DIR . $filename . '.php';
        $arrParams['filename'] = USER_DIR . $filename;

        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();

        $page_id = $this->registerPage($arrParams, $objLayout);

        $objBlob = new SC_Helper_Blob_Ex();
        $containerName = $objBlob->getTemplateContainerName($arrParams['device_type_id']);

        /*
         * 新規登録時
         * or 編集可能な既存ページ編集時かつ, PHP ファイルが存在しない場合に,
         * PHP ファイルを作成する.
         */
        if (SC_Utils_Ex::isBlank($arrParams['page_id'])
            || $objLayout->isEditablePage($arrParams['device_type_id'], $arrParams['page_id'])) {
            if (!$this->createPHPFile($filename)) {
                $this->arrErr['err'] = '※ PHPファイルの作成に失敗しました<br />';
                $objQuery->rollback();
                return false;
            }
            // 新規登録時のみ $page_id を代入
            $arrParams['page_id'] = $page_id;
        }

        if ($objLayout->isEditablePage($arrParams['device_type_id'], $page_id)) {
            $tpl_path = $objLayout->getTemplatePath($arrParams['device_type_id']) . $arrParams['filename'] . '.tpl';
        } else {
            $tpl_path = $objLayout->getTemplatePath($arrParams['device_type_id']) . $filename . '.tpl';
        }

        if (!$objBlob->putBlobData($containerName, $arrParams['filename'] . '.tpl', $arrParams['tpl_data'])) {
            $this->arrErr['err'] = '※ TPLファイルの書き込みに失敗しました<br />';
            $objQuery->rollback();
            return false;
        }
        //既存のTPLが存在する場合は削除しておく
        if (file_exists($tpl_path)) {
                unlink($tpl_path);
        }

        $objQuery->commit();
        return $arrParams['page_id'];
    }
}
?>
