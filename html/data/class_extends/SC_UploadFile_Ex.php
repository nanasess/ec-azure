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

require_once CLASS_REALDIR . 'SC_UploadFile.php';

class SC_UploadFile_Ex extends SC_UploadFile {

    // ダウンロード一時ファイルを保存ディレクトリに移す
    function moveTempDownFile() {
        $cnt = 0;
        $objImage = new SC_Image_Ex($this->temp_dir);
        $objBlob = new SC_Helper_Blob_Ex();
        $containerName="downsave";
        foreach($this->keyname as $val) {
            if(isset($this->temp_file[$cnt]) && $this->temp_file[$cnt] != "") {

                $objBlob->saveBlob($containerName, $this->temp_file[$cnt], $this->temp_dir.$this->temp_file[$cnt]);
               
                // すでに保存ファイルがあった場合は削除する。
                if($objBlob->blobExists($containerName, $this->save_file[$cnt]))
                {
                    $objBlob->deleteBlob($containerName, $this->save_file[$cnt]);
                    //$objImage->deleteImage($this->save_file[$cnt], $this->save_dir);
                }
                unlink($this->temp_dir.$this->temp_file[$cnt]);
            }
            $cnt++;
        }
    }

    // フォームに渡す用のファイル情報配列を返す
    function getFormFileList($temp_url, $save_url, $real_size = false) {
        $objBlob = new SC_Helper_Blob_Ex();
        $containerName = "saveimage";
        $arrRet = array();
        $cnt = 0;
        foreach($this->keyname as $val) {
            if(isset($this->temp_file[$cnt]) && $this->temp_file[$cnt] != "") {
                // ファイルパスチェック(パスのスラッシュ/が連続しないようにする。)
                if(ereg("/$", $temp_url)) {
                    $arrRet[$val]['filepath'] = $temp_url . $this->temp_file[$cnt];
                } else {
                    $arrRet[$val]['filepath'] = $temp_url . "/" . $this->temp_file[$cnt];
                }
                $arrRet[$val]['real_filepath'] = $this->temp_dir . $this->temp_file[$cnt];
            } elseif (isset($this->save_file[$cnt]) && $this->save_file[$cnt] != "") {
                // ファイルパスチェック(パスのスラッシュ/が連続しないようにする。)
                if(ereg("/$", $save_url)) {
                    $arrRet[$val]['filepath'] = $save_url . $this->save_file[$cnt];
                } else {
                    $arrRet[$val]['filepath'] = $save_url . "/" . $this->save_file[$cnt];
                }
                //$arrRet[$val]['real_filepath'] = $this->save_dir . $this->save_file[$cnt];
                $arrRet[$val]['real_filepath'] = IMAGE_SAVE_URLPATH . $this->save_file[$cnt];
            }
            if(isset($arrRet[$val]['filepath']) && !empty($arrRet[$val]['filepath'])) {
                if($real_size){
//                    if(is_file($arrRet[$val]['real_filepath'])) {
                    if($objBlob->blobExists($containerName, $this->save_file[$cnt])) { //is_file($arrRet[$val]['real_filepath'])) {
                        list($width, $height) = getimagesize($arrRet[$val]['real_filepath']);
                    }
                    // ファイル横幅
                    $arrRet[$val]['width'] = $width;
                    // ファイル縦幅
                    $arrRet[$val]['height'] = $height;
                }else{
                    // ファイル横幅
                    $arrRet[$val]['width'] = $this->width[$cnt];
                    // ファイル縦幅
                    $arrRet[$val]['height'] = $this->height[$cnt];
                }
                // 表示名
                $arrRet[$val]['disp_name'] = $this->disp_name[$cnt];
            }
            $cnt++;
        }
        return $arrRet;
    }
}

?>
