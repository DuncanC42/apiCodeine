<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="CPAM API Documentation",
 *     version="1.0.0",
 *     description="API documentation for CPAM application"
 * )
 * @OA\Server(
 *     description="API Server",
 *     url="/"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class OpenApiSpec {}
