<?php
/**
 * User: remmel
 * Date: 20/06/18
 * Time: 19:14
 */

namespace Main;


interface IBank {
    /**
     * Returns the list of transaction for a specific month
     * @param \DateTime $month
     * @return Transaction[]
     */
    public function transactions(\DateTime $month);
}