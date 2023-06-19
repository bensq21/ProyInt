<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use App\Models\Tutor;

class TutorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Listado de todas las sedes
        $tutores = Tutor::all()->values()->toArray();
        return response()->json($tutores);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('dni', 'nombre', 'apellidos', 'telefono', 'email', 'sede_id');

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'dni' => 'required|string|min:9|max:10',
            'nombre' => 'required|string|min:2|max:30',
            'apellidos' => 'required|string|min:2|max:60',
            'email' => 'required|email|unique:users',
            'telefono' => 'string|min:9|max:20',
        ]);

        // Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        // Creamos el nuevo usuario si todo es correcto
        $tutor = Tutor::create([
            'dni' => $request->dni,
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'telefono' => $request->telefono,
        ]);

        // Devolvemos la respuesta con los datos del usuario
        return response()->json([
            'exito' => true,
            'mensaje' => 'Usuario creado',
            'usuario' => $tutor
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('nombre', 'apellidos', 'telefono', 'email');

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'nombre' => 'required|string|min:2|max:30',
            'apellidos' => 'required|string|min:2|max:60',
            'email' => 'required|email|unique:users',
            'telefono' => 'string|min:9|max:20',
        ]);

        // Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $tutor = Tutor::find($id);

        if ($tutor) {
            $tutor->nombre = $request->nombre; 
            $tutor->apellidos = $request->apellidos; 
            $tutor->telefono = $request->telefono;
            $tutor->email = $request->email;
            $tutor->save();
            return response()->json([
                'exito' => true,
                'mensaje' => 'Tutor actualizado',
                'usuario' => $tutor
            ], Response::HTTP_OK);
        }

        // Devolvemos la respuesta con los datos del usuario
        return response()->json([
            'exito' => false,
            'mensaje' => 'Usuario no actualizado',
            'usuario' => $tutor
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Obtenemos la información de la sede a borrar (Error 404 en caso de no encontrar la misma)
        $tutor = Tutor::with('alumnos')->findOrFail($id);
        if (count($tutor->alumnos) == 0) {
            $tutor->delete();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Tutor eliminado',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'exito' => false,
            'mensaje' => 'No se puede eliminar un tutor con candidaturas',
        ], Response::HTTP_OK);
    }
}