<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Posts",
 *     description="Operaciones relacionadas con los posts"
 * )
 */
class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Obtener todos los posts",
     *     tags={"Posts"},
     *     security={{"Bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de posts obtenida exitosamente",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Post"))
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index()
    {
        $posts = Post::with('user')->get();
        return response()->json($posts, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Crear un nuevo post",
     *     tags={"Posts"},
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Mi primer post"),
     *             @OA\Property(property="content", type="string", example="Contenido del post"),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Post creado exitosamente"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => $user->id,
        ]);

        return response()->json($post, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     summary="Obtener un post por ID",
     *     tags={"Posts"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del post",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=404, description="El post no existe"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function show($id)
    {
        $post = Post::with('user')->find($id);
        if (!$post) {
            return response()->json(['error' => 'El post no existe'], 404);
        }
        return response()->json($post, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     summary="Actualizar un post",
     *     tags={"Posts"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Nuevo título del post"),
     *             @OA\Property(property="content", type="string", example="Nuevo contenido del post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=403, description="No tenes permiso para modificar este post"),
     *     @OA\Response(response=404, description="Post no encontrado"),
     *     @OA\Response(response=401, description="Token inválido o no proporcionado")
     * )
     */

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['error' => 'El post no existe'], 404);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if ($post->user_id != $user->id) {
            return response()->json(['error' => 'No tenes permiso para modificar este post'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:100',
            'content' => 'sometimes|required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($request->has('title')){
            $post->title = $request->title;
        }

        if($request->has('content')){
            $post->content = $request->content;
        }

        $post->save();
        return response()->json($post, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Eliminar un post",
     *     tags={"Posts"},
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Post eliminado exitosamente"),
     *     @OA\Response(response=401, description="No tenes permiso para modificar este post"),
     *     @OA\Response(response=404, description="El post no existe")
     * )
     */



    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['error' => 'El post no existe'], 404);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if ($post->user_id != $user->id) {
            return response()->json(['error' => 'No tenes permiso para modificar este post'], 401);
        }

        $post->delete();
        return response()->json(['message' => 'Post eliminado exitosamente'], 200);
    }
}
