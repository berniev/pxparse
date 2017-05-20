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

class FieldSpecsCombined
{

    /* from DB */

    /** @var string */
    public $name = '';

    /** @var int */
    public $type = 0;

    /** @var int */
    public $len = 0;

    /** @var int */
    public $isKey = 0;

    /** @var int */
    public $num = 0;

    /* from VAL */

    /** @var int */
    public $required = 0;

    /** @var string */
    public $default = '';

    /** @var string */
    public $picture = '';

    /** @var string */
    public $loVal = '';

    /** @var string */
    public $hiVal = '';

    /** @var string */
    public $lookupTable = '';

    /** @var string */
    public $fillType = '';

    /** @var int */
    public $autoFill = 0;

    /** @var int */
    public $autoLookup = 0;

    /** @var int */
    public $autoPic = 0;

    /* from SET */

    /** @var string */
    public $dunno1 = '';

    /** @var int */
    public $defDispLen = 0;

    /** @var int */
    public $useDispLen = 0;

    /** @var int */
    public $dunno2 = 0;

    /** @var int */
    public $decPlaces = 0;

    static public function InfoSqlCreate($sqlTableName)
    {
        $sql = "\nCREATE TABLE `{$sqlTableName}` (";
        $sql .= "\n`Table` VARCHAR(8) NOT NULL,";
        $sql .= "\n`Field` VARCHAR(40) NOT NULL,";
        $sql .= "\n`Type` VARCHAR(8) NULL,";
        $sql .= "\n`Len` SMALLINT UNSIGNED NULL,";
        $sql .= "\n`IsKey` TINYINT UNSIGNED NULL,";
        $sql .= "\n`Num` SMALLINT UNSIGNED NULL,";
        $sql .= "\n`Required` TINYINT UNSIGNED NULL,";
        $sql .= "\n`Default` VARCHAR(256) NULL,";
        $sql .= "\n`Picture` VARCHAR(256) NULL,";
        $sql .= "\n`LoVal` VARCHAR(256) NULL,";
        $sql .= "\n`HiVal` VARCHAR(256) NULL,";
        $sql .= "\n`LookupTable` VARCHAR(80) NULL,";
        $sql .= "\n`FillType` VARCHAR(10) NULL,";
        $sql .= "\n`AutoFill` TINYINT UNSIGNED NULL,";
        $sql .= "\n`AutoLookup` TINYINT UNSIGNED NULL,";
        $sql .= "\n`AutoPic` TINYINT UNSIGNED NULL,";
        $sql .= "\n`Dunno1` VARCHAR(8) NULL,";
        $sql .= "\n`DefDispLen` SMALLINT UNSIGNED NULL,";
        $sql .= "\n`UseDispLen` SMALLINT UNSIGNED NULL,";
        $sql .= "\n`Dunno2` VARCHAR(8) NULL,";
        $sql .= "\n`DecPlaces` SMALLINT UNSIGNED NULL,";
        $sql .= "\nPRIMARY KEY (`Table`, `Field`)";
        $sql .= "\n);\n";
        return $sql;
    }

    static public function InfoSqlInsert($sqlTableName, $tableName, $fields)
    {
        $sqls = [];
        foreach($fields as $field){
            $sql = "('{$tableName}', ";
            $sql .= "'{$field->name}', ";
            $sql .= "'{$field->type}', ";
            $sql .= "'{$field->len}', ";
            $sql .= "'{$field->isKey}', ";
            $sql .= "'{$field->num}', ";
            $sql .= "'{$field->required}', ";
            $sql .= "'{$field->default}', ";
            $sql .= "'{$field->picture}', ";
            $sql .= "'{$field->loVal}', ";
            $sql .= "'{$field->hiVal}', ";
            $sql .= "'{$field->lookupTable}', ";
            $sql .= "'{$field->fillType}', ";
            $sql .= "'{$field->autoFill}', ";
            $sql .= "'{$field->autoLookup}', ";
            $sql .= "'{$field->autoPic}', ";
            $sql .= "'{$field->dunno1}', ";
            $sql .= "'{$field->defDispLen}', ";
            $sql .= "'{$field->useDispLen}', ";
            $sql .= "'{$field->dunno2}', ";
            $sql .= "'{$field->decPlaces}')";
            $sqls[] = $sql;
        }
        $fields = "`Table`, `Field`, `Type`, `Len`, `IsKey`, `Num`, `Required`, `Default`, `Picture`, `LoVal`, `HiVal`, `LookupTable`, `FillType`, `AutoFill`, `AutoLookup`, `AutoPic`, `Dunno1`, `DefDispLen`, `UseDispLen`, `Dunno2`, `DecPlaces`";
        return "\nINSERT INTO `{$sqlTableName}` \n($fields) \nVALUES\n" . implode(",\n", $sqls) . ";\n";
    }
}