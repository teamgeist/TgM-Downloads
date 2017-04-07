<?php
namespace TGM\TgmDownloads\Domain\Repository;


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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * The repository for Downloads
 */
class DownloadRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    );

    public function initializeObject()
    {
        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);

        //Set on false so we get all entrys
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * Get all filter relevant downloads
     *
     * @param $filter array
     */
    public function findViaFilter($filter)
    {
        if (!empty($filter['categories'])) {
            $query = $this->evaluateFilter($filter);
        } else {
            //get all
            return $this->findAll();
        }
        return $query->execute();
    }



    /**
     * Generate the filter constraint
     *
     * @param $filter array
     */
    protected function evaluateFilter($filter)
    {
        $query = $this->createQuery();
        $conjunction = $filter['categoriesConjunction'];
        $constraints = [];
        $categories = explode(',',$filter['categories']);
        foreach ($categories as $category){
            $constraints[] = $query->contains('categories',$category);
        }
        switch ($conjunction){
            case 'or' :
                $query->matching($query->logicalOr($constraints));
                break;
            case 'and' :
                $query->matching($query->logicalAnd($constraints));
                break;
            default :
                new \Exception('Unknown Conjunction');
        }
        return $query;
    }

}