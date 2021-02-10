<?php

/**
 * @copyright Copyright (c) 2021 Afterpay Corporate Services Pty Ltd
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Afterpay\SDK\Test;

require_once __DIR__ . '/autoload.php';

use PHPUnit\Framework\TestCase;

class Sample extends TestCase
{
    private $sample_dir = __DIR__ . '/../sample';

    protected $expected_files = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function compareExpectedOutputToActualOutput()
    {
        $tested_files = [];

        foreach ($this->expected_files as $path) {
            $abs_path = realpath("{$this->sample_dir}/{$path}");

            /**
             * Read the comments in the sample code to determine the expected output.
             */

            $expected_output_strings = [];
            $expected_output_patterns = [];
            $tokens = token_get_all(file_get_contents($abs_path));

            foreach ($tokens as $token) {
                if (! is_array($token) || $token[ 0 ] != T_COMMENT) {
                    continue;
                }

                $matches = [];
                if (preg_match('/^\/\*=\s(.*\s+)=\*\/$/ms', $token[ 1 ], $matches)) {
                    $expected_output_strings[] = $matches[ 1 ];
                } elseif (preg_match('/^\/\*~\s(.*\s+)~\*\/$/ms', $token[ 1 ], $matches)) {
                    $expected_output_patterns[] = $matches[ 1 ];
                }
            }

            /**
             * Execute the sample code to determine the actual output.
             */

            ob_start();
            include $abs_path;
            $actual_output = ob_get_clean();

            /**
             * Assert that the sample code behaves as expected.
             */

            if (method_exists($this, 'assertMatchesRegularExpression')) {
                $phpunit_regex_method = 'assertMatchesRegularExpression';
            } else {
                $phpunit_regex_method = 'assertRegExp';
            }

            if (count($expected_output_strings) > 0 && count($expected_output_patterns) == 0) {
                $this->assertEquals(implode('', $expected_output_strings), $actual_output);
            } elseif (count($expected_output_strings) == 0 && count($expected_output_patterns) > 0) {
                $this->{$phpunit_regex_method}('/^' . implode('', $expected_output_patterns) . '$/', $actual_output);
            } elseif (count($expected_output_strings) > 0 && count($expected_output_patterns) > 0) {
                throw new \Exception('Cannot mix string and pattern expectations in the same sample file');
            } else {
                throw new \Exception('No expectations found in the sample file');
            }

            $tested_files[] = $path;
        }

        $this->assertEquals($this->expected_files, $tested_files);
    }
}
