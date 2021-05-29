<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Phper666\JWTAuth\Middleware\JWTAuthMiddleware as JWTAuthMiddlewareBase;
use Phper666\JWTAuth\Exception\TokenValidException;
use Phper666\JWTAuth\Util\JWTUtil;

use Hyperf\Utils\Context;

trait TraitJWTAuthMiddleware
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isValidToken = false;
        // 根据具体业务判断逻辑走向，这里假设用户携带的token有效
        $token = $request->getHeaderLine('Authorization') ?? '';
        if (strlen($token) > 0) {
            $token = JWTUtil::handleToken($token);
            if ($token !== false && $this->jwt->checkToken($token)) {
                $isValidToken = true;
            }
        }
        if ($isValidToken) {
            $result = $this->jwt->getParserData();
            $request = $request->withAttribute('jwtResult', $result);
            Context::set(ServerRequestInterface::class, $request);

            return $handler->handle($request);
        }

        throw new TokenValidException('Token authentication does not pass', 401);
    }
}
