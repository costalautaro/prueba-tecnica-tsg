<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * @OA\Tag(
 *     name="Usuarios",
 *     description="Operaciones relacionadas con usuarios"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Obtener todos los usuarios",
     *     tags={"Usuarios"},
     *     security={{"Bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios obtenida exitosamente",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index() {
        $users = User::all();
        return response()->json($users, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Obtener un usuario por ID",
     *     tags={"Usuarios"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del usuario",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="El usuario no existe"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function show($id) {

        $user = User::find($id);
        if(!$user) {
            return response()->json(['message' => 'El usuario no existe'], 404);
        }
        return response()->json($user, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Actualizar un usuario",
     *     tags={"Usuarios"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Nuevo Nombre"),
     *             @OA\Property(property="email", type="string", format="email", example="nuevocorreo@test.com"),
     *             @OA\Property(property="password", type="string", format="password", example="nuevapassword"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="nuevapassword")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario actualizado"),
     *     @OA\Response(response=401, description="No tenes permiso para modificar este usuario"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function update(Request $request, $id) {

        $user = User::find($id);
        if(!$user){
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }


        $authUser = JWTAuth::parseToken()->authenticate();

        if($authUser->id != $user->id){
            return response()->json(['error' => 'No tenes permiso para modificar este usuario'], 403);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|email|unique:users,email,'.$id,
            'password' => 'sometimes|required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->fill($request->only(['name', 'email']));

        if($request->has('password')){
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return response()->json($user, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Eliminar un usuario",
     *     tags={"Usuarios"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Usuario eliminado"),
     *     @OA\Response(response=401, description="No tenes permiso para eliminar este usuario"),
     *     @OA\Response(response=404, description="El usuario no existe")
     * )
     */
    public function destroy($id) {

        $user = User::find($id);
        if(!$user){
            return response()->json(['error' => 'El usuario no existe'], 404);
        }

        $authUser = JWTAuth::parseToken()->authenticate();

        if($authUser->id != $user->id){
            return response()->json(['error' => 'No tenes permiso para eliminar este usuario'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado'], 200);
    }
}
