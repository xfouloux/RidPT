<?php

namespace Rid\Http\Message;

use Rid\Base;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

/**
 * Request组件
 */
class Request extends HttpFoundationRequest implements Base\StaticInstanceInterface, Base\ComponentInterface
{
    use Base\StaticInstanceTrait, Base\ComponentTrait;

    protected \Swoole\Http\Request $_swoole_request;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($config = [])
    {
        $this->setTrustedField($config);
    }

    private function setTrustedField($config = [])
    {
        if (\array_key_exists('trustedHosts', $config)) {
            self::setTrustedHosts($config['trustedHosts']);
        }
        if (\array_key_exists('trustedProxies', $config)) {
            self::setTrustedProxies($config['trustedProxies'], $config['trustedHeaderSet'] ?? Request::HEADER_X_FORWARDED_ALL);
        }
    }

    // 设置请求对象
    public function setRequester(\Swoole\Http\Request $request)
    {
        $this->_swoole_request = $request;

        $server = \array_change_key_case($request->server, CASE_UPPER);

        // Add formatted headers to server
        foreach ($request->header as $key => $value) {
            $server['HTTP_' . \mb_strtoupper(\str_replace('-', '_', $key))] = $value;
        }

        $this->initialize(
            $request->get ?? [],
            $request->post ?? [],
            [],
            $request->cookie ?? [],
            $request->files ?? [],
            $server,
            $request->rawContent()
        );
    }

    public function getSwooleRequest(): \Swoole\Http\Request
    {
        return $this->_swoole_request;
    }
}
