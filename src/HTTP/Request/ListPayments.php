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

namespace Afterpay\SDK\HTTP\Request;

use Afterpay\SDK\Exception\InvalidArgumentException;
use Afterpay\SDK\Exception\PrerequisiteNotMetException;
use Afterpay\SDK\HTTP\Request;

/**
 * @todo Make a flexible array for all query params similar to body data.
 */
class ListPayments extends Request
{
    /**
     * @var string  $toCreatedDate      An inclusive end date and time to search, in ISO 8601 format.
     * @var string  $fromCreatedDate    An inclusive start date and time to search, in ISO 8601 format.
     * @var integer $limit              The number of results to retrieve. Must be between 1 and 250 (inclusive).
     * @var integer $offset             The position to start the results at. The first result has a position of 0.
     * @var array   $tokens             One or more order tokens to search for.
     * @var array   $ids                One or more Afterpay Order IDs to search for.
     * @var array   $merchantReferences One or more Merchant Reference IDs to search for.
     * @var array   $statuses           One or more Afterpay Order Statuses to search for.
     *                                  Possible values include "APPROVED" and "DECLINED".
     * @var string  $orderBy            A field to order results by. If provided, must be one of "createdAt", "id",
     *                                  "totalAmount", "merchantReference" or "email".
     * @var boolean $ascending          `true` to order results in ascending order, or `false` for descending order.
     */
    protected $toCreatedDate;
    protected $fromCreatedDate;
    protected $limit;
    protected $offset;
    protected $tokens;
    protected $ids;
    protected $merchantReferences;
    protected $statuses;
    protected $orderBy;
    protected $ascending;

    /**
     * The URI querystring is constructed here, immediately before the Request is sent.
     *
     * @throws \Afterpay\SDK\Exception\PrerequisiteNotMetException
     */
    protected function beforeSend()
    {
        $queryParams = [];

        if (! is_null($this->toCreatedDate)) {
            $str = urlencode($this->toCreatedDate);
            $queryParams[] = "toCreatedDate={$str}";
        }
        if (! is_null($this->fromCreatedDate)) {
            $str = urlencode($this->fromCreatedDate);
            $queryParams[] = "fromCreatedDate={$str}";
        }
        if (! is_null($this->limit)) {
            $queryParams[] = "limit={$this->limit}";
        }
        if (! is_null($this->offset)) {
            $queryParams[] = "offset={$this->offset}";
        }
        if (! is_null($this->tokens)) {
            foreach ($this->tokens as $token) {
                $str = urlencode($token);
                $queryParams[] = "tokens={$str}";
            }
        }
        if (! is_null($this->ids)) {
            foreach ($this->ids as $id) {
                $str = urlencode($id);
                $queryParams[] = "ids={$str}";
            }
        }
        if (! is_null($this->merchantReferences)) {
            foreach ($this->merchantReferences as $merchantReference) {
                $str = urlencode($merchantReference);
                $queryParams[] = "merchantReferences={$str}";
            }
        }
        if (! is_null($this->statuses)) {
            foreach ($this->statuses as $status) {
                $queryParams[] = "statuses={$status}";
            }
        }
        if (! is_null($this->orderBy)) {
            $queryParams[] = "orderBy={$this->orderBy}";
        }
        if (! is_null($this->ascending)) {
            $ascendingStr = $this->ascending ? 'true' : 'false';
            $queryParams[] = "ascending={$ascendingStr}";
        }

        if (empty($queryParams)) {
            throw new PrerequisiteNotMetException('Cannot send a ListPayments Request without any search parameters');
        }

        $querystring = implode('&', $queryParams);

        $this->setUri("/v2/payments?{$querystring}");
    }

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this
            ->configureBasicAuth()
        ;
    }

    /**
     * @param string $toCreatedDate
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setToCreatedDate($toCreatedDate)
    {
        if (! is_string($toCreatedDate)) {
            throw new InvalidArgumentException('Expected string for toCreatedDate; ' . gettype($toCreatedDate) . ' given');
        } elseif (strlen($toCreatedDate) < 1) {
            throw new InvalidArgumentException('Expected non-empty string for toCreatedDate; empty string given');
        }

        $this->toCreatedDate = $toCreatedDate;

        return $this;
    }

    /**
     * @param string $fromCreatedDate
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setFromCreatedDate($fromCreatedDate)
    {
        if (! is_string($fromCreatedDate)) {
            throw new InvalidArgumentException('Expected string for fromCreatedDate; ' . gettype($fromCreatedDate) . ' given');
        } elseif (strlen($fromCreatedDate) < 1) {
            throw new InvalidArgumentException('Expected non-empty string for fromCreatedDate; empty string given');
        }

        $this->fromCreatedDate = $fromCreatedDate;

        return $this;
    }

    /**
     * @param integer $limit
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setLimit($limit)
    {
        if (! is_int($limit)) {
            throw new InvalidArgumentException('Expected integer for limit; ' . gettype($limit) . ' given');
        } elseif ($limit < 1 || $limit > 250) {
            throw new InvalidArgumentException("Expected between 1 and 250 (inclusive) for limit; {$limit} given");
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * @param integer $offset
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setOffset($offset)
    {
        if (! is_int($offset)) {
            throw new InvalidArgumentException('Expected integer for offset; ' . gettype($offset) . ' given');
        } elseif ($offset < 0) {
            throw new InvalidArgumentException("Expected zero or a positive integer for offset; {$offset} given");
        }

        $this->offset = $offset;

        return $this;
    }

    /**
     * @param array $tokens
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setTokens($tokens)
    {
        if (! is_array($tokens)) {
            throw new InvalidArgumentException('Expected array for tokens; ' . gettype($tokens) . ' given');
        } elseif (array_sum(array_map('is_string', $tokens)) != count($tokens)) {
            throw new InvalidArgumentException("Expected an array of string elements; one or more non-string elements given");
        }

        $this->tokens = $tokens;

        return $this;
    }

    /**
     * @param array $ids
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setIds($ids)
    {
        if (! is_array($ids)) {
            throw new InvalidArgumentException('Expected array for ids; ' . gettype($ids) . ' given');
        } elseif (array_sum(array_map('is_string', $ids)) != count($ids)) {
            throw new InvalidArgumentException("Expected an array of string elements; one or more non-string elements given");
        }

        $this->ids = $ids;

        return $this;
    }

    /**
     * @param array $merchantReferences
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setMerchantReferences($merchantReferences)
    {
        if (! is_array($merchantReferences)) {
            throw new InvalidArgumentException('Expected array for merchantReferences; ' . gettype($merchantReferences) . ' given');
        } elseif (array_sum(array_map('is_string', $merchantReferences)) != count($merchantReferences)) {
            throw new InvalidArgumentException("Expected an array of string elements; one or more non-string elements given");
        }

        $this->merchantReferences = $merchantReferences;

        return $this;
    }

    /**
     * @param array $statuses
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setStatuses($statuses)
    {
        if (! is_array($statuses)) {
            throw new InvalidArgumentException('Expected array for statuses; ' . gettype($statuses) . ' given');
        } elseif (array_sum(array_map('is_string', $statuses)) != count($statuses)) {
            throw new InvalidArgumentException('Expected an array of string elements for statuses; one or more non-string elements given');
        } elseif (count(array_udiff($statuses, ['APPROVED', 'DECLINED'], 'strcasecmp')) > 0) {
            throw new InvalidArgumentException('Expected all statuses to be one of "APPROVED", "DECLINED"; one or more unexpected strings given');
        }

        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @param string $orderBy
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setOrderBy($orderBy)
    {
        if (! is_string($orderBy)) {
            throw new InvalidArgumentException('Expected string for orderBy; ' . gettype($orderBy) . ' given');
        } elseif (! in_array($orderBy, ['createdAt', 'id', 'totalAmount', 'merchantReference', 'email'])) {
            throw new InvalidArgumentException('Expected one of "createdAt", "id", "totalAmount", "merchantReference", "email" for orderBy; "' . $orderBy . '" given');
        }

        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @param boolean $ascending
     * @return \Afterpay\SDK\HTTP\Request\ListPayments
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setAscending($ascending)
    {
        if (! is_bool($ascending)) {
            throw new InvalidArgumentException('Expected boolean for ascending; ' . gettype($ascending) . ' given');
        }

        $this->ascending = $ascending;

        return $this;
    }
}
