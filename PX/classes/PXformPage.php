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

class PXformPage
{

    /** @var int */
    public $num = 0; // 0 to n-1

    /** @var Form[] */
    public $detailForms = [];

    /** @var array */
    public $chrMap = [];

    /** @var array */
    public $cmapLines = [];

    /** @var PXformDefField[] */
    public $formFields = [];

    /** @var array */
    public $fldStyles;

    public $primaryFieldsMaybe = 0;

    public $linesPerPage = 0;

    public $dunno1 = '';

    public $dunno2 = 0;

    public $numRegularFields = 0;

    public $numOtherFields = 0;

    public $header = null;

    public function __Construct($num)
    {
        $this->num = $num;
    }
}

