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

namespace PX\classes\Data;

use PX\classes\DestSql;
use PX\classes\FieldSpecsCombined;

class PXparseDb extends PXparseDataFile
{

    /** @var FieldSpecs[] */
    public $fields = [];

    /** @var [] */
    private $keySubFields = [];

    /**
     * @param string $path
     * @param string $tableName
     */
    public function __Construct($path, $tableName)
    {
        $this->tableName = $tableName;
        $this->file = "{$path}{$tableName}.db";
    }

    /**
     * @param string $path
     *
     * @return array|false
     */
    static public function GetTableNames($path)
    {
        $tableNames = [];
        $isDir = is_dir($path);
        if ( ! $isDir) {
            echo "Not a directory: {$path}";
            return false;
        }
        $dir_handle = @opendir($path);
        if ( ! $dir_handle) {
            echo "opendir failure on {$path}";
            return false;
        }
        while ($file = readdir($dir_handle)) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $filename = explode(".", $file);
            $cnt = count($filename);
            $cnt--;
            $ext = $filename[$cnt];
            if (strtolower($ext) == 'db') {
                $tableNames[] = $filename[0];
            }
        }
        return $tableNames;
    }

    /**
     * @return array|false
     */
    public function ParseFile()
    {
        $this->ParseDataFileHeader();
        if ($this->table->fileVersionId != 9) {
            echo "\nNot a V4.n table";
            return false;
        }
        $this->fields = [];
        for ($i = 0; $i < $this->table->numFields; $i++) {
            $field = new FieldSpecs($this->names[$i]);
            $field->type = $this->specs[$i]['type'];
            $field->len = $this->specs[$i]['len'];
            $field->num = $this->nums[$i];
            $this->fields[] = $field;
        }
        $this->table->isKeyed = $this->table->fileType == '02' ? 0 : 1;
        if ($this->table->isKeyed) {
            for ($i = 0; $i < $this->table->numKeyFields; $i++) {
                $this->fields[$i]->isKey = 1;
            }
        }
        return [$this->table, $this->fields];
    }

    /**
     * @param DestSql              $dest
     * @param FieldSpecsCombined[] $fields
     *
     * @return bool
     */
    public function ParseData(DestSql $dest, array $fields)
    {
        set_time_limit(300);

        if ($this->table->numRecords == 0) {
            return true;
        }

        if ($this->table->isEncrypted) {
            echo "\nTable is encrypted, not extracting data";
            return false;
        }

        /* table is not empty, not encrypted */
        $dest->Write("\n\nALTER TABLE `{$this->tableName}` DISABLE KEYS;");
        $dest->Write("\nINSERT INTO `{$this->tableName}`");

        $colNames = [];
        foreach ($this->fields as $field) {
            $colNames[] = "`{$field->name}`";
        }
        $colNamesStr = "\n(" . implode(', ', $colNames) . ")";
        $dest->Write("{$colNamesStr}\nVALUES");

        $blockSize = $this->table->blockSize * 1024;
        $nextBlockNum = $this->table->firstBlock;
        do {
            $this->SetPosn($this->table->headerSize + ($nextBlockNum - 1) * $blockSize);

            /* block Header */
            $nextBlockNum = $this->ReadPxLe2();
            $this->Skip(2); // prev block num - ignore
            $offsetToLastRecord = $this->ReadPxLe2();

            /* records */
            $records = $this->ReadRecords($offsetToLastRecord, $fields);
            if (false === $records) {
                echo "\nreadfail";
                return false; // read fail
            }
            $rows = '';
            foreach ($records as $record) {
                $cols = "\n(" . implode(', ', $record) . ")";
                $rows[] = $cols;
            }
            $rows = implode(",", $rows);
            if ($dest->Write($rows) === false) {
                echo "\nwritefail";
                return false; // write fail
            }
            if ($nextBlockNum > 0) {
                $dest->Write(",");
            }
        } while ($nextBlockNum > 0);
        $dest->Write(";\nALTER TABLE `{$this->tableName}` ENABLE KEYS;");
        if ($this->keySubFields) {
            echo "\nWarning: Substituted value for null primary key or required field(s): " .
                 implode(', ', array_keys($this->keySubFields));
        }
        return true;
    }

    /**
     * @param int                  $offsetToLastRecord
     * @param FieldSpecsCombined[] $fields
     *
     * @return array
     */
    private function ReadRecords($offsetToLastRecord, array $fields)
    {
        $records = [];
        $lastRecordStart = $this->GetPosn() + $offsetToLastRecord;
        while ($this->GetPosn() <= $lastRecordStart) {
            $records[] = $this->ReadRecord($fields);
        }
        return $records;
    }

    /**
     * @param FieldSpecsCombined[] $fields
     *
     * @return array
     */
    private function ReadRecord(array $fields)
    {
        $rowVals = [];
        foreach ($this->fields as $field) {
            $rowVal = $this->GetFieldData($field->type, $field->len);
            if ($rowVal === null && ($field->isKey || $fields[$field->name]->required == '1')) {
                $this->keySubFields["`{$field->name}`"] = true;
                switch ($field->type) {
                    case 'Date':
                        $rowVal = '0000-00-00';
                        break;
                    case 'Number':
                    case 'Short':
                    case 'Dollar':
                        $rowVal = '0';
                        break;
                    default:
                        $rowVal = '';
                }
            }
            $rowVals[] = ($rowVal === null) ? 'NULL' : "'" . $rowVal . "'";
        }
        return $rowVals;
    }

}



