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

class PXparseForm extends PXparse
{

    /** @var array */
    public $tableColumnSpecs = [];
    /** @var PXformHeader */
    public $formHeader = null;
    /** @var  PXformPage[] */
    public $pages = [];
    /** @var PXembeddedFormSpec[] */
    public $embeddedFormSpecs = [];
    /** @var string[] */
    protected $tableColumnNames = [];

    /**
     * @param string $fName
     *
     * @return PXparseForm | false
     */
    public function ParseFile($fName)
    {

        if ( ! $this->Open($fName)) {
            return false;
        }

        $this->tableColumnNames = $this->GetFieldNames2(); // sets $this->fieldCount, $this->fieldNames

        $tableColumnCount = count($this->tableColumnNames);

        $this->formHeader = $this->Header();

        $numPages = $this->formHeader->numPages;

        for ($i = 0; $i < $numPages; $i++) {

            $page = new PXformPage($i);
            $this->pages[$i] = $page;

            $page->header = $this->PageHeader($page);
            $page->formFields = $this->PageFields($page->numRegularFields, $page->numOtherFields);

            list($page->chrMap, $unused) = $this->CharMap($page->linesPerPage);
        }

        for ($i = 0; $i < $this->formHeader->embeddedFormsCount; $i++) {
            $specs = $this->EmbeddedFormSpecs();
            $this->embeddedFormSpecs[] = $specs;
        }

        for ($i = 0; $i < $numPages; $i++) {
            $numLines = $this->pages[$i]->linesPerPage;
            list($this->pages[$i]->cmapLines, $this->pages[$i]->fldStyles) = $this->CharMap($numLines);
        }

        $this->tableColumnSpecs = $this->TableColumnSpecs();

        for ($i = 0; $i < $numPages; $i++) {
            $style = [];
            foreach ($this->pages[$i]->formFields as &$field) {
                var_dump($field->fieldNum);
                var_dump($field->fieldNum != 256 ? $this->tableColumnSpecs[$field->fieldNum - 1] : '');
                if ($field->fieldNum != 256) {
                    $field->tableColType = $this->tableColumnSpecs[$field->fieldNum - 1]['type'];
                    $field->tableColSize = $this->tableColumnSpecs[$field->fieldNum - 1]['size'];
                }
                if (isset($this->pages[$i]->fldStyles[$field->y - 1][$field->x - 1])) {
                    /*
                     * x fldstyles are areas, a field may not necessarily start at the edge of the style area
                     */
                    $style = $this->pages[$i]->fldStyles[$field->y - 1][$field->x - 1];
                }
                $field->SetStyle($style);
            }
        }
        $this->Close();
        return true;
    }

    public function GetFieldNames2()
    {
        $rest = fread($this->handle, 16384);
        $fpos = strrpos($rest, "\x00\x00") + 2;
        $fieldNamesStr = rtrim(substr($rest, $fpos));
        $tableColumnNames = explode("\x00", $fieldNamesStr);
        fseek($this->handle, 0);
        return $tableColumnNames;
    }

    /**
     * @return PXformHeader
     */
    private function Header()
    {
        $head = new PXformHeader();
        $head->type = $this->Hex(1);
        $head->dunno0 = $this->Hex(1);
        $head->desc = strstr($this->Raw(40), "\x00", true);
        $head->dunno1 = $this->Hex(1);
        $head->formExtentY = $this->Dec(1);
        $head->formExtentX = $this->Dec(1);
        $chunk = $this->Hex(1);
        if ($head->type != '19') {
            $head->embeddedFormsCount = hexdec($chunk);
        } else {
            $head->dunno2 = hexdec($chunk);
        }
        $head->repeatCount = $this->Dec(1);
        $head->repeatLines = $this->Dec(1);
        $head->dunno3 = $this->Dec(1);
        $head->repeatExtentY = $this->Dec(1) + 1;
        $head->repeatExtentX = $this->Dec(1) + 1;
        $head->dunno4 = $this->Dec(1);
        $chunk = $this->Hex(1);
        if ($head->type == '19') {
            $head->embeddedFormsCount = hexdec($chunk);
        } else {
            $head->dunno5 = $chunk;
        }
        $head->gap = $this->Hex(1);
        $head->tableFieldsPlusSomething = $this->Dec(1);
        $chunk = $this->Raw(38); //??
        $head->dunno6 = chunk_split(bin2hex($chunk), 2, ' ');
        $head->numPages = $this->Dec(1);
        $head->regularFields = $this->Dec(1);
        $head->otherFields = $this->Dec(1);
        return $head;
    }

    /**
     * @param PXformPage $page
     */
    private function PageHeader(PXformPage $page)
    {
        $page->primaryFieldsMaybe = $this->Dec(1);
        $page->linesPerPage = $this->Dec(1);
        $page->dunno1 = $this->Hex(1);
        $page->dunno2 = $this->Dec(1);
        $page->numRegularFields = $this->Dec(1);
        $page->numOtherFields = $this->Dec(1);
    }

    private function Field($regularFieldsOnly)
    {
        $field = new PXformDefField();
        $field->y = $this->Dec(1) + 1;
        $field->x = $this->Dec(1) + 1;
        $field->end = $this->Dec(1) + 1;
        $field->len = $field->end - $field->x + 1;
        $field->fieldNum = $this->Dec(1) + 1;
        $this->Hex(1);

        $this->Hex(1);

        $isCalculated = false;
        $field->detail = '';
        if ($regularFieldsOnly) {
            $field->name = $this->tableColumnNames[$field->fieldNum - 1];
            $field->type = 'regular';
        } else {
            if ($field->fieldNum == 256) {
                $isCalculated = true;
                $field->type = 'calculated';

                $this->Raw(2);
                $len = $this->Dec(1);
                $this->Raw(1);

                $field->detail = $this->Raw($len);
            } elseif ($field->fieldNum > count($this->tableColumnNames)) {
                $field->type = 'recordNum';
            } else {
                $field->type = 'dispOnly';
                $field->detail = $this->tableColumnNames[$field->fieldNum - 1];
                $field->name = $this->tableColumnNames[$field->fieldNum - 1];
            }
        }
        if ( ! $isCalculated) {
            $chunk = $this->Hex(4);
        }
        return $field;
    }

    /**
     * @param $numRegularFields
     * @param $numOtherFields
     *
     * @return array
     *
     */
    private function PageFields($numRegularFields, $numOtherFields)
    {
        $formFields = [];
        for ($i = 0; $i < $numRegularFields; $i++) {
            $formFields[] = $this->Field(true);
        }
        for ($i = 0; $i < $numOtherFields; $i++) {
            $formFields[] = $this->Field(false);
        }
        return $formFields;
    }

    private function TableColumnSpecs()
    {
        $this->Raw(6);
        $fieldSpecs = [[]];
        $fldNums = [];
        $tableColumnCount = count($this->tableColumnNames);
        for ($i = 0; $i < $tableColumnCount; $i++) {
            $fldNums[$i] = $this->Dec(1);
            $fieldSpecs[$i]['name'] = $this->tableColumnNames[$i];
            $this->Hex(1);
        }
        for ($i = 0; $i < $tableColumnCount; $i++) {
            $type = (int)$this->Dec(1);
            $fieldSpecs[$i]['type'] = parent::FIELD_TYPES[$type];
            $fieldSpecs[$i]['size'] = $this->Dec(1);
        }
        return $fieldSpecs;
    }

    private function CharMap($pageLines)
    {
        $colours = [
            0  => 'Black',
            1  => 'Blue',
            2  => 'Green',
            3  => 'Cyan',
            4  => 'Red',
            5  => 'Magenta',
            6  => 'Brown',
            7  => 'LtGrey',
            8  => 'Grey',
            9  => 'LtBlue',
            10 => 'LtGreen',
            11 => 'LtCyan',
            12 => 'LtRed',
            13 => 'LtMagenta',
            14 => 'Yellow',
            15 => 'White'
        ];
        $getStyle = function ($dataDec) use ($colours) {
            $colorFg = $colours[$dataDec & 0x0f];
            $colorBg = $colours[($dataDec >> 4) & 0x0f];
            //$mono = true && $dataDec & 0x10 ? 'Yes' : 'No';
            return [$colorFg, $colorBg];
        };
        $lineNum = 0;
        $chrMap = [[]];
        $prevStyle = [];
        $key = null;
        $x = 0;
        $fldStyles = [[]];
        while (1) {
            $input = $this->Raw(1);
            if (bin2hex($input) == "7f") {
                $ctrl = $this->Hex(1);
                switch ($ctrl) {
                    case '45' :
                        // EOL
                        if (++$lineNum < $pageLines) {
                            $prevStyle = [];
                            $key = null;
                            $chrMap[$lineNum] = [];
                            $x = 0;
                            continue 2;
                        } else {
                            break 2;
                        }
                    case '4c' :
                        // multiple of a character, all field boundaries and spaces
                        $count = $this->Dec(1);
                        $this->Hex(1);
                        $data = str_repeat(htmlspecialchars(CodePage850::CP850toUTF8($this->Raw(1))), $count);
                        $style = $getStyle($this->Dec(1));
                        $fldStyles[$lineNum][$x] = $style;
                        $x += $count;
                        break;
                    default:
                        $this->Close();
                        exit('expected 4c');
                }
            } else {
                // single character
                //$data = $input;
                $data = htmlspecialchars(CodePage850::CP850toUTF8($input));
                $style = $getStyle($this->Dec(1));
                $x++;
            }
            if ($style != $prevStyle) {
                $prevStyle = $style;
                $map = new \stdClass();
                $map->data = $data;
                $map->fg = $style[0];
                $map->bg = $style[1];
                $key = array_push($chrMap[$lineNum], $map) - 1;
            } else {
                $chrMap[$lineNum][$key]->data .= $data;
            }
        }
        return [$chrMap, $fldStyles];
    }

    /**
     * @return PXembeddedFormSpec
     */
    private function EmbeddedFormSpecs()
    {
        $spec = new PXembeddedFormSpec();
        $embeddedForm = [];
        $NullTerm = function ($str) {
            return strstr($str, "\00", true);
        };
        //header
        $spec->type = $this->Hex(1);
        $this->Hex(1);
        $len = $this->Dec(1);
        $this->Hex(1);

        $tableName = $NullTerm($this->Raw(79));
        $slashPos = strrpos($tableName, "\\");
        if ($slashPos !== false) {
            $spec->tableName = substr($tableName, $slashPos + 1);
            $spec->database = substr($tableName, 0, $slashPos);
        } else {
            $spec->tableName = $tableName;
            $spec->database = '';
        }

        $this->Hex(1);
        $spec->form = $NullTerm($this->Raw(4));
        $spec->formNum = (int)substr($spec->form, 1); // todo length 1 only allows for form nums 1-9, can be 1-14

        $spec->tableForm = "{$spec->tableName}.{$spec->form}";

        $this->Hex($len - 84 - 10);

        $spec->pageNum = $this->Dec(1); // the page of the master form on which the detail form appears (1 to n)
        $this->Hex(1);
        $spec->y = $this->Dec(1) + 1;
        $this->Hex(1);
        $spec->x = $this->Dec(1) + 1;
        $this->Hex(1);
        $spec->isLinked = $this->Hex(1) == '01' ? 'Y' : 'N';
        $this->Hex(1);
        $spec->numLinkFields = $this->Dec(1); // the first n index fields that link to master
        $this->Hex(1);

        if ($spec->isLinked == 'Y') {
            $spec->type = $this->Hex(1);
            $this->Hex(1);
            $len = $this->Dec(1);
            $this->Hex(1);
            $this->Raw($len);
        }
        return $spec;
    }

    public function Draw()
    {
        echo "<br>Draw function not yet available<br>";
    }
}
