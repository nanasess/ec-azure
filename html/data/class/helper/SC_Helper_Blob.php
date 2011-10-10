<?php
 /*
  * Copyright(c) 2000-2011 LOCKON CO.,LTD. All Rights Reserved.
  *
  * http://www.lockon.co.jp/
  */

 /**
  * Windows Azure Storage Blob関連のヘルパークラス.
  *
  * @package Helper
  * @author LOCKON CO.,LTD.
  * @version $Id: SC_Helper_Blob.php 21194 2011-08-17 01:39:55Z Seasoft $
  */
  
require_once DATA_REALDIR . 'module/Microsoft/WindowsAzure/Storage.php';
require_once DATA_REALDIR . 'module/Microsoft/WindowsAzure/Storage/Blob.php';  
  
class SC_Helper_Blob {

    var $objBlobClient;

     // }}}
     // {{{ constructor

     /**
      * デフォルトコンストラクタ.
      *
      * Blobを利用する関数
      */
    function SC_Helper_Blob() {
        $this->objBlobClient = $this->createBlobStorageClient();
     }

     // }}}
     // {{{ functions

    function createBlobStorageClient() {
        $blobStorageClient = new Microsoft_WindowsAzure_Storage_Blob(
            Microsoft_WindowsAzure_Storage::URL_CLOUD_BLOB,
            CLOUD_STORAGE_ACCOUNT,
            CLOUD_STORAGE_KEY,
            false);
            //Microsoft_WindowsAzure_RetryPolicy::retryN(10, 250));

        return $blobStorageClient;
    }

    /**
    * コンテナを作成する
    * @param $containerName コンテナ名
    * @return void
    */
    function createContainer($containerName, $public=true){
        $mess = "";
        if ($this->containerExists($containerName)) {
            $mess .= $containerName . ":コンテナが存在します\n";
        } else {
            $result = $this->objBlobClient->createContainer($containerName);
            if($public) {
                $this->objBlobClient->setContainerAcl($containerName, Microsoft_WindowsAzure_Storage_Blob::ACL_PUBLIC);
            }
        }
        return $mess;
    }
    
    /**
    * blobにファイルを保存する
    * @param $containerName
    * @param $filename
    * @param $fromFilepath
    */
    function saveBlob($containerName, $filename, $fromFilepath){
        $mess = "";

        if ($this->blobExists($containerName, $filename)) {
            $mess.= $containerName ."/" . $filename . "：ファイルが存在します\n";
        } else {
            $result = $this->objBlobClient->putBlob($containerName, $filename, $fromFilepath);
            $mess = $containerName . "/". $filename . "：コピー成功\n";
        }
        return $mess;
    }
    /**
     * blobにファイルを作成する
     * @param <type> $containerName
     * @param <type> $blobName
     * @param <type> $data 
     */
    function putBlobData($containerName, $blobName, $data) {
        return $this->objBlobClient->putBlobData($containerName, $blobName, $data);
    }

    /**
     * blobのファイルを削除する
     * @param $containerName
     * @param $filename
     */
    function deleteBlob($containerName, $filename) {
        if ($this->blobExists($containerName, $filename)) {
            $this->objBlobClient->deleteBlob($containerName, $filename);
        }
    }
    
    /**
     * blobにファイルが存在するか確認する
     * @param <type> $containerName
     * @param <type> $filename
     * @return <type> 
     */
    function blobExists($containerName, $filename) {
        return $this->objBlobClient->blobExists($containerName, $filename);
    }


    /**
     * デバイスに応じてテンプレートのコンテナを指定する
     * @param <type> $device_type_id
     * @return string
     */
    function getTemplateContainerName($device_type_id) {
        $containerName = DEFAULT_TEMPLATE_NAME;
        switch ($device_type_id) {
            case DEVICE_TYPE_PC:
                $containerName = "template". TEMPLATE_NAME;
                break;
            case DEVICE_TYPE_MOBILE:
                $containerName = "template". MOBILE_TEMPLATE_NAME;
                break;
            case DEVICE_TYPE_SMARTPHONE:
                $containerName = "template". SMARTPHONE_TEMPLATE_NAME;
                break;
        }

        return $containerName;
    }

    /**
     * Blobをローカルに取得する
     * @param <type> $containerName
     * @param <type> $filename
     * @param <type> $localfilename
     * @return <type>
     */
    function getBlob($containerName, $filename, $localfilename) {
       return $this->objBlobClient->getBlob($containerName, $filename, $localfilename);
    }

    /**
     * Blobのデータを取得する
     * @param <type> $containerName
     * @param <type> $filename
     */
    function getBlobData($containerName, $filename) {
        return $this->objBlobClient->getBlobData($containerName, $filename);
    }


    /**
     * コンテナが存在するか確認する
     * @param <type> $containerName
     * @return <type>
     */
    function containerExists($containerName) {
        return $this->objBlobClient->containerExists($containerName);
    }
    
    /**
    * ディレクトリを回帰的に保存する
    * @param $containerName
    * @param $dirPath
    * @param $prefix
    * @return 
    */
    function putBlobDir($containerName, $dirPath, $prefix = ""){

        if(!is_dir($dirPath)){
            return false;
        }
        $mess = "";
        $mod= stat($dirPath);
        $fileArray=glob( $dirPath."*" );
        if (is_array($fileArray)) {
            foreach( $fileArray as $key => $data_ ){
                mb_ereg("^(.*[\/])(.*)",$data_, $matches);
                $data=$matches[2];
                if( is_dir( $data_ ) ){
                    $mess .= $this->putBlobDir( $containerName, $data_.'/', $prefix . "/");
                }else{
                    $mess .= $this->saveBlob($containerName, $prefix . $data, $dirPath.$data);
                }
            }
        }
        return $mess;
    }

    /**
     * Blobをダウンロード
     * @param <type> $containerName
     * @param <type> $filename
     * @param string $down_name
     */
    function downloadBlob($containerName, $filename, $down_name="") {
        if($this->objBlobClient->blobExists($containerName, $filename)) {
            echo ($this->objBlobClient->getBlobData($containerName, $filename));
            exit;
        } else {
            return false;
        }
    }

    /**
     * コンテナのリストを返す
     *
     * @return array $arrContainers コンテナ配列
     */
    function listContainers()
    {
        $arrContainers = $this->objBlobClient->listContainers();
        return $arrContainers;
    }
}
?>
