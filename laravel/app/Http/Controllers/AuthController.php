<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


/**
 *@OA\Info(
 *    title="API TSG",
 *       version="1.0.0",
 *       description="Manejo de usuarios y posts con autenticacionJWT")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar un nuevo usuario",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Gaston Garcia"),
     *             @OA\Property(property="email", type="string", format="email", example="gaston@test.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario registrado exitosamente"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);



        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }



    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Iniciar sesión y recibir el token JWT",
     *      tags={"Autenticación"},
     *   @OA\RequestBody(
     *        required=true,
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="123456"),
     *     )
     *    ),
     *   @OA\Response(response=200, description="Inicio de sesión correcto"),
     *  @OA\Response(response=401, description="Sin autorización"),
     *  )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Sin autorización'], 401);
        }

        return response()->json(compact('token'));
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Obtener el usuario logueado",
     *     tags={"Autenticación"},
     *     security={{"Bearer":{}}},
     *          @OA\Response(
     *         response=200,
     *         description="Datos del usuario logueado",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="name", type="string", example="Juan Perez"),
     *             @OA\Property(property="email", type="string", example="juan@example.com")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token inválido")
     * )
     */
    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    /**
     * @OA\Post(
     *    path="/api/logout",
     *   summary="Cerrar sesión",
     *  tags={"Autenticación"},
     * security={{"Bearer":{}}},
     * @OA\Response(
     *     response=200,
     *     description="Sesión cerrada con éxito",
     *     @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Sesión cerrada con éxito")
     *     )
     * ),
     *  @OA\Response(response=401, description="Token inválido")
     * )
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Sesión cerrada con éxito']);
    }




    //
}
