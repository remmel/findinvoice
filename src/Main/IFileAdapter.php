<?php
/**
 * User: remmel
 * Date: 20/06/18
 * Time: 18:51
 */

namespace Main;


interface IFileAdapter {
    /**
     * Get list of files in the subfolder named with specific month
     * @param \DateTime $date
     * @return File[]
     */
    public function files(\DateTime $date);

    /**
     * Upload a new file the subfolder
     * @param \DateTime $month
     * @param $tmp path of tmp file uploaded on server
     * @param $newName new name of the file eg (2018-06-34_GoogleSuite_345.00_june-4pax.pdf)
     * @return mixed
     */
    public function upload(\DateTime $month, $tmp, $newName);

    /**
     * Remove a file
     */
    public function remove($fId);
}