<?php

/**
 * @copyright Copyright (c) 2020-2021 Afterpay Corporate Services Pty Ltd
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

namespace Afterpay\SDK\Shared;

use Afterpay\SDK\Exception\InternalException;
use Afterpay\SDK\Exception\InvalidArgumentException;
use Afterpay\SDK\Exception\InvalidModelException;
use Afterpay\SDK\Model;

trait ModelMethods
{
    /**
     * @param string $propertyName
     * @return mixed
     */
    private function getProperty($propertyName)
    {
        if (! array_key_exists('value', $this->data[ $propertyName ])) {
            if (array_key_exists('default', $this->data[ $propertyName ])) {
                return $this->data[ $propertyName ][ 'default' ];
            }

            return null;
        }

        return $this->data[ $propertyName ][ 'value' ];
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     * @return object
     */
    private function setProperty($propertyName, ...$value)
    {
        if (method_exists($this, 'filterBeforeSet' . ucfirst($propertyName))) {
            $value = $this->{ 'filterBeforeSet' . ucfirst($propertyName) }(... $value);
        }

        $this->clearErrors($propertyName);

        $values = count($value);
        $property = &$this->data[ $propertyName ];

        if ($values == 1) {
            $value = $value[ 0 ];
            $actualType = gettype($value);
        } elseif ($values > 1) {
            if (array_key_exists('type', $property)) {
                $expectedType = $property[ 'type' ];

                if (class_exists($expectedType)) {
                    $value = new $expectedType(... $value);
                    $actualType = gettype($value);
                }
            }
        }

        if ($actualType == 'object') {
            $actualType = get_class($value);

            if (array_key_exists('type', $property)) {
                $expectedType = $property[ 'type' ];

                if (class_exists($expectedType) && $value instanceof \StdClass) {
                    $value = new $expectedType($value);

                    $actualType = get_class($value);
                }
            }
        }

        if (array_key_exists('type', $property)) {
            $expectedType = $property[ 'type' ];

            $matches = [];

            if ($actualType == 'array' && preg_match('/^([A-Z][A-Za-z\\\]+)(\[\])$/', $expectedType, $matches)) {
                $allElementsValid = true;

                foreach ($value as &$element) {
                    if (! is_a($element, $matches[ 1 ])) {
                        if (is_array($element) || $element instanceof \StdClass) {
                            try {
                                $element = new $matches[ 1 ]($element);
                            } catch (InvalidModelException $e) {
                                $allElementsValid = false;
                            }
                        } else {
                            $allElementsValid = false;
                        }
                    }
                }

                if ($allElementsValid) {
                    $actualType = $expectedType;
                }
            } elseif ($actualType == 'array' && class_exists($expectedType)) {
                $value = new $expectedType($value);
                $actualType = get_class($value);
            } elseif ($actualType == 'string' && class_exists($expectedType) && is_array(json_decode($value, true))) {
                $value = new $expectedType($value);
                $actualType = get_class($value);
            } elseif ($expectedType == 'boolean') {
                $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if (!is_null($filteredValue)) {
                    $value = $filteredValue;
                    $actualType = gettype($value);
                }
            }
        } else {
            $expectedType = $actualType;
        }

        if (! is_null($value) || (array_key_exists('required', $property) && $property[ 'required' ])) {
            if (
                array_key_exists('type', $property)
                && $property[ 'type' ] == 'enumi'
                && array_key_exists('options', $property)
                && is_array($property[ 'options' ])
            ) {
                $valueMatchesValidOption = false;

                if ($actualType == 'string') {
                    foreach ($property[ 'options' ] as $optionKey => $optionValue) {
                        if (is_string($optionValue)) {
                            if (strtolower($value) == strtolower($optionValue)) {
                                $valueMatchesValidOption = true;
                            }
                        } else {
                            throw new InternalException('The "enumi" data type supports case insensitive string options only');
                        }
                    }
                }

                if (! $valueMatchesValidOption) {
                    $error = 'Expected one of "';
                    $error .= implode('", "', $property[ 'options' ]);
                    $error .= '" for ' . get_class($this) . "::\${$propertyName}; ";

                    if ($actualType == 'string') {
                        $error .= "\"{$value}\" given";
                    } else {
                        $error .= "{$actualType} given";
                    }

                    if (Model::getAutomaticValidationEnabled()) {
                        throw new InvalidModelException($error);
                    } else {
                        $this->addError($error, $propertyName);
                    }
                }
            } elseif ($expectedType != $actualType) {
                $error = "Expected {$expectedType} for " . get_class($this) . "::\${$propertyName}; {$actualType} given";

                if (Model::getAutomaticValidationEnabled()) {
                    throw new InvalidModelException($error);
                } else {
                    $this->addError($error, $propertyName);
                }
            }
        }

        if ($expectedType == 'string') {
            if (array_key_exists('length', $property) && $actualType == $expectedType) {
                if (! is_int($property[ 'length' ])) {
                    throw new InvalidArgumentException("Expected integer for 'length' of '{$propertyName}' in " . get_class($this));
                }

                $expectedLength = $property[ 'length' ];
                $actualLength = strlen($value);

                if ($actualLength > $expectedLength) {
                    $error = "Expected maximum of {$expectedLength} characters for " . get_class($this) . "::\${$propertyName}; {$actualLength} characters given";

                    if (Model::getStringTruncationEnabled()) {
                        $value = substr($value, 0, $expectedLength);
                    } elseif (Model::getAutomaticValidationEnabled()) {
                        throw new InvalidModelException($error);
                    } else {
                        $this->addError($error, $propertyName);
                    }
                }
            }
        } elseif ($expectedType == 'integer') {
            if (array_key_exists('min', $property) && $actualType == $expectedType) {
                if (! is_int($property[ 'min' ])) {
                    throw new InvalidArgumentException("Expected integer for 'min' of '{$propertyName}' in " . get_class($this));
                }

                $expectedMin = $property[ 'min' ];

                if ($value < $expectedMin) {
                    $error = "Expected minimum of {$expectedMin} for " . get_class($this) . "::\${$propertyName}; {$value} given";

                    if (Model::getAutomaticValidationEnabled()) {
                        throw new InvalidModelException($error);
                    } else {
                        $this->addError($error, $propertyName);
                    }
                }
            }
            if (array_key_exists('max', $property) && $actualType == $expectedType) {
                if (! is_int($property[ 'max' ])) {
                    throw new InvalidArgumentException("Expected integer for 'max' of '{$propertyName}' in " . get_class($this));
                }

                $expectedMax = $property[ 'max' ];

                if ($value > $expectedMax) {
                    $error = "Expected maximum of {$expectedMax} for " . get_class($this) . "::\${$propertyName}; {$value} given";

                    if (Model::getAutomaticValidationEnabled()) {
                        throw new InvalidModelException($error);
                    } else {
                        $this->addError($error, $propertyName);
                    }
                }
            }
        }

        $this->data[ $propertyName ][ 'value' ] = $value;

        if (method_exists($this, 'afterSet')) {
            $this->{ 'afterSet' }($propertyName);
        }

        return $this;
    }

    private function passConstructArgsToMagicSetters(...$args)
    {
        if (is_array($args[ 0 ]) || is_object($args[ 0 ])) {
            foreach ($args[ 0 ] as $propertyName => $value) {
                if (is_numeric($propertyName)) {
                    $propertyName = array_keys($this->data)[ $propertyName ];
                }

                $this->{ 'set' . ucfirst($propertyName) }($value);
            }
        } else {
            $jsonInterpretation = is_string($args[ 0 ]) ? json_decode($args[ 0 ], true) : null;

            if (is_array($jsonInterpretation)) {
                foreach ($jsonInterpretation as $propertyName => $value) {
                    $this->{ 'set' . ucfirst($propertyName) }($value);
                }
            } elseif (property_exists($this, 'data')) {
                $i = 0;

                foreach ($this->data as $propertyName => $propertyData) {
                    if (! is_null($args[ $i ])) {
                        $this->{ 'set' . ucfirst($propertyName) }($args[ $i ]);
                    }

                    $i++;

                    if ($i >= count($args)) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param string $propertyName
     */
    protected function clearErrors($propertyName = null)
    {
        if (! is_null($propertyName)) {
            if (array_key_exists('errors', $this->data[ $propertyName ])) {
                $this->data[ $propertyName ][ 'errors' ] = [];
            }
        } else {
            foreach ($this->data as $propertyName => $propertyData) {
                $this->clearErrors($propertyName);
            }
        }
    }

    /**
     * @param string $error
     * @param string $propertyName
     */
    protected function clearError($error, $propertyName)
    {
        if (array_key_exists('errors', $this->data[ $propertyName ])) {
            $key = array_search($error, $this->data[ $propertyName ][ 'errors' ]);
            if (is_int($key)) {
                array_splice($this->data[ $propertyName ][ 'errors' ], $key, 1);
            }
        }
    }

    /**
     * @param string $error
     * @param string $propertyName
     */
    protected function addError($error, $propertyName)
    {
        if (! array_key_exists('errors', $this->data[ $propertyName ])) {
            $this->data[ $propertyName ][ 'errors' ] = [];
            $key = false;
        } else {
            $key = array_search($error, $this->data[ $propertyName ][ 'errors' ]);
        }

        if ($key === false) {
            $this->data[ $propertyName ][ 'errors' ][] = $error;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        $matches = [];

        if (preg_match('/^([a-z]+)(.*)$/', $name, $matches)) {
            $command = $matches[ 1 ];
            $propertyName = lcfirst($matches[ 2 ]);

            if (! in_array($command, [ 'get', 'set' ]) || ! property_exists($this, 'data') || ! array_key_exists($propertyName, $this->data)) {
                throw new \Exception('Call to undefined method ' . get_class($this) . "::{$name}");
            }

            switch ($command) {
                case 'get':
                    return $this->getProperty($propertyName);
                break;

                case 'set':
                    return $this->setProperty($propertyName, ... $arguments);
                break;
            }
        }
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return count($this->getValidationErrors()) == 0;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $return = [];

        if (! property_exists($this, 'data')) {
            # For GET requests, there is no request body to validate.

            return $return;
        }

        foreach ($this->data as $name => $data) {
            if (array_key_exists('errors', $data) && ! empty($data[ 'errors' ])) {
                $return[ $name ] = $data[ 'errors' ];
            } elseif (
                array_key_exists('required', $data)
                && $data[ 'required' ]
                && is_null($this->getProperty($name))
            ) {
                $return[ $name ] = [ 'Required property missing: ' . get_class($this) . '::$' . $name ];
            }

            if (array_key_exists('value', $data)) {
                if (
                    is_object($data[ 'value' ])
                    && method_exists($data[ 'value' ], 'getValidationErrors')
                ) {
                    $errors = $data[ 'value' ]->getValidationErrors();

                    if (count($errors) > 0) {
                        $return[ $name ] = $errors;
                    }
                } elseif (is_array($data[ 'value' ])) {
                    foreach ($data[ 'value' ] as $key => $value) {
                        if (
                            is_object($value)
                            && method_exists($value, 'getValidationErrors')
                        ) {
                            $errors = $value->getValidationErrors();

                            if (count($errors) > 0) {
                                $return[ "{$name}[{$key}]" ] = $errors;
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }

    /**
     * @param array $errors
     * @param int $indent
     * @return string
     */
    public function getValidationErrorsAsHtml($errors = null, $indent = 0)
    {
        if ($this->isValid()) {
            return '';
        }

        $tab_str = str_repeat(' ', 4);
        $indent_str = str_repeat($tab_str, $indent);

        $return = "{$indent_str}<ul>\n";

        if (is_null($errors)) {
            $errors = $this->getValidationErrors();
        }

        foreach ($errors as $field_name_str => $field_errors_arr) {
            $return .= "{$indent_str}{$tab_str}<li>{$field_name_str}:</li>\n";

            if (count(array_filter(array_keys($field_errors_arr), 'is_string')) > 0) {
                # Associative array

                $return .= $this->getValidationErrorsAsHtml($field_errors_arr, $indent + 1);
            } else {
                # Array with numeric keys

                $return .= "{$indent_str}{$tab_str}<ul>\n";

                foreach ($field_errors_arr as $subfield_name_str => $field_error) {
                    $return .= "{$indent_str}{$tab_str}{$tab_str}<li>{$field_error}</li>\n";
                }

                $return .= "{$indent_str}{$tab_str}</ul>\n";
            }
        }

        $return .= "{$indent_str}</ul>\n";

        return $return;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = [];

        if (property_exists($this, 'data')) {
            foreach ($this->data as $propertyName => $propertyData) {
                if (array_key_exists('value', $propertyData)) {
                    $data[ $propertyName ] = $propertyData[ 'value' ];
                } elseif (array_key_exists('default', $propertyData) && ! is_null($propertyData[ 'default' ])) {
                    $data[ $propertyName ] = $propertyData[ 'default' ];
                } elseif (array_key_exists('required', $propertyData) && $propertyData[ 'required' ]) {
                    // Invalid model.
                    // May want to throw an exception here if auto validation is enabled,
                    // but this would only apply to required props that are missing.
                }
            }
        }

        return $data;
    }
}
