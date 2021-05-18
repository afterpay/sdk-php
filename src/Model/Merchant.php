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

namespace Afterpay\SDK\Model;

use Afterpay\SDK\Model;

final class Merchant extends Model
{
    /**
     * @var array $data
     */
    protected $data = [
        'redirectConfirmUrl' => [
            'type' => 'string'
        ],
        'redirectCancelUrl' => [
            'type' => 'string'
        ],
        'popupOriginUrl' => [
            'type' => 'string'
        ]
    ];

    protected function afterSet($propertyName)
    {
        $redirectUrlsRequiredError = 'redirectConfirmUrl and redirectCancelUrl are required if popupOriginUrl is not provided';
        $popupUrlRequiredError = 'popupOriginUrl is required if redirectConfirmUrl and redirectCancelUrl are not provided';

        $redirectConfirmUrl = $this->getRedirectConfirmUrl();
        $redirectCancelUrl = $this->getRedirectCancelUrl();
        $popupOriginUrl = $this->getPopupOriginUrl();

        if (empty($redirectConfirmUrl) && empty($redirectCancelUrl) && empty($popupOriginUrl)) {
            $this->addError($redirectUrlsRequiredError, 'redirectConfirmUrl');
            $this->addError($redirectUrlsRequiredError, 'redirectCancelUrl');
            $this->addError($popupUrlRequiredError, 'popupOriginUrl');
        } else {
            $this->clearError($redirectUrlsRequiredError, 'redirectConfirmUrl');
            $this->clearError($redirectUrlsRequiredError, 'redirectCancelUrl');
            $this->clearError($popupUrlRequiredError, 'popupOriginUrl');
            if (! empty($redirectConfirmUrl) && empty($redirectCancelUrl)) {
                $this->addError($redirectUrlsRequiredError, 'redirectCancelUrl');
            }
            if (! empty($redirectCancelUrl) && empty($redirectConfirmUrl)) {
                $this->addError($redirectUrlsRequiredError, 'redirectConfirmUrl');
            }
        }
    }

    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this->afterSet(null);
    }
}
