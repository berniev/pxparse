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

use PX\classes\Data\Index\PXparseX;
use PX\classes\Data\PXparseDb;
use PX\classes\Set\PXparseSet;
use PX\classes\Val\PXparseVal;

/**
 * Combine the structures from DB, Xnn, VAL, and SET files
 */
class ParseTables
{

    /** @var string[] */
    private $tableNames = [];

    /** @var string */
    private $path = '';

    /** @var FieldSpecsCombined[] */
    public $fields = [];

    /** @var bool */
    private $perTable = false;

    /** @var string */
    private $sqlInfoFile = '';

    /** @var string */
    private $sqlCreateFile = '';

    /** @var string */
    private $sqlDataFile = '';

    /** @var string */
    private $destDb = '';

    /** @var string */
    private $sqlInfoTableName = '';

    public function __Construct(
        array $tableNames = [],
        $path = './',
        $perTable = false,
        $sqlInfoFile = 'pxinfo.sql',
        $sqlCreateFile = 'pxcreate.sql',
        $sqlDataFile = 'pxdata.sql',
        $destDb = ''
    ) {
        $this->tableNames = $tableNames;
        $this->path = $path;
        $this->perTable = $perTable;
        $this->sqlInfoFile = $sqlInfoFile;
        $this->sqlCreateFile = $sqlCreateFile;
        $this->sqlDataFile = $sqlDataFile;
        $this->destDb = $destDb;
        $this->sqlInfoTableName = 'pxinfo';
    }

    /**
     * @param \string[] $tableNames
     */
    public function setTableNames($tableNames)
    {
        $this->tableNames = $tableNames;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param bool $perTable
     */
    public function setPerTable($perTable)
    {
        $this->perTable = $perTable;
    }

    /**
     * @param string $sqlInfoFile
     */
    public function setSqlInfoFile($sqlInfoFile)
    {
        $this->sqlInfoFile = $sqlInfoFile;
    }

    /**
     * @param string $sqlCreateFile
     */
    public function setSqlCreateFile($sqlCreateFile)
    {
        $this->sqlCreateFile = $sqlCreateFile;
    }

    /**
     * @param string $sqlDataFile
     */
    public function setSqlDataFile($sqlDataFile)
    {
        $this->sqlDataFile = $sqlDataFile;
    }

    /**
     * @param string $destDb
     */
    public function setDestDb($destDb)
    {
        $this->destDb = $destDb;
    }

    /**
     * @param string $sqlInfoTableName
     */
    public function setSqlInfoTableName($sqlInfoTableName)
    {
        $this->sqlInfoTableName = $sqlInfoTableName;
    }

    /**
     * @return bool
     */
    public function Parse()
    {
        $tableNames = $this->tableNames == [] ? PXparseDb::GetTableNames($this->path) : $this->tableNames;
        if ($tableNames === false) {
            return false;
        }

        $infoDest = new DestSql($this->sqlInfoFile);
        $infoDest->Open();

        $createDest = new DestSql($this->sqlCreateFile);
        $createDest->Open();

        $dataDest = new DestSql($this->sqlDataFile);
        $dataDest->Open();

        if ($this->destDb) {
            $db = "\nCREATE DATABASE IF NOT EXISTS `{$this->destDb}`;\n\nUSE `{$this->destDb}`;\n";
            $infoDest->Write($db);
            $createDest->Write($db);
            $dataDest->Write($db);
        }

        $infoDest->Write(FieldSpecsCombined::InfoSqlCreate($this->sqlInfoTableName));

        foreach ($tableNames as $tableName) {
            echo "<br>$tableName";
            $this->ParseTable($tableName, $infoDest, $createDest, $dataDest);
        }

        $dataDest->Close();
        $createDest->Close();
        $infoDest->Close();

        return true;
    }

    /**
     * @param string  $tableName
     *
     * @param DestSql $infoDest
     * @param DestSql $createDest
     * @param DestSql $dataDest
     *
     * @return bool
     */
    private function ParseTable($tableName, $infoDest, $createDest, $dataDest)
    {
        /* from DB */
        $dparser = new PXparseDb($this->path, $tableName);
        $dparser->Open();
        $res = $dparser->ParseFile();
        if ( ! $res) {
            return false;
        }

        $this->fields = [];

        foreach ($dparser->fields as $field) {
            $this->fields[$field->name] = new FieldSpecsCombined();

            $this->fields[$field->name]->name = $field->name;
            $this->fields[$field->name]->len = $field->len;
            $this->fields[$field->name]->type = $field->type;
            $this->fields[$field->name]->isKey = $field->isKey;
            $this->fields[$field->name]->num = $field->num;
        }

        /* from VAL */
        $vparser = new PXparseVal($this->path, $tableName);
        $res = $vparser->ParseFile();
        if ($res) {
            foreach ($vparser->vals as $val) {
                $this->fields[$val->name]->lookupTable = $val->lookupTable;
                $this->fields[$val->name]->picture = $val->pic;
                $this->fields[$val->name]->default = $val->def;
                $this->fields[$val->name]->required = $val->reqd;
                $this->fields[$val->name]->autoFill = $val->autoFill;
                $this->fields[$val->name]->autoPic = $val->autoPic;
                $this->fields[$val->name]->autoLookup = $val->autoLookup;
                $this->fields[$val->name]->loVal = $val->loVal;
                $this->fields[$val->name]->hiVal = $val->hiVal;
                $this->fields[$val->name]->fillType = $val->fillType;
            }
        }

        /* from SET */
        $sparser = new PXparseSet($this->path, $tableName);
        $res = $sparser->ParseFile();
        if ($res) {
            foreach ($sparser->settings as $set) {
                $this->fields[$set->name]->decPlaces = $set->decPlaces;
                $this->fields[$set->name]->dunno1 = $set->dunno1;
                $this->fields[$set->name]->dunno2 = $set->dunno2;
                $this->fields[$set->name]->defDispLen = $set->defDispLen;
                $this->fields[$set->name]->useDispLen = $set->useDispLen;
            }
        }

        /* write sql info */
        $infoDest->Write(FieldSpecsCombined::InfoSqlInsert($this->sqlInfoTableName, $tableName, $this->fields));

        /* from Xx */
        $indexes = [];
        $xFiles = PXparseX::GetXfiles($this->path, $tableName);
        foreach ($xFiles as $xFile) {
            $xparser = new PXparseX($this->path, $tableName);
            $res = $xparser->ParseFile($this->path . $xFile, $dparser);
            if ($res) {
                $indexes[$res->name] = $res;
            }
        }
        /* write sql create */
        $createDest->Write($this->GenerateSqlCreate($tableName, $this->fields, $indexes));

        /* write sql insert */
        $dparser->ParseData($dataDest, $this->fields);
        return true;
    }

    /**
     * @param string               $tableName
     * @param array[]              $indexes
     * @param FieldSpecsCombined[] $fields
     *
     * @return string
     */
    private function GenerateSqlCreate($tableName, array $fields, array $indexes)
    {
        $fldStrs = [];
        $pkeys = [];
        foreach ($fields as $name => $field) {
            if ($field->isKey == '1') {
                $pkeys[] = $name;
            }
            $sqlType = '';
            switch ($field->type) {
                case 'Alpha':
                    $sqlType = "VARCHAR({$field->len})";
                    break;
                case 'Number':
                    $sqlType = 'DOUBLE';
                    break;
                case  'Dollar':
                    $sqlType = 'DECIMAL(19,4)';
                    break;
                case  'Short':
                    $sqlType = 'SMALLINT';
                    break;
                case  'Memo':
                    $sqlType = 'TEXT';
                    break;
                case  'Blob':
                    $sqlType = "BLOB({$field->len})";
                    break;
                case  'Date':
                    $sqlType = 'DATE';
            }
            $null = ($field->required == '1' || $field->isKey) ? 'NOT NULL' : 'NULL';
            $fldStrs[] = "\n`{$field->name}` {$sqlType} {$null}";
        }
        if ($pkeys) {
            foreach ($pkeys as &$pkey) {
                $pkey = "`{$pkey}`";
            }
            $fldStrs[] = "\nPRIMARY KEY (" . implode(', ', $pkeys) . ")";
        }
        foreach ($indexes as $name => $index) {
            foreach ($index->fields as &$fieldName) {
                $fieldName = "`{$fieldName}`";
            }
            $fldStr = implode(',', $index->fields);
            $fldStrs[] = "\nINDEX `{$name}` ({$fldStr})";
        }

        $fldsStr = implode(',', $fldStrs);
        $sql = "\nCREATE TABLE `{$tableName}` ({$fldsStr}\n);\n";
        return $sql;
    }

}