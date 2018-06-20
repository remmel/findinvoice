<?php
/**
 * User: remmel
 * Date: 20/06/18
 * Time: 15:52
 */

namespace Main;


class File {
    public $id; //path or id. for local FS id = DOCUMENTS_FOLDER + / + filename (with extension)
    public $name; //filename without extension
    public $filename; //with extension
    public $viewlink; //view document
}