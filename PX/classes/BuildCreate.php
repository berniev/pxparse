<?php
/**
 * Copyright 2017 Bernie van't Hof
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PX\classes;

class BuildCreate
{

    public $sql = '';

    /** @var TableSpecs */
    public $table = null;

    /** @var FieldSpecs[] */
    public $fields = [];

    /**
     * Build sql create statement for a single db
     *
     * @param string $tableName
     *
     * @return bool|string
     */
    public function Aggregate($tableName)
    {
        /* DB */
        $parser = new PXparseDb;
        $res = $parser->ParseFile($tableName);
        if ( ! $res) {
            return false;
        }
        list($this->table, $this->fields) = $res;

        /* VAL */
        $parser = new PXparseVal;
        $vals = $parser->ParseFile("{$tableName}.val");
        if ($vals) {
            foreach ($vals as $val) {
            }
        }

        /* Xx */
        $xFiles = $this->GetXfiles($tableName);
        if ($xFiles) {
            foreach ($xFiles as $xFile) {
                $parser = new PXparseX;
                list($unused, $indexFields) = $parser->ParseFile($xFile);
            }
        }

        return $this->sql;
    }

    /**
     * @param string $tableName
     *
     * @return array
     */
    public function GetXfiles($tableName)
    {

        return [];
    }

    /**
     * Build sql create statements for all db's in a directory
     *
     * @param string $location
     * @param bool   $includeSubdirectories
     */
    public function BuildDirectory($location, $includeSubdirectories = false)
    {
    }

}