<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Middleware;

use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoolecan\Foundation\RpcClient\PassportRpcClient;
use Swoolecan\Foundation\Exceptions\BusinessException;

trait TraitJWTUserMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwtResult = $request->getAttribute('jwtResult');
        $passportBase = make(PassportRpcClient::class);
        if (empty($jwtResult) || !isset($jwtResult['user_id']) || empty($jwtResult['user_id'])) {
            throw new BusinessException(400, 'Token有误');
        }
        $user = $passportBase->getUserById($jwtResult['user_id']);
        if (!isset($user['code']) || $user['code'] != 200) {
            $message = isset($user['message']) ? $user['message'] : 'Token未验证通过';
            $code = isset($user['code']) ? $user['code'] : 401;
            throw new BusinessException($code, $message);
        }
        $request = $request->withAttribute('user', $user['data']);
        Context::set(ServerRequestInterface::class, $request);
        return $handler->handle($request);
    }

}
