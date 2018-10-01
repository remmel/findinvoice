<?php
/**
 * User: remmel
 * Date: 20/06/18
 * Time: 15:52
 */

namespace App\Legacy;


class File {
    public $id; //path or id. for local FS id = DOCUMENTS_FOLDER + / + filename (with extension)
    public $name; //name with extension
    public $viewlink; //view document
}