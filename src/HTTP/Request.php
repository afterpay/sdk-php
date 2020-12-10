<?php

namespace Afterpay\SDK\HTTP;

use Afterpay\SDK\Config;
use Afterpay\SDK\MerchantAccount;
use Afterpay\SDK\HTTP;
use Afterpay\SDK\HTTP\Response;
use Afterpay\SDK\Exception\InvalidArgumentException;
use Afterpay\SDK\Exception\NetworkException;
use Afterpay\SDK\Exception\ParsingException;

class Request extends HTTP
{
    use \Afterpay\SDK\Shared\ModelMethods;

    /**
     * @var \Afterpay\SDK\MerchantAccount $merchant
     */
    private $merchant;

    /**
     * @var resource $ch
     */
    protected $ch;

    /**
     * @var string $apiEnvironmentUrl
     */
    protected $apiEnvironmentUrl;

    /**
     * @var string $uri
     */
    protected $uri;

    /**
     * @var array $headers
     */
    protected $headers;

    /**
     * @var \Afterpay\SDK\HTTP\Response $response
     */
    protected $response;

    /**
     * @var int $curl_errno
     */
    protected $curl_errno;

    /**
     * @var string $curl_error
     */
    protected $curl_error;

    /**
     * @var string $mock_mode
     */
    private $mock_mode;

    /**
     * Class constructor
     */
    public function __construct(...$args)
    {
        parent::__construct();

        if (count($args) == 1 && $args[ 0 ] instanceof MerchantAccount) {
            $this->merchant = $args[ 0 ];

            $merchantId = $this->merchant->getMerchantId();
        } else {
            $merchant = $this->getMerchantAccount();
            $merchantId = $merchant->getMerchantId();

            if (count($args) > 0) {
                $this->passConstructArgsToMagicSetters(... $args);
            }
        }

        $php_version_str = phpversion();
        $curl_version_arr = curl_version();
        $curl_version_str = $curl_version_arr[ 'version' ];
        $composer_json = Config::get('composerJson');
        $ua_extra_a = '';
        $ua_extra_b = '';

        if (! empty($merchantId)) {
            $ua_extra_b .= "; Merchant/{$merchantId}";
        }

        $this->ch = curl_init();
        $this->headers = array();

        # Boolean options
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);

        # Integer options
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 70);

        # String options
        curl_setopt($this->ch, CURLOPT_USERAGENT, "afterpay-sdk-php/{$composer_json->version} ({$ua_extra_a}PHP/{$php_version_str}; cURL/{$curl_version_str}{$ua_extra_b})");
    }

    /**
     * @return \Afterpay\SDK\MerchantAccount
     */
    protected function getMerchantAccount()
    {
        if ($this->merchant instanceof MerchantAccount) {
            # First, look for a MerchantAccount instance as a property of this individual object.
            # This allows multiple Requests to be instantiated simultaneously,
            # each using different credentials.

            return $this->merchant;
        } else {
            # Otherwise, look for credentials as static properties of the parent class.
            # This allows credentials to be set once on the class, then used by
            # many different Requests.

            # If nothing is set on the class yet, as a last resort, try to
            # find credentials in the .env.php configuration file.

            $merchant = new MerchantAccount();

            if (is_null(self::getMerchantId())) {
                self::setMerchantId(Config::get('merchantId'));
            }

            if (is_null(self::getSecretKey())) {
                self::setSecretKey(Config::get('secretKey'));
            }

            if (is_null(self::getCountryCode())) {
                self::setCountryCode(Config::get('countryCode'));
            }

            if (is_null(self::getApiEnvironment())) {
                self::setApiEnvironment(Config::get('apiEnvironment'));
            }

            $merchant
                ->setMerchantId(self::getMerchantId())
                ->setSecretKey(self::getSecretKey())
                ->setCountryCode(self::getCountryCode())
                ->setApiEnvironment(self::getApiEnvironment())
            ;

            return $merchant;
        }

        return $credentials;
    }

    /**
     * @param \Afterpay\SDK\MerchantAccount $merchant
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setMerchantAccount($merchant)
    {
        if (! $merchant instanceof MerchantAccount) {
            $type = gettype($merchant);

            if ($type == 'object') {
                $type = get_class($merchant);
            }

            throw new InvalidArgumentException("Afterpay\SDK\MerchantAccount expected; {$type} given");
        }

        $this->merchant = $merchant;

        $this->setUri($this->uri); # If the Country Code or API Environment has changed we'll need to update the CURLOPT_URL option
        $this->configureBasicAuth(); # If the MerchantAccount credentials have changed we'll need to update the CURLOPT_USERPWD option

        return $this;
    }

    /**
     * @param int $milliseconds
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setConnectionTimeout($milliseconds)
    {
        if (! is_int($milliseconds)) {
            throw new InvalidArgumentException('Integer expected; ' . gettype($milliseconds) . ' given');
        }

        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT_MS, $milliseconds);

        return $this;
    }

    /**
     * @param int $milliseconds
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setTimeout($milliseconds)
    {
        if (! is_int($milliseconds)) {
            throw new InvalidArgumentException('Integer expected; ' . gettype($milliseconds) . ' given');
        }

        curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $milliseconds);

        return $this;
    }

    /**
     * @return string
     */
    public function getApiEnvironmentUrl()
    {
        return $this->apiEnvironmentUrl;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return \Afterpay\SDK\HTTP\Request
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        $merchant = $this->getMerchantAccount();
        $countryCode = $merchant->getCountryCode();
        $apiEnvironment = $merchant->getApiEnvironment();

        # Use the Country Code to determine the geographic region
        # Defaults to Oceania / Asia Pacific (AU/NZ)

        $region_suffix = '';
        $sandbox_suffix = '-sandbox';
        $tld = 'afterpay.com';

        if (strlen($countryCode) == 2) {
            if (preg_match('/CA|US/', $countryCode)) {
                $region_suffix = '.us'; # North America
            } elseif (preg_match('/GB|UK/', $countryCode)) {
                $region_suffix = '.eu'; # Europe
            } elseif (preg_match('/ES|FR|IT|PT/', $countryCode)) {
                $tld = 'clearpay.com'; # Southern Europe
                $sandbox_suffix = '.sandbox';
            }
        }

        if (strtolower($apiEnvironment) == 'production') {
            $this->apiEnvironmentUrl = "https://api{$region_suffix}.{$tld}";
        } else {
            $this->apiEnvironmentUrl = "https://api{$region_suffix}{$sandbox_suffix}.{$tld}";
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->apiEnvironmentUrl . $this->uri);

        return $this;
    }

    /**
     * @param string $method
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setHttpMethod($method = 'GET')
    {
        switch ($method) {
            case 'GET':
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;

            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, true);
                break;

            case 'PUT':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
                break;

            default:
                throw new InvalidArgumentException("Unexpected HTTP Method given: {$method}");
        }

        return $this;
    }

    /**
     * @param mixed $body
     * @return \Afterpay\SDK\HTTP\Request
     */
    public function setRequestBody($body_mixed)
    {
        if (is_string($body_mixed)) {
            $body_string = $body_mixed;
        } elseif (is_array($body_mixed) || is_object($body_mixed)) {
            $body_string = json_encode($body_mixed);
        }

        $this->addHeader('Content-Type', 'application/json');
        $this->addHeader('Content-Length', strlen($body_string));
        $this->setRawBody($body_string);

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body_string);

        return $this;
    }

    /**
     * @return \Afterpay\SDK\HTTP\Request
     */
    public function configureBasicAuth()
    {
        $merchant = $this->getMerchantAccount();
        $merchantId = $merchant->getMerchantId();
        $secretKey = $merchant->getSecretKey();

        if ($merchantId && $secretKey) {
            curl_setopt($this->ch, CURLOPT_USERPWD, "{$merchantId}:{$secretKey}");
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $key
     * @param string $value
     * @return \Afterpay\SDK\HTTP\Request
     */
    public function addHeader($key, $value)
    {
        $this->headers[] = "{$key}: {$value}";

        return $this;
    }

    /**
     * @param array $headers
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setHeaders($headers)
    {
        if (! is_array($headers)) {
            throw new InvalidArgumentException('Array expected; ' . gettype($headers) . ' given');
        }

        $this->headers = $headers;

        return $this;
    }

    /**
     * @return string
     */
    public function getRawLog()
    {
        $str = '';

        $str .= "########## BEGIN RAW HTTP REQUEST  ##########\n";
        $str .= $this->getRaw() . "\n";
        $str .= "########## END RAW HTTP REQUEST    ##########\n";

        if ($this->getResponse()) {
            $str .= "########## BEGIN RAW HTTP RESPONSE ##########\n";
            $str .= $this->getResponse()->getRaw() . "\n";
            $str .= "########## END RAW HTTP RESPONSE   ##########\n";
        }

        return $this->maybeObfuscate($str);
    }

    /**
     * @return \Afterpay\SDK\HTTP\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function getCurlErrno()
    {
        return $this->curl_errno;
    }

    /**
     * @param int $curl_errno
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setCurlErrno($curl_errno)
    {
        if (! is_int($curl_errno)) {
            throw new InvalidArgumentException('Integer expected; ' . gettype($curl_errno) . ' given');
        }

        $this->curl_errno = $curl_errno;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurlError()
    {
        return $this->curl_error;
    }

    /**
     * @param string $curl_errno
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setCurlError($curl_error)
    {
        if (! is_string($curl_error)) {
            throw new InvalidArgumentException('String expected; ' . gettype($curl_error) . ' given');
        }

        $this->curl_error = $curl_error;

        return $this;
    }

    /**
     * @return string
     */
    public function getMockMode()
    {
        return $this->mock_mode;
    }

    /**
     * @param string $mock_mode
     * @return \Afterpay\SDK\HTTP\Request
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     */
    public function setMockMode($mock_mode)
    {
        if (! is_string($mock_mode)) {
            throw new InvalidArgumentException('String expected; ' . gettype($mock_mode) . ' given');
        }

        if (
            ! in_array($mock_mode, [
                'alwaysReceiveServiceUnavailable',
                'alwaysThrowNetworkException',
                'alwaysThrowParsingException'
            ])
        ) {
            throw new InvalidArgumentException("Invalid mock mode: '{$mock_mode}'");
        }

        $this->mock_mode = $mock_mode;

        return $this;
    }

    /**
     * @return bool
     * @throws \Afterpay\SDK\Exception\InvalidArgumentException
     * @throws \Afterpay\SDK\Exception\NetworkException
     * @throws \Afterpay\SDK\Exception\ParsingException
     */
    public function send()
    {
        if (method_exists($this, 'beforeSend')) {
            $this->beforeSend();
        }

        if ($this->getMockMode() == 'alwaysThrowNetworkException') {
            throw new NetworkException('Connection timed out after 0 milliseconds (mock)', 7);
        }

        $preferred_response_class = str_replace('Afterpay\SDK\HTTP\Request', 'Afterpay\SDK\HTTP\Response', get_class($this));
        if (class_exists($preferred_response_class)) {
            $this->response = new $preferred_response_class();
        } else {
            $this->response = new Response();
        }
        $this->response->setRequest($this);

        if (method_exists($this, 'jsonSerialize')) {
            $model_data = $this->jsonSerialize();

            if (is_null($this->getRawBody()) && ! empty($model_data)) {
                $this->setRequestBody($model_data);
            }
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getHeaders());

        if ($this->getMockMode() == 'alwaysReceiveServiceUnavailable') {
            $this->response
                ->setHttpStatusCode(503)
                ->setContentType('application/json')
                ->setRawBody(
                    '{
                      "errorCode" : "service_unavailable_mock",
                      "errorId" : "0123456789abcdef",
                      "message" : "Service Unavailable (Mock)",
                      "httpStatusCode" : 503
                    }'
                )
            ;

            return false;
        } elseif ($this->getMockMode() == 'alwaysThrowParsingException') {
            $this->response
                ->setHttpStatusCode(200)
                ->setContentType('text/plain;charset=iso-8859-1')
            ;

            throw new ParsingException('Syntax error (mock)', 4);
        }

        $rs = curl_exec($this->ch);

        $this
            ->setRawHeaders(curl_getinfo($this->ch, CURLINFO_HEADER_OUT))
            ->setCurlErrno(curl_errno($this->ch))
            ->setCurlError(curl_error($this->ch))
        ;

        $this->response
            ->setHttpStatusCode(curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE))
            ->setContentType(curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE))
        ;

        curl_close($this->ch);

        if ($rs === false) {
            // 7 and 28 are common timeout errno's
            throw new NetworkException($this->curl_error, $this->curl_errno);
        }

        $rs = str_replace("\r\n", "\n", $rs); # Warning: this manipulates the raw response data!

        $response_parts = explode("\n\n", $rs);

        $response_headers = [];

        if (stripos($rs, 'HTTP/1.1 100') === 0 || stripos($rs, 'HTTP/2 100') === 0) {
            $response_headers[] = array_shift($response_parts);
        }

        $response_headers[] = array_shift($response_parts);

        $this->response
            ->setRawHeaders(implode("\n\n", $response_headers) . "\n\n")
            ->setRawBody(implode("\n\n", $response_parts))
        ;

        if (method_exists($this->response, 'afterReceive')) {
            $this->response->afterReceive($this->getMerchantAccount());
        }

        return $this->response->isSuccessful();
    }
}
