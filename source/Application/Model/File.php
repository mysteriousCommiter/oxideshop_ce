<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Application\Model;

use oxField;
use oxRegistry;
use oxDb;
use oxException;

/**
 * Article files manager.
 *
 */
class File extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    /**
     * No active user exception code.
     */
    const NO_USER = 2;

    /**
     * Object core table name
     *
     * @var string
     */
    protected $_sCoreTable = 'oxfiles';

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'oxfile';

    /**
     * Stores relative oxFile path from configs 'sDownloadsDir'
     *
     * @var string
     */
    protected $_sRelativeFilePath = null;

    /**
     * Paid order indicator
     *
     * @var bool
     */
    protected $_blIsPaid = null;

    /**
     * Full URL where article could be downloaded from.
     * Is set to false in case download is not available for current user
     *
     * @var string|bool
     */
    protected $_sDownloadLink = null;

    /**
     * Has valid downloads indicator
     *
     * @var bool
     */
    protected $_blHasValidDownloads = null;

    /**
     * Default manual upload dir located within general file dir
     *
     * @var string
     */
    protected $_sManualUploadDir = "uploads";

    /**
     * Initialises the instance
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    /**
     * Sets oxefile__oxstorehash with file hash.
     * Moves file to desired location and change its access rights.
     *
     * @param int $sFileIndex File index
     *
     * @throws oxException Throws exception if file wasn't moved or if rights wasn't changed.
     */
    public function processFile($sFileIndex)
    {
        $aFileInfo = $this->getConfig()->getUploadedFile($sFileIndex);

        $this->_checkArticleFile($aFileInfo);

        $sFileHash = $this->_getFileHash($aFileInfo['tmp_name']);
        $this->oxfiles__oxstorehash = new \OxidEsales\Eshop\Core\Field($sFileHash, \OxidEsales\Eshop\Core\Field::T_RAW);
        $sUploadTo = $this->getStoreLocation();

        if (!$this->_uploadFile($aFileInfo['tmp_name'], $sUploadTo)) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('EXCEPTION_COULDNOTWRITETOFILE');
        }
    }
    /**
     * @deprecated use self::checkArticleFile instead
     */
    protected function _checkArticleFile($aFileInfo) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->checkArticleFile($aFileInfo);
    }

    /**
     * Checks if given file is valid upload file
     *
     * @param array $aFileInfo File info array
     *
     * @throws oxException Throws exception if file wasn't uploaded successfully.
     */
    protected function checkArticleFile($aFileInfo)
    {
        //checking params
        if (!isset($aFileInfo['name']) || !isset($aFileInfo['tmp_name'])) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('EXCEPTION_NOFILE');
        }

        // error uploading file ?
        if (isset($aFileInfo['error']) && $aFileInfo['error']) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('EXCEPTION_FILEUPLOADERROR_' . ((int) $aFileInfo['error']));
        }
    }
    /**
     * @deprecated use self::getBaseDownloadDirPath instead
     */
    protected function _getBaseDownloadDirPath() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->getBaseDownloadDirPath();
    }

    /**
     * Return full path of root dir where download files are stored
     *
     * @return string
     */
    protected function getBaseDownloadDirPath()
    {
        $sConfigValue = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sDownloadsDir');

        //Unix full path is set
        if ($sConfigValue && $sConfigValue[0] == DIRECTORY_SEPARATOR) {
            return $sConfigValue;
        }

        //relative path is set
        if ($sConfigValue) {
            $sPath = getShopBasePath() . DIRECTORY_SEPARATOR . $sConfigValue;

            return $sPath;
        }

        //no path is set
        $sPath = getShopBasePath() . "/out/downloads/";

        return $sPath;
    }

    /**
     * Returns full filesystem path where files are stored.
     * Make sure that object oxfiles__oxstorehash or oxfiles__oxfilename
     * attribute is set before calling this method
     *
     * @return string
     */
    public function getStoreLocation()
    {
        $sPath = $this->_getBaseDownloadDirPath();
        $sPath .= DIRECTORY_SEPARATOR . $this->_getFileLocation();

        return $sPath;
    }

    /**
     * Return true if file is under download folder.
     * Return false if file is above download folder or if file does not exist.
     *
     * @return bool
     */
    public function isUnderDownloadFolder()
    {
        $storageLocation = realpath($this->getStoreLocation());

        if ($storageLocation === false) {
            return false;
        }

        $downloadFolder = realpath($this->_getBaseDownloadDirPath());

        return strpos($storageLocation, $downloadFolder) !== false;
    }
    /**
     * @deprecated use self::getFileLocation instead
     */
    protected function _getFileLocation() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->getFileLocation();
    }

    /**
     * Returns relative file path from oxConfig 'sDownloadsDir' variable.
     *
     * @return string
     */
    protected function getFileLocation()
    {
        $this->_sRelativeFilePath = '';
        $sFileHash = $this->oxfiles__oxstorehash->value;
        $sFileName = $this->oxfiles__oxfilename->value;

        //security check for demo shops
        if ($this->getConfig()->isDemoShop()) {
            $sFileName = basename($sFileName);
        }

        if ($this->isUploaded()) {
            $this->_sRelativeFilePath = $this->_getHashedFileDir($sFileHash);
            $this->_sRelativeFilePath .= DIRECTORY_SEPARATOR . $sFileHash;
        } else {
            $this->_sRelativeFilePath = DIRECTORY_SEPARATOR . $this->_sManualUploadDir . DIRECTORY_SEPARATOR . $sFileName;
        }

        return $this->_sRelativeFilePath;
    }
    /**
     * @deprecated use self::getHashedFileDir instead
     */
    protected function _getHashedFileDir($sFileHash) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->getHashedFileDir($sFileHash);
    }

    /**
     * Returns relative sub dir of oxconfig 'sDownloadsDir' of
     * required file from supplied $sFileHash parameter.
     * Creates dir in case it does not exist.
     *
     * @param string $sFileHash File hash value
     *
     * @return string
     */
    protected function getHashedFileDir($sFileHash)
    {
        $sDir = substr($sFileHash, 0, 2);
        $sAbsDir = $this->_getBaseDownloadDirPath() . DIRECTORY_SEPARATOR . $sDir;

        if (!is_dir($sAbsDir)) {
            mkdir($sAbsDir, 0755);
        }

        return $sDir;
    }
    /**
     * @deprecated use self::getFileHash instead
     */
    protected function _getFileHash($sFileName) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->getFileHash($sFileName);
    }

    /**
     * Calculates file hash.
     * Currently MD5 is used.
     *
     * @param string $sFileName File name values
     *
     * @return string
     */
    protected function getFileHash($sFileName)
    {
        return md5_file($sFileName);
    }
    /**
     * @deprecated use self::uploadFile instead
     */
    protected function _uploadFile($sSource, $sTarget) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->uploadFile($sSource, $sTarget);
    }

    /**
     * Moves file from source to target and changes file mode.
     * Returns true on success.
     *
     * @param string $sSource Source filename
     * @param string $sTarget Target filename
     *
     * @return bool
     */
    protected function uploadFile($sSource, $sTarget)
    {
        $blDone = move_uploaded_file($sSource, $sTarget);

        if ($blDone) {
            $blDone = @chmod($sTarget, 0644);
        }

        return $blDone;
    }

    /**
     * Checks whether the file has been uploaded over admin area.
     * Returns true in case file is uploaded (and hashed) over admin area.
     * Returns false in case file is placed manually (ftp) to "out/downloads/uploads" dir.
     * It's similar so don't get confused here.
     *
     * @return bool
     */
    public function isUploaded()
    {
        $blHashed = false;
        if ($this->oxfiles__oxstorehash->value) {
            $blHashed = true;
        }

        return $blHashed;
    }

    /**
     * Deletes oxFile record from DB, removes orphan files.
     *
     * @param string $sOxId default null
     *
     * @return bool
     */
    public function delete($sOxId = null)
    {
        $sOxId = $sOxId ? $sOxId : $this->getId();

        $this->load($sOxId);
        // if record cannot be delete, abort deletion
        if ($blDeleted = parent::delete($sOxId)) {
            $this->_deleteFile();
        }

        return $blDeleted;
    }
    /**
     * @deprecated use self::deleteFile instead
     */
    protected function _deleteFile() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->deleteFile();
    }

    /**
     * Checks if file is not used for  other objects.
     * If not used, unlink the file.
     *
     * @return null|false
     */
    protected function deleteFile()
    {
        if (!$this->isUploaded()) {
            return false;
        }
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $iCount = $oDb->getOne(
            'SELECT COUNT(*) FROM `oxfiles` WHERE `OXSTOREHASH` = :oxstorehash',
            [
                ':oxstorehash' => $this->oxfiles__oxstorehash->value
            ]
        );
        if (!$iCount) {
            $sPath = $this->getStoreLocation();
            unlink($sPath);
        }
    }
    /**
     * @deprecated use self::getFilenameForUrl instead
     */
    protected function _getFilenameForUrl() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return $this->getFilenameForUrl();
    }

    /**
     * returns oxfile__oxfilename for URL usage
     * converts spec symbols to %xx combination
     *
     * @return string
     */
    protected function getFilenameForUrl()
    {
        return rawurlencode($this->oxfiles__oxfilename->value);
    }

    /**
     * Supplies the downloadable file for client and exits
     */
    public function download()
    {
        $oUtils = \OxidEsales\Eshop\Core\Registry::getUtils();
        $sFileName = $this->_getFilenameForUrl();
        $sFileLocations = $this->getStoreLocation();

        if (!$this->exist() || !$this->isUnderDownloadFolder()) {
            throw new \OxidEsales\Eshop\Core\Exception\StandardException('EXCEPTION_NOFILE');
        }

        $oUtils->setHeader("Pragma: public");
        $oUtils->setHeader("Expires: 0");
        $oUtils->setHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
        $oUtils->setHeader('Content-Disposition: attachment;filename=' . $sFileName);
        $oUtils->setHeader("Content-Type: application/octet-stream");
        if ($iFileSize = $this->getSize()) {
            $oUtils->setHeader("Content-Length: " . $iFileSize);
        }
        readfile($sFileLocations);
        $oUtils->showMessageAndExit(null);
    }

    /**
     * Check if file exist
     *
     * @return bool
     */
    public function exist()
    {
        return file_exists($this->getStoreLocation());
    }

    /**
     * Checks if this file has valid ordered downloads
     *
     * @return bool
     */
    public function hasValidDownloads()
    {
        if ($this->_blHasValidDownloads == null) {
            $this->_blHasValidDownloads = false;

            $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

            $sSql = "SELECT
                        `oxorderfiles`.`oxid`
                     FROM `oxorderfiles`
                        LEFT JOIN `oxorderarticles` ON `oxorderarticles`.`oxid` = `oxorderfiles`.`oxorderarticleid`
                        LEFT JOIN `oxorder` ON `oxorder`.`oxid` = `oxorderfiles`.`oxorderid`
                     WHERE `oxorderfiles`.`oxfileid` = :oxfileid
                        AND ( ! `oxorderfiles`.`oxmaxdownloadcount` OR `oxorderfiles`.`oxmaxdownloadcount` > `oxorderfiles`.`oxdownloadcount`)
                        AND ( `oxorderfiles`.`oxvaliduntil` = '0000-00-00 00:00:00' OR `oxorderfiles`.`oxvaliduntil` > :oxvaliduntil )
                        AND `oxorder`.`oxstorno` = 0
                        AND `oxorderarticles`.`oxstorno` = 0";
            $params = [
                ':oxfileid' => $this->getId(),
                ':oxvaliduntil' => date('Y-m-d H:i:s', \OxidEsales\Eshop\Core\Registry::getUtilsDate()->getTime())
            ];

            if ($oDb->getOne($sSql, $params)) {
                $this->_blHasValidDownloads = true;
            }
        }

        return $this->_blHasValidDownloads;
    }

    /**
     * Returns max download count of file
     *
     * @return int
     */
    public function getMaxDownloadsCount()
    {
        $iMaxCount = $this->oxfiles__oxmaxdownloads->value;
        //if value is -1, takes global options
        if ($iMaxCount < 0) {
            $iMaxCount = $this->getConfig()->getConfigParam("iMaxDownloadsCount");
        }

        return $iMaxCount;
    }

    /**
     * Returns max download count of file, if user is not registered
     *
     * @return int
     */
    public function getMaxUnregisteredDownloadsCount()
    {
        $iMaxCount = $this->oxfiles__oxmaxunregdownloads->value;
        //if value is -1, takes global options
        if ($iMaxCount < 0) {
            $iMaxCount = $this->getConfig()->getConfigParam("iMaxDownloadsCountUnregistered");
        }

        return $iMaxCount;
    }

    /**
     * Returns ordered file link expiration time in hours
     *
     * @return int
     */
    public function getLinkExpirationTime()
    {
        $iExpTime = $this->oxfiles__oxlinkexptime->value;
        //if value is -1, takes global options
        if ($iExpTime < 0) {
            $iExpTime = $this->getConfig()->getConfigParam("iLinkExpirationTime");
        }

        return $iExpTime;
    }

    /**
     * Returns download link expiration time in hours, after the first download
     *
     * @return int
     */
    public function getDownloadExpirationTime()
    {
        $iExpTime = $this->oxfiles__oxdownloadexptime->value;
        //if value is -1, takes global options
        if ($iExpTime < 0) {
            $iExpTime = $this->getConfig()->getConfigParam("iDownloadExpirationTime");
        }

        return $iExpTime;
    }

    /**
     * Returns file size in bytes
     *
     * @return int
     */
    public function getSize()
    {
        $iSize = 0;
        if ($this->exist()) {
            $iSize = filesize($this->getStoreLocation());
        }

        return $iSize;
    }
}
