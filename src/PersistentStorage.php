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

namespace Afterpay\SDK;

use Afterpay\SDK\Config;
use Afterpay\SDK\Exception;
use Afterpay\SDK\MerchantAccount;
use Afterpay\SDK\Model\Money;
use Afterpay\SDK\HTTP\Request\GetConfiguration as GetConfigurationRequest;

final class PersistentStorage
{
    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function get($property, $merchant = null)
    {
        $instance = self::getInstance();

        return $instance->getProperty($property, $merchant);
    }

    public static function disconnect()
    {
        self::$instance = null;
    }

    public static function testConnection()
    {
        $instance = self::getInstance();

        if ($instance->db_api == 'mysqli') {
            if ($instance->db_connection instanceof \mysqli) {
                $instance = null;
                self::disconnect();

                return true;
            }
        }

        return false;
    }

    /**
     * @var array $data
     */
    private $data = [
        /**
         * These are properties of a MerchantAccount.
         *
         * @see \Afterpay\SDK\Config
         */
        'orderMinimum' => [
            'type' => Money::class,
            'lifespan' => 900 // 15min (60 * 15)
        ],
        'orderMaximum' => [
            'type' => Money::class,
            'lifespan' => 900 // 15min (60 * 15)
        ]
    ];

    private $db_api;
    private $db_host;
    private $db_database;
    private $db_tablePrefix;
    private $db_user;
    private $db_pass;
    private $db_connection;

    public function __construct()
    {
        //echo get_class( $this ) . "::__construct()\n";

        $this->db_api = Config::get('db.api');
        $this->db_host = Config::get('db.host');
        $this->db_port = Config::get('db.port');
        $this->db_database = Config::get('db.database');
        $this->db_tablePrefix = Config::get('db.tablePrefix');
        $this->db_user = Config::get('db.user');
        $this->db_pass = Config::get('db.pass');

        if ($this->db_api == 'mysqli') {
            if (extension_loaded('mysqli')) {
                /**
                 * @see https://www.php.net/manual/en/mysqli.construct.php
                 */

                if (empty($this->db_host)) {
                    $this->db_host = ini_get('mysqli.default_host');
                }

                if (empty($this->db_user)) {
                    $this->db_user = ini_get('mysqli.default_user');
                }

                if (empty($this->db_pass)) {
                    $this->db_pass = ini_get('mysqli.default_pw');
                }

                if (empty($this->db_port)) {
                    $this->db_port = ini_get('mysqli.default_port');
                }

                # It's safe to suppress any errors/warnings/notices here because
                # if we can't connect we'll throw an Exception anyway.
                set_error_handler(function () {
                });
                $this->db_connection = new \mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_database, $this->db_port);
                restore_error_handler();

                if ($this->db_connection->connect_errno) {
                    throw new Exception($this->db_connection->connect_error, $this->db_connection->connect_errno);
                }
            } else {
                throw new Exception("Required extension 'mysqli' not loaded");
            }
        } else {
            throw new Exception("No available database API");
        }
    }

    public function __destruct()
    {
        //echo get_class( $this ) . "::__destruct()\n";

        if (! is_null($this->db_connection)) {
            if ($this->db_api == 'mysqli') {
                $this->db_connection->close();
            }

            $this->db_connection = null;
        }
    }

    private function getProperty($property, $merchant)
    {
        if (array_key_exists($property, $this->data)) {
            /**
             * E.g. Given the default value of $this->db_tablePrefix ("afterpay_"):
             *      "Afterpay\SDK\Money" --> "\Money" --> "Money" --> "afterpay_Money"
             *
             * @todo Create a method for converting UpperCamelCase to snake_case
             *       because the mysqli driver may be case insensitive.
             */
            $table_name = preg_replace('/[^a-z0-9_]+/i', '', $this->db_tablePrefix . substr(strrchr($this->data[ $property ][ 'type' ], '\\'), 1));
            $need_to_get_fresh_data = false;
            $properties_to_update = [];
            $return = null;

            if ($merchant instanceof MerchantAccount && $merchant->isSetup()) {
                $merchantId = $merchant->getMerchantId();
            } else {
                $merchant = null;
                $merchantId = Config::get('merchantId');
            }

            if ($this->db_api == 'mysqli') {
                $escaped_table_name = $this->db_connection->real_escape_string($table_name);

                if (in_array($property, [ 'orderMinimum', 'orderMaximum' ])) {
                    try {
                        $select_stmt = $this->db_connection->prepare("
                            SELECT `property`, `amount`, `currency`, `updated_at`
                            FROM `{$escaped_table_name}`
                            WHERE
                                `merchant_id` = ?
                                AND `property` IN ( 'orderMinimum', 'orderMaximum' )
                            LIMIT 2
                        ");

                        if ($select_stmt === false && $this->db_connection->errno == 1146) {
                            throw new Exception("Table '{$this->db_database}.{$escaped_table_name}' doesn't exist", 1146);
                        }

                        $select_stmt->bind_param(
                            "s",
                            $merchantId
                        );
                        $select_stmt->execute();
                        $select_rs = $select_stmt->get_result();
                        $select_stmt->close();

                        if ($select_rs->num_rows > 0) {
                            while ($obj = $select_rs->fetch_object()) {
                                if (time() - strtotime($obj->updated_at) > $this->data[ $obj->property ][ 'lifespan' ]) {
                                    # We've loaded the object from persistent storage, but it's outdated.
                                    # We'll need to call the API, then update the db.

                                    $need_to_get_fresh_data = true;
                                    $properties_to_update[] = $obj->property;
                                } elseif ($obj->property == $property) {
                                    if (is_null($obj->amount) && is_null($obj->currency)) {
                                        $return = null;
                                    } else {
                                        $return = new Money([
                                            'amount' => $obj->amount,
                                            'currency' => $obj->currency
                                        ]);
                                    }
                                }
                            }
                        } else {
                            # The table exists, but the property we're looking for within that table doesn't.
                            # We'll need to call the API, then insert the result into the db.

                            $need_to_get_fresh_data = true;
                        }

                        $select_rs->free();
                    } catch (\Exception $e) {
                        if ($e->getCode() == 1146) {
                            # Table does not exist.
                            # E.g. "Table '{$this->db_database}.{$escaped_table_name}' doesn't exist"

                            /**
                             * Create the missing table.
                             * Note that this will use the default engine, charset and collation for the db.
                             *
                             * @todo Define at least part of this schema in the Model, not here.
                             *       `id`, `merchant_id` and `property` would be applicable in any schema,
                             *       as would `created_at` and `updated_at`, but the properties of
                             *       `amount` and `currency` should be defined in the model.
                             * @todo Is there a library we can use to do this stuff?
                             * @todo Build an upgrade/migration class for users who update the SDK and need
                             *       their existing db models upgraded
                             */
                            $create_table_stmt = $this->db_connection->prepare("
								CREATE TABLE `{$escaped_table_name}` (
									`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
									`merchant_id` varchar(9) DEFAULT NULL,
									`property` varchar(64) DEFAULT NULL,
									`amount` varchar(10) DEFAULT NULL,
									`currency` varchar(3) DEFAULT NULL,
									`created_at` datetime DEFAULT NULL,
									`updated_at` datetime DEFAULT NULL,
									PRIMARY KEY (`id`),
									UNIQUE KEY `merchant_id_property` (`merchant_id`, `property`)
								)
                            ");

                            if ($create_table_stmt === false) {
                                throw new Exception($this->db_connection->error, $this->db_connection->errno);
                            }

                            $create_table_stmt->execute();
                            $create_table_stmt->close();

                            # Now the table is created, but it's empty.
                            # We'll need to call the API, then insert the result into the db.

                            $need_to_get_fresh_data = true;
                        } else {
                            throw new Exception($this->db_connection->error, $this->db_connection->errno);
                        }
                    }
                }
            } else {
                $need_to_get_fresh_data = true;
            }

            if ($need_to_get_fresh_data) {
                if (in_array($property, [ 'orderMinimum', 'orderMaximum' ])) {
                    $getConfigurationRequest = new GetConfigurationRequest($merchant);

                    /**
                     * @todo As these actions are more specific to the endpoint itself,
                     *       rather than the particular persisted property being accessed,
                     *       move this functionality into a method on the response class.
                     *
                     * @todo Refactor and simplify this horrible code.
                     */
                    if ($getConfigurationRequest->send()) {
                        $responseBody = $getConfigurationRequest->getResponse()->getParsedBody();
                        $now = date('Y-m-d H:i:s');

                        $propertyMappings = [
                            'minimumAmount' => 'orderMinimum',
                            'maximumAmount' => 'orderMaximum'
                        ];

                        foreach ($propertyMappings as $responseBodyProperty => $persistentStorageProperty) {
                            if (property_exists($responseBody, $responseBodyProperty)) {
                                # The property we're looking for exists in the response.
                                # For example, the v2 GetConfiguration response has a `maximumAmount` property.

                                if (in_array($persistentStorageProperty, $properties_to_update)) {
                                    if ($this->db_api == 'mysqli') {
                                        # We already have a corresponding property in our PersistentStorage db table
                                        # for this property, so we're going to update it.

                                        $update_stmt = $this->db_connection->prepare("
											UPDATE `{$escaped_table_name}`
											SET
												`amount` = ?,
												`currency` = ?,
												`updated_at` = ?
											WHERE
												`merchant_id` = ?
												AND `property` = ?
											LIMIT 1
                                        ");
                                        $update_stmt->bind_param(
                                            "sssss",
                                            $responseBody->$responseBodyProperty->amount,
                                            $responseBody->$responseBodyProperty->currency,
                                            $now,
                                            $merchantId,
                                            $persistentStorageProperty
                                        );
                                        $update_stmt->execute();
                                        $update_stmt->close();
                                    }
                                } else {
                                    if ($this->db_api == 'mysqli') {
                                        # We've never cached this property before, so we need to run an
                                        # INSERT query.

                                        $insert_stmt = $this->db_connection->prepare("
											INSERT INTO `{$escaped_table_name}`
											(
												`merchant_id`,
												`property`,
												`amount`,
												`currency`,
												`created_at`,
												`updated_at`
											) VALUES (
												?,
												?,
												?,
												?,
												?,
												?
											)
                                        ");
                                        $insert_stmt->bind_param(
                                            "ssssss",
                                            $merchantId,
                                            $persistentStorageProperty,
                                            $responseBody->$responseBodyProperty->amount,
                                            $responseBody->$responseBodyProperty->currency,
                                            $now,
                                            $now
                                        );
                                        $insert_stmt->execute();
                                        $insert_stmt->close();
                                    }
                                }

                                if ($property == $persistentStorageProperty) {
                                    # This was the specific property that was requested, so as well as updating
                                    # this and any other properties that were included in the same response,
                                    # we'll return this value when we're finished here.

                                    $return = new Money([
                                        'amount' => $responseBody->$responseBodyProperty->amount,
                                        'currency' => $responseBody->$responseBodyProperty->currency
                                    ]);
                                }
                            } else {
                                # The property we're looking for does not exist in the response.
                                # For example, the v2 GetConfiguration response does not have a
                                # `minimumAmount` property (AU/NZ only).

                                if (in_array($persistentStorageProperty, $properties_to_update)) {
                                    if ($this->db_api == 'mysqli') {
                                        # We already have a corresponding property in our PersistentStorage db table
                                        # for this property, so we're going to update it.

                                        $update_stmt = $this->db_connection->prepare("
											UPDATE `{$escaped_table_name}`
											SET
												`amount` = NULL,
												`currency` = NULL,
												`updated_at` = ?
											WHERE
												`merchant_id` = ?
												AND `property` = ?
											LIMIT 1
                                        ");
                                        $update_stmt->bind_param(
                                            "sss",
                                            $now,
                                            $merchantId,
                                            $persistentStorageProperty
                                        );
                                        $update_stmt->execute();
                                        $update_stmt->close();
                                    }
                                } else {
                                    if ($this->db_api == 'mysqli') {
                                        # We've never cached this property before, so we need to run an
                                        # INSERT query.

                                        $insert_stmt = $this->db_connection->prepare("
											INSERT INTO `{$escaped_table_name}`
											(
												`merchant_id`,
												`property`,
												`amount`,
												`currency`,
												`created_at`,
												`updated_at`
											) VALUES (
												?,
												?,
												NULL,
												NULL,
												?,
												?
											)
                                        ");
                                        $insert_stmt->bind_param(
                                            "ssss",
                                            $merchantId,
                                            $persistentStorageProperty,
                                            $now,
                                            $now
                                        );
                                        $insert_stmt->execute();
                                        $insert_stmt->close();
                                    }
                                }

                                if ($property == $persistentStorageProperty) {
                                    # This was the specific property that was requested, so as well as updating
                                    # this and any other properties that were included in the same response,
                                    # we'll return this value when we're finished here.

                                    $return = null;
                                }
                            }
                        }
                    } else {
                        /**
                         * @todo Really should be throwing an Exception here...
                         */
                        //echo 'got a ' . $getConfigurationRequest->getResponse()->getHttpStatusCode() . " from the API when trying to get this data!\n";
                    }
                } else {
                    /**
                     * @todo Throw an Exception here - trying to access a property
                     *       that we don't have a handler for.
                     */
                }
            }

            return $return;
        } else {
            /**
             * @todo Throw an Exception here - trying to access a property
             *       that doesn't exist.
             */
        }

        return null;
    }
}
