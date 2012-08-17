<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Interfaces\Mongo;

/**
 * Interface of data access services
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IDataAccess
{

    /**
     * Initialize a data service handler to read or write in a DataBase
     * Collection
     *
     * @param string $collection name of the DB
     * @param string $dbName name of the DB
     * @param string $connection connection string to the DB server
     */
    public function init ($collection, $dbName = null, $connection = null);

    /**
     * Do a find request on the current collection
     *
     * @return array
     */
    public function read ();

    /**
     * Do a findone request on the current collection
     *
     * @return array
     */
    public function findOne ();

    /**
     * Create an objet in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create (array $obj, $safe = true);

    /**
     * Update an objet in the current collection
     *
     * @param criteria Update condition criteria
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function update(array $criteria, array $obj, $safe = true);

    /**
     * Update an objet in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $safe = true);
}