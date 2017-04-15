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

class CodePage850
{

    /**
     * A map of extended ASCII CP-850 (MS-DOS Latin 1) table to Unicode hex
     *
     * @var array
     */
    const conversionTable = [
        128 => '00C7', // Ç
        129 => '00FC', // ü
        130 => '00E9', // é
        131 => '00E2', // â
        132 => '00E4', // ä
        133 => '00E0', // à
        134 => '00E5', // å
        135 => '00E7', // ç
        136 => '00EA', // ê
        137 => '00EB', // ë
        138 => '00E8', // è
        139 => '00EF', // ï
        140 => '00EE', // î
        141 => '00EC', // ì
        142 => '00C4', // Ä
        143 => '00C5', // Å
        144 => '00C9', // É
        145 => '00E6', // æ
        146 => '00C6', // Æ
        147 => '00F4', // ô
        148 => '00F6', // ö
        149 => '00F2', // ò
        150 => '00FB', // û
        151 => '00F9', // ù
        152 => '00FF', // ÿ
        153 => '00D6', // Ö
        154 => '00DC', // Ü
        155 => '00F8', // ø
        156 => '00A3', // £
        157 => '00D8', // Ø
        158 => '00D7', // ×
        159 => '0192', // ƒ
        160 => '00E1', // á
        161 => '00ED', // í
        162 => '00F3', // ó
        163 => '00FA', // ú
        164 => '00F1', // ñ
        165 => '00D1', // Ñ
        166 => '00AA', // ª
        167 => '00BA', // º
        168 => '00BF', // ¿
        169 => '00AE', // ®
        170 => '00AC', // ¬
        171 => '00BD', // ½
        172 => '00BC', // ¼
        173 => '00A1', // ¡
        174 => '00AB', // «
        175 => '00BB', // »
        176 => '2591', // ░
        177 => '2592', // ▒
        178 => '2593', // ▓
        179 => '2502', // │
        180 => '2524', // ┤
        181 => '00C1', // Á
        182 => '00C2', // Â
        183 => '00C0', // À
        184 => '00A9', // ©
        185 => '2563', // ╣
        186 => '2551', // ║
        187 => '2557', // ╗
        188 => '255D', // ╝
        189 => '00A2', // ¢
        190 => '00A5', // ¥
        191 => '2510', // ┐
        192 => '2514', // └
        193 => '2534', // ┴
        194 => '252C', // ┬
        195 => '251C', // ├
        196 => '2500', // ─
        197 => '253C', // ┼
        198 => '00E3', // ã
        199 => '00C3', // Ã
        200 => '255A', // ╚
        201 => '2554', // ╔
        202 => '2569', // ╩
        203 => '2566', // ╦
        204 => '2560', // ╠
        205 => '2550', // ═
        206 => '256C', // ╬
        207 => '00A4', // ¤
        208 => '00F0', // ð
        209 => '00D0', // Ð
        210 => '00CA', // Ê
        211 => '00CB', // Ë
        212 => '00C8', // È
        213 => '0131', // ı
        214 => '00CD', // Í
        215 => '00CE', // Î
        216 => '00CF', // Ï
        217 => '2518', // ┘
        218 => '250C', // ┌
        219 => '2588', // █
        220 => '2584', // ▄
        221 => '00A6', // ¦
        222 => '00CC', // Ì
        223 => '2580', // ▀
        224 => '00D3', // Ó
        225 => '00DF', // ß
        226 => '00D4', // Ô
        227 => '00D2', // Ò
        228 => '00F5', // õ
        229 => '00D5', // Õ
        230 => '00B5', // µ
        231 => '00FE', // þ
        232 => '00DE', // Þ
        233 => '00DA', // Ú
        234 => '00DB', // Û
        235 => '00D9', // Ù
        236 => '00FD', // ý
        237 => '00DD', // Ý
        238 => '00AF', // ¯
        239 => '00B4', // ´
        240 => '00AD', // SHY
        241 => '00B1', // ±
        242 => '2017', // ‗
        243 => '00BE', // ¾
        244 => '00B6', // ¶
        245 => '00A7', // §
        246 => '00F7', // ÷
        247 => '00B8', // ¸
        248 => '00B0', // °
        249 => '00A8', // ¨
        250 => '00B7', // ·
        251 => '00B9', // ¹
        252 => '00B3', // ³
        253 => '00B2', // ²
        254 => '25A0', // ■
        255 => '00A0', // NBSP
    ];

    /**
     * Convert CP-850 (MS-DOS Latin 1) characters to UTF-8 (Unicode)
     *
     * @param string $string CP-850 (MS-DOS Latin 1) string to convert
     *
     * @return string UTF-8 converted string
     */
    public static function CP850toUTF8($string)
    {
        $newString = '';
        $limit = mb_strlen($string);
        for ($i = 0; $i < $limit; $i++) {
            $c = mb_substr($string, $i, 1);
            $ascii = ord($c);
            if ($ascii > 127) {
                $hex = 'FFFD'; // replacement character
                if ($ascii < 256) {
                    $hex = self::conversionTable[$ascii];
                }
                $c = mb_convert_encoding('&#x' . $hex . ';', 'UTF-8', 'HTML-ENTITIES');
            }
            $newString .= $c;
        }
        return $newString;
    }

}
