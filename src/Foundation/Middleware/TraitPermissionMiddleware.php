<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Middleware;

trait TraitPermissionMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $passportBase = make(PassportRpcClient::class);
        $dispatcher = $request->getAttribute('Hyperf\HttpServer\Router\Dispatched');
        $routeCode = $dispatcher->handler->options['routeCode'];
        $permission = $passportBase->checkPermission($userId, $routeCode);
        if (!isset($permission['code']) || $permission['code'] != 200) {
            $message = isset($permission['message']) ? $permission['message'] : '无权进行该操作';
            $code = isset($permission['code']) ? $permission['code'] : 403;
            throw new BusinessException($code, $message);
        }
        return $handler->handle($request);
    }

}
