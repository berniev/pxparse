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

/**
 * Combine the structures from DB, Xxx, VAL, and SET files
 *
 * Class Aggregate
 *
 * @package PX\classes
 */
class Aggregate
{

    /** @var string */
    private $name = '';

    /** @var string */
    private $path = '';

    /** @var TableSpecsCombined[] */
    public $tables = null;

    /** @var FieldSpecsCombined[] */
    public $fields = [];

    /** @var SecIndex[] */
    public $indexes = [];

    /** @var string[] */
    public $sqls = [];

    public function __Construct($tableName = '*', $path = '')
    {
        $this->name = $tableName; // '*' = all
        $this->path = $path ? : './'; // '' = cwd
    }

    /**
     * @return TableSpecsCombined[]
     */
    public function Parse()
    {
        $tableNames = $this->name == '*' ? $this->GetTableNames() : [$this->name];
        $this->tables = [];
        foreach ($tableNames as $table) {
            list($table, $fields, $indexes) = $this->DoAggregate($table);
            $this->tables[$table->name] = $table;
            $this->fields[$table->name] = $fields;
            $this->indexes[$table->name] = $indexes;
        }
        return $this->tables;
    }

    /**
     * Combine the various pdox table data
     *
     * @param string $tableName
     *
     * @return array|false
     */
    private function DoAggregate($tableName)
    {
        /* from DB */
        $parser = new PXparseDb;
        $res = $parser->ParseFile("{$this->path}{$tableName}.db");
        if ( ! $res) {
            return false;
        }
        $table = new TableSpecsCombined();

        $fields = [];

        $table->name = $tableName;
        $table->numFields = $parser->table->numFields;
        $table->numKeyFields = $parser->table->numKeyFields;
        $table->sortOrder = $parser->table->sortOrder;
        $pkeys = [];
        foreach ($parser->fields as $field) {
            $cField = new FieldSpecsCombined();

            $cField->name = $field->name;
            $cField->len = $field->len;
            $cField->type = $field->type;
            $cField->isKey = $field->isKey;
            $cField->num = $field->num;

            $fields[$field->name] = $cField;

            if($field->isKey){
                $pkeys[] = $field->name;
            }
        }

        /* from VAL */
        $parser = new PXparseVal;
        $res = $parser->ParseFile("{$this->path}{$tableName}.val");
        if ($res) {
            foreach ($parser->vals as $val) {
                $fields[$val->name]->lookupTable = $val->lookupTable;
                $fields[$val->name]->picture = $val->pic;
                $fields[$val->name]->default = $val->def;
                $fields[$val->name]->required = $val->reqd;
                $fields[$val->name]->autoFill = $val->autoFill;
                $fields[$val->name]->autoPic = $val->autoPic;
                $fields[$val->name]->autoLookup = $val->autoLookup;
                $fields[$val->name]->loVal = $val->loVal;
                $fields[$val->name]->hiVal = $val->hiVal;
                $fields[$val->name]->fillType = $val->fillType;
            }
        }

        /* from SET */
        $parser = new PXparseSet;
        $res = $parser->ParseFile("{$this->path}{$tableName}.set");
        if ($res) {
            foreach ($parser->settings as $set) {
                $fields[$set->name]->decPlaces = $set->decPlaces;
                $fields[$set->name]->dunno1 = $set->dunno1;
                $fields[$set->name]->dunno1 = $set->dunno2;
                $fields[$set->name]->defDispLen = $set->defDispLen;
                $fields[$set->name]->useDispLen = $set->useDispLen;
            }
        }

        /* from Xx */
        $xFiles = $this->GetXfiles($tableName);
        $indexes = [];
        foreach ($xFiles as $xFile) {
            $parser = new PXparseX;
            $res = $parser->ParseFile($this->path . $xFile);
            if ($res) {
                $ifcount = count(explode(',',$parser->index->fields));
                $sliced = array_slice(explode(',',$parser->index->fields),0, $ifcount -$table->numKeyFields);
                $parser->index->fields = implode(',',$sliced);
                $indexes[$parser->index->name] = $parser->index;
            }
        }
        return [$table, $fields, $indexes];
    }

    /**
     * @return false|array
     */
    private function GetTableNames()
    {
        $extn = 'db';
        $tableNames = [];
        $dir_handle = @opendir($this->path);
        if ( ! $dir_handle) {
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
            if (strtolower($ext) == strtolower($extn)) {
                $tableNames[] = $filename[0];
            }
        }
        return $tableNames;
    }

    /**
     * @param string $tableName
     *
     * @return string[]|false
     */
    private function GetXfiles($tableName)
    {
        $xFiles = [];
        $dir_handle = @opendir($this->path);
        if ( ! $dir_handle) {
            echo("<br>" . __METHOD__ . " Can't open path {$this->path}\n");
            return false;
        }
        while ($file = readdir($dir_handle)) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $filename = explode(".", $file);
            if (strtolower($filename[0]) == strtolower($tableName)) {
                $cnt = count($filename);
                $cnt--;
                $ext = $filename[$cnt];
                if (substr((strtolower($ext)), 0, 1) == 'x') {
                    $xFiles[] = $file;
                }
            }
        }
        closedir($dir_handle);

        return $xFiles;
    }

    public function GenerateSqlCreate()
    {
        foreach ($this->tables as $table) {
            $fldStrs = [];
            $pkeys = [];
            $sql = "\nCREATE TABLE `{$table->name}`";
            foreach ($this->fields[$table->name] as $field) {
                IF ($field->isKey == '1') {
                    $pkeys[] = $field->name;
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
                $null = $field->required = '1' || $field->isKey ? 'NOT NULL' : 'NULL';
                $fldStrs[] = "\n`{$field->name}` {$sqlType} {$null}";
            }
            if ($pkeys) {
                foreach ($pkeys as &$pkey) {
                    $pkey = "`{$pkey}`";
                }
                $fldStrs[] = "\nPRIMARY KEY (" . implode(', ', $pkeys) . ")";
            }
            foreach ($this->indexes[$table->name] as $index) {
                $flds = explode(',', $index->fields);
                foreach ($flds as &$fld) {
                    $fld = "`{$fld}`";
                }
                $fldStr = implode(',', $flds);
                $fldStrs[] = "\nINDEX `{$index->name}` ({$fldStr})";
            }

            $fldsStr = implode(',', $fldStrs);
            $this->sqls[$table->name] = "\nCREATE TABLE `{$table->name}` ({$fldsStr})\n";
        }
    }
}