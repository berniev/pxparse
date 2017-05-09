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

namespace PX\classes\Form;

class PXembeddedFormSpec
{

    /** @var  string */
    public $type = '';

    /** @var string */
    public $tableName = '';

    /** @var string */
    public $database = '';

    /** @var string */
    public $form = '';

    /** @var int */
    public $formNum = 0;

    /** @var string */
    public $tableForm = '';

    /** @var int */
    public $pageNum = 0;

    /** @var int */
    public $y = 0;

    /** @var int */
    public $x = 0;

    /** @var string */
    public $isLinked = '';

    /** @var int */
    public $numLinkFields = 0;

}
