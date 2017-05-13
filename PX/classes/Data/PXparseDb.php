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
 *
 * Just to get primary key field names
 */

namespace PX\classes\Data;

use PX\classes\DestSql;

class PXparseDb extends PXparseDataFile
{

    /** @var FieldSpecs[] */
    public $fields = [];

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
        list($specs, $names, $nums) = $this->ParseDataFileHeader();
        if ($this->table->fileVersionId != 9) {
            echo "\nNot a V4.n table";
            return false;
        }
        $this->fields = [];
        for ($i = 0; $i < $this->table->numFields; $i++) {
            $field = new FieldSpecs($names[$i]);
            $field->type = $specs[$i]['type'];
            $field->len = $specs[$i]['len'];
            $field->num = $nums[$i];
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
     * @param DestSql $dest
     *
     * @return bool
     */
    public function ParseData(DestSql $dest)
    {
        set_time_limit(300);

        if ($this->table->numRecords == 0) {
            return true;
        }

        if ($this->table->isEncrypted) {
            echo "\nTable {$this->tableName} is encrypted, not extracting data";
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
            $records = $this->ReadRecords($offsetToLastRecord);
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
        return true;
    }

    /**
     * @param int $offsetToLastRecord
     *
     * @return array
     */
    private function ReadRecords($offsetToLastRecord)
    {
        $records = [];
        $lastRecordStart = $this->GetPosn() + $offsetToLastRecord;
        while ($this->GetPosn() <= $lastRecordStart) {
            $records[] = $this->ReadRecord();
        }
        return $records;
    }

    /**
     * @return array
     */
    private function ReadRecord()
    {
        $rowVals = [];
        foreach ($this->fields as $field) {
            $rowVal = $this->GetFieldData($field->type, $field->len);
            $rowVals[] = $rowVal ? "'" . $rowVal . "'" : 'NULL';
        }
        return $rowVals;
    }

}



