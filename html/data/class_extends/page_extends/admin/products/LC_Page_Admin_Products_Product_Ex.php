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
require_once CLASS_REALDIR . 'pages/admin/products/LC_Page_Admin_Products_Product.php';

/**
 * 商品登録 のページクラス(拡張).
 *
 * LC_Page_Admin_Products_Product をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Admin_Products_Product_Ex.php 20764 2011-03-22 06:26:40Z nanasess $
 */
class LC_Page_Admin_Products_Product_Ex extends LC_Page_Admin_Products_Product {

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
     * アップロードファイルをBlobに保存する
     *
     * @param object $objUpFile SC_UploadFileインスタンス
     * @param object $objDownFile SC_UploadFileインスタンス
     * @param integer $product_id 商品ID
     * @return void
     */
    function lfSaveUploadFiles(&$objUpFile, &$objDownFile, $product_id) {
        // TODO: SC_UploadFile::moveTempFileの画像削除条件見直し要
        $objImage = new SC_Image_Ex($objUpFile->temp_dir);
        $objBlob = new SC_Helper_Blob_Ex();
        $arrKeyName = $objUpFile->keyname;
        $arrTempFile = $objUpFile->temp_file;
        $arrSaveFile = $objUpFile->save_file;
        $containerName = "saveimage";
        $arrImageKey = array();
        foreach($arrTempFile as $key => $temp_file) {
            if($temp_file) {
                $objBlob->saveBlob($containerName, $temp_file, $objImage->tmp_dir.$temp_file);
                unlink($objImage->tmp_dir.$temp_file);
                $arrImageKey[] = $arrKeyName[$key];
                if(!empty($arrSaveFile[$key])
                        && !$this->lfHasSameProductImage($product_id, $arrImageKey, $arrSaveFile[$key])
                        && !in_array($temp_file, $arrSaveFile)) {
                    $objBlob->deleteBlob($containerName, $arrSaveFile[$key]);
                }
            }
        }
        $objDownFile->moveTempDownFile();
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }
}
?>
