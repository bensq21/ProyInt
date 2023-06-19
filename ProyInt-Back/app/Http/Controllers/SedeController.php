<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;
use App\Models\Tutor;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class SedeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Listado de todas las sedes
        $sedes = Sede::with('tutores')->get();

        foreach ($sedes as $key_sede => $sede) {
            foreach ($sede->tutores as $key_tutor => $tutor) {
                $tutor_Alumnos = Tutor::with('alumnos')->find($tutor->id);
                $alumnos = count($tutor_Alumnos->alumnos->filter(function ($alumno) {
                    return $alumno->tutor_status === 'acepta' || $alumno->tutor_status === 'espera';
                }));
                $sedes[$key_sede]->tutores[$key_tutor]->alumnos = $alumnos;
            }
        }

        // Devolvemos la respuesta con los datos del usuario
        return response()->json([
            'exito' => true,
            'sedes' => $sedes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('empresa', 'contacto', 'telefono', 'email', );

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'empresa' => 'required|string|min:2|max:60|unique:sedes',
            'contacto' => 'required|string|min:2|max:30',
            'telefono' => 'required|string|min:9|max:20',
            'email' => 'required|email|unique:sedes|unique:users',
        ]);

        // Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        // Creamos el nuevo usuario si todo es correcto
        $sede = Sede::create([
            'empresa' => $request->empresa,
            'contacto' => $request->contacto,
            'telefono' => $request->telefono,
            'email' => $request->email,
        ]);

        // Devolvemos la respuesta con los datos de la sede
        return response()->json([
            'exito' => true,
            'mensaje' => 'Sede creada.',
            'sede' => $sede
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->only('empresa', 'contacto', 'telefono', 'email', );

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'empresa' => 'required|string|min:9|max:10',
            'contacto' => 'required|string|min:2|max:30',
            'telefono' => 'required|string|min:2|max:60',
            'email' => 'required|email|unique:users',
        ]);

        // Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $sede = Sede::find($id);

        if (!$sede) {
            $sede->empresa = $request->empresa; // $request->nombre
            $sede->contacto = $request->contacto; //
            $sede->telefono = $request->telefono;
            $sede->email = $request->email;
            $sede->save();
            return response()->json([
                'exito' => true,
                'mensaje' => 'Sede actualizada',
                'usuario' => $sede
            ], Response::HTTP_OK);
        }

        return response()->json([
            'exito' => false,
            'mensaje' => 'No se encontro la sede',
            'usuario' => $sede
        ], Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Obtenemos la información de la sede a borrar (Error 404 en caso de no encontrar la misma)
        $sede = Sede::findOrFail($id);
        // Confirmamos que no haya departamentos

        // Verificar si los tutores tienen alumnos asociados
        foreach ($sede->tutores as $tutor) {
            if ($tutor->alumnos()->count() > 0) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'No se puede eliminar la sede. Hay tutores con alumnos asociados.'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
        
        // Eliminar los tutores asociados
        $sede->tutores()->delete();

        // Eliminar la sede si no hay tutores con alumnos asociados
        $sede->delete();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Sede eliminada correctamente.'
        ], Response::HTTP_OK);
    }
}