<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling ImportData
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IImportData extends IAbstractCollection
{

    /**
     * Writes the datas contained in the imported file to the ImportData collection
     *
     * @param   string  $fileName           Full path to the CSV file
     * @param   string  $separator          Separator used in the CSV file
     * @param   string  $userEncoding       Current encoding of the CSV file
     * @param   string  $importKeyValue     Unique identifier for this import
     * @return  bool
     */
    public function writeImportFile($fileName, $separator, $userEncoding, $importKeyValue);

}
