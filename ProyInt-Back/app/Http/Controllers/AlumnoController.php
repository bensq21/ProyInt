<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Alumno;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AlumnoController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $candidatura = Alumno::where('user_id', $request->user_id)->count();

        if ($candidatura) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Este alumno ya tiene una candidatura.'
            ], Response::HTTP_OK);
        } else {
            Alumno::create([
                'user_id' => $request->user_id,
                'tutor_id' => $request->tutor_id,
                'tutor_status' => $request->tutor_status,
                'curriculum' => $request->curriculum,
            ]);

            // Devolvemos la respuesta con los datos del usuario
            return response()->json([
                'exito' => true,
                'mensaje' => 'Datos de alumno creados'
            ], Response::HTTP_OK);
        }

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

        $alumno = Alumno::find($id);

        if ($alumno) {
            $alumno->tutor_id = $request->tutor_id; //
            $alumno->tutor_status = $request->tutor_status;
            $alumno->save();
            return response()->json([
                'exito' => true,
                'mensaje' => 'Candidatura actualizada.',
                'usuario' => $alumno
            ], Response::HTTP_OK);
        }

        return response()->json([
            'exito' => false,
            'mensaje' => 'No se encontro la candidatura.',
            'usuario' => $alumno
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
        $alumno = Alumno::findOrFail($id);

        if ($alumno) {
            $alumno->delete();
        }

        return response()->json([
            'exito' => true,
            'mensaje' => 'Candidatura eliminado con exito'
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function alumnos()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $alumnos = User::with('alumno')->where('docente_id', $user->id)->get();

        return response()->json([
            'exito' => true,
            'alumnos' => $alumnos
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function candidaturas()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $alumnos = User::where('docente_id', $user->id)
            ->has('alumno')
            ->with([
                    'alumno' => function ($query) {
                        $query->whereNotNull('tutor_status')->with('tutor');
                    }
                ])
            ->get();

        $alumnos = $alumnos->filter(function ($alumno) {
            return $alumno->alumno !== null;
        })->values()->toArray();

        return response()->json([
            'exito' => true,
            'alumnos' => $alumnos
        ], Response::HTTP_OK);

    }
}