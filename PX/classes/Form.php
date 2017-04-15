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

abstract class Form
{

    /** @var string */
    public $tableName = '';

    /** @var int */
    public $formNum = 0;

    /** @var array */
    public $tableColumnSpecs = [];

    /** @var \PX\classes\PXformField[]   NB: [] = use ~all~ fields */
    public $fields = [];

    /** @var PXformPage */
    public $currPage = null;

    /** @var int */
    public $numRows = 0;

    /** @var int */
    public $x = 1;
    /** @var int */
    public $y = 1;

    /** @var int */
    public $w = 0;

    /** @var int */
    public $h = 0;

    /** @var int */
    public $repeatCount = 0;
    /** @var int */
    public $repeatLines = 0;
    /** @var int */
    public $repeatExtentY = 0;
    /** @var int */
    public $repeatExtentX = 0;
    /** @var int */
    public $numRegularFields = 0;
    /** @var int */
    public $numOtherFields = 0; 

    /**
     * @param string $tableName
     * @param int    $formNum
     * @param int    $numRegularFields
     * @param int    $numOtherFields
     * @param int    $repeatCount
     * @param int    $repeatLines
     * @param int    $repeatExtentY
     * @param int    $repeatExtentX
     * @param array  $colSpecs
     * @param array  $pages
     * @param int    $w
     * @param int    $h
     * @param int    $x
     * @param int    $y
     *
     */

    public function __construct(
        $tableName,
        $formNum,
        $numRegularFields,
        $numOtherFields,
        $repeatCount,
        $repeatLines,
        $repeatExtentY,
        $repeatExtentX,
        array $colSpecs,
        array $pages,
        $w,
        $h,
        $x,
        $y
    ) {
        $this->tableName = $tableName;
        $this->formNum = $formNum;
        $this->numRegularFields = $numRegularFields;
        $this->numOtherFields = $numOtherFields;
        $this->repeatCount = $repeatCount;
        $this->repeatLines = $repeatLines;
        $this->repeatExtentY = $repeatExtentY;
        $this->repeatExtentX = $repeatExtentX;
        $this->tableColumnSpecs = $colSpecs;
        $this->pages = $pages;
        $this->w = $w;
        $this->h = $h;
        $this->x = $x;
        $this->y = $y;

        $this->currPage = $this->pages[0];
    }

    /**
     * @return int
     */
    public function GetFormNum()
    {
        return $this->formNum;
    }

    /**
     * PAL is 1 to n
     *
     * @return int
     */
    public function GetPageNum()
    {
        return $this->currPage->num + 1;
    }

}
