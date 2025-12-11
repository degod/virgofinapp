<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="VirgoFin App Docs",
 *     description="API documentation for the Virgo Financial Application",
 *     @OA\Contact(
 *         email="support@virgofinapp.test",
 *         name="VirgoFin App Support"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:9030",
 *     description="Version 1 of the VirgoFin App API"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use Laravel Sanctum for API authentication"
 * )
 */
abstract class Controller
{
    //
}
