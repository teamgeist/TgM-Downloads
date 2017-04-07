<?php

namespace TGM\TgmDownloads\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Oliver Pfaff <op@teamgeist-medien.de>, Teamgeist Medien GbR
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TGM\TgmDownloads\Domain\Model\Download;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * DownloadController
 */
class DownloadController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * downloadRepository
     *
     * @var \TGM\TgmDownloads\Domain\Repository\DownloadRepository
     * @inject
     */
    protected $downloadRepository = null;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        //Get Plugin Settings
        $settings = $this->settings['flex'];
        if (!empty($settings['dataTables'])) {
            $this->injectDataTabels();
            unset($settings['pagination']);
        }

        //set ordering
        if (!empty($settings['orderBy'])) {
            $this->downloadRepository->setDefaultOrderings([$settings['orderBy'] => $settings['orderType']]);
        }

        //get downloads
        $downloads = $this->downloadRepository->findViaFilter($settings['filter']);

        //find the newest download (by Date, if no date is set crdate will be used) @TODO make own repository query
        if (!empty($settings['latest'])) {
            $download = $this->findNewestDownload($downloads);

            $this->view->assignMultiple([
                'latestDownload' => $download,
                'settings' => $settings
            ]);
        }

        $this->view->assignMultiple([
            'downloads' => $downloads,
            'settings' => $settings
        ]);
    }

    /**
     * @param QueryResult $downloads
     * @return Download|false
     */
    protected function findNewestDownload($downloads)
    {
        if (count($downloads) > 0) {
            $date = '';
            $latestDownload = '';
            /** @var \TGM\TgmDownloads\Domain\Model\Download $download */
            foreach ($downloads as $download){
                //first iteration
                if (empty($date)) {
                    //If we have a date
                    if(!empty($download->getDate())) {
                        $date = $download->getDate();
                    }else{
                        $date = new \DateTime('@'.$download->getCrdate());
                        $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                    }
                    $latestDownload = $download;
                    continue;
                }
                //second+ iteration
                if(!empty($download->getDate())) {
                    $dateToCompare = $download->getDate();
                }else{
                    $dateToCompare = new \DateTime('@'.$download->getCrdate());
                    $dateToCompare->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                }
                if($dateToCompare > $date){
                    $date = $dateToCompare;
                    $latestDownload = $download;
                }
            }
            return $download;
        }
        return false;
    }


    /**
     * Add Needed JS and CSS for DataTabels
     */
    protected function injectDataTabels()
    {
        /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
        $pageRenderer = $this->objectManager->get(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->addJsFooterFile('//cdn.datatables.net/v/bs/dt-1.10.13/r-2.1.0/datatables.min.js',
            'text/javascript', false, false, '', true);
        $pageRenderer->addCssLibrary('//cdn.datatables.net/v/bs/dt-1.10.13/r-2.1.0/datatables.min.css');
        $jsInit = '$(document).ready( function () {$(\'.table_downloads\').DataTable();} );';
        if ($pageRenderer->getLanguage() == 'de') {
            $jsInit = '$(document).ready( function () {$(\'.table_downloads\').DataTable({
                    language: {
                        url: \'//cdn.datatables.net/plug-ins/1.10.13/i18n/German.json\'
                    },
                    order: []
                });} );';
        }
        $pageRenderer->addJsFooterInlineCode('dataTables', $jsInit);
    }

    /**
     * action show
     *
     * @param \TGM\TgmDownloads\Domain\Model\Download $download
     * @return string|void
     */
    public function downloadAction(\TGM\TgmDownloads\Domain\Model\Download $download)
    {
        //Set Downloadtimes +1
        $downloadtimes = $download->getDownloadtimes();
        $downloadtimes = (int)$downloadtimes + 1;
        $download->setDownloadtimes($downloadtimes);
        $this->downloadRepository->update($download);
        //Persist
        /** @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager */
        $persistenceManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface::class);
        $persistenceManager->persistAll();
        //Setup the Download or the PDF output
        /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $file */
        $file = $download->getDownload();
        $originalFileRef = $file->getOriginalResource();
        $cType = $originalFileRef->getMimeType();

        $headers = array(
            'Pragma' => 'public',
            'Expires' => 0,
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-Type' => $cType,
            'Content-Disposition' => 'inline; filename="' . $originalFileRef->getName() . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length' => $originalFileRef->getSize()
        );

        if ($cType != 'application/pdf') {
            $headers['Content-Disposition'] = 'attachment; filename="' . $originalFileRef->getName() . '"';
        }

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
        $response = $this->controllerContext->getResponse();

        foreach ($headers as $header => $data) {
            $response->setHeader($header, $data, 1);
        }
        $response->sendHeaders();
        echo $originalFileRef->getContents();

        exit();
    }

    /**
     * action import BLOB extension
     *
     * @return void
     */
    public function importTxBlobAction()
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $DB */
        $DB = $GLOBALS['TYPO3_DB'];
        //$DB->store_lastBuiltQuery = true;
        $fullBlobEntrys = $DB->exec_SELECT_mm_query('*', 'tx_drblob_content', 'tx_drblob_category_mm',
            'tx_drblob_category', '', '', '');
        //AND  tx_drblob_content.uid = 515
        //DebuggerUtility::var_dump($DB->debug_lastBuiltQuery);


        /** @var \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository $categoryRepo */
        $categoryRepo = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository::class);

        /** @var \TYPO3\CMS\Core\Resource\StorageRepository $storageRepo */
        $storageRepo = $this->objectManager->get(\TYPO3\CMS\Core\Resource\StorageRepository::class);

        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory */
        $resourceFactory = $this->objectManager->get(\TYPO3\CMS\Core\Resource\ResourceFactory::class);

        /** @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistManager */
        $persistManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface::class);

        $storage = $storageRepo->findByUid(1);
        $folderBasis = $storage->getFolder('user_upload/downloads');
        $replaceArray = [',', ' ', '&', '(', ')', '+'];

        //Create ne download entry for each blob entry
        while ($entry = $GLOBALS['TYPO3_DB']->sql_fetch_row($fullBlobEntrys)) {
            if (empty($entry[33])) {
                continue;
            }
            //DebuggerUtility::var_dump($entry);
            //Check if Category exsists
            if (!empty($entry[49])) {
                /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $sysCat */
                $sysCat = $categoryRepo->findByTitle($entry[49]);
                //$sysCat = $categoryRepo->findByTitle('test, 123 und 4');
                if ($sysCat->count() < 1) {
                    //add sys cat to new download entry
                    if ($storage->hasFolder('user_upload/downloads/' . $entry[49]) === false) {
                        $storage->createFolder(str_replace(',', '_', $entry[49]), $folderBasis);
                    }
                    $folder = $storage->getFolder('user_upload/downloads/' . str_replace($replaceArray, '_',
                            $entry[49]));
                    /** @var \TYPO3\CMS\Extbase\Domain\Model\Category $newCategory */
                    $newCategory = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Model\Category::class);
                    $newCategory->setTitle($entry[49]);
                    //$newCategory->setTitle('test, 123 und 4');
                    $newCategory->setPid(208);
                    $newCategory->setParent($categoryRepo->findByUid(2));
                    $categoryRepo->add($newCategory);
                    $persistManager->persistAll();
                    $category = $newCategory;
                } else {
                    $folder = $storage->getFolder('user_upload/downloads/' . str_replace($replaceArray, '_',
                            $entry[49]));
                    $category = $sysCat->getFirst();
                }
            }

            //create new file form the blob data
            $data = stripslashes($entry[37]);

            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $storage->createFile(str_replace($replaceArray, '_', $entry[33]), $folder);
            $file->setContents($data);

            //Create core filereference
            $fileReference = $resourceFactory->createFileReferenceObject(
                [
                    'uid_local' => $file->getUid(),
                    'uid_foreign' => uniqid('NEW_'),
                    'uid' => uniqid('NEW_'),
                    'crop' => null,
                ]
            );

            //Create extbase Filereference
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $newFileReference */
            $newFileReference = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Model\FileReference::class);
            $newFileReference->setOriginalResource($fileReference);

            //Create new tgm Download Entry
            /** @var \TGM\TgmDownloads\Domain\Model\Download $newDownload */
            $newDownload = $this->objectManager->get(\TGM\TgmDownloads\Domain\Model\Download::class);
            $newDownload->setDownload($newFileReference);
            $newDownload->setTitle($entry[25]);
            $newDownload->setDate(new \DateTime(date('d.m.Y', (int)$entry[3])));
            $newDownload->addCategory($category);
            $newDownload->setPid((int)$entry[1]);
            $newDownload->setDownloadtimes($entry[32]);
            $this->downloadRepository->add($newDownload);
        }
        $persistManager->persistAll();
    }
}