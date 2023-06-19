<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{

    // Función que utilizaremos para registrar al usuario
    public function register(Request $request)
    {
        // Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('dni', 'password', 'nombre', 'apellidos', 'telefono', 'email', 'docente_id');

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'dni' => 'required|string|min:9|max:10|unique:users',
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
        $user = User::create([
            'dni' => $request->dni,
            'password' => bcrypt($request->password),
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'docente_id' => (isset($request->docente_id)) ? $request->docente_id : null
        ]);

        // Devolvemos la respuesta con los datos del usuario
        return response()->json([
            'exito' => true,
            'mensaje' => 'Usuario creado',
            'usuario' => $user
        ], Response::HTTP_OK);
    }


    public function update(Request $request)
    {
        // Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('password', 'nombre', 'apellidos', 'telefono', 'email', 'docente_id');

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'nombre' => 'required|string|min:2|max:30',
            'apellidos' => 'required|string|min:2|max:60',
            'email' => 'required|email',
            'telefono' => 'string|min:9|max:20',
        ]);

        // Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No se encontró usuario.',
            ], 400);
        }

        // Devolvemos la respuesta con los datos del usuario

        $user->password = (isset($request->password)) ? bcrypt($request->password) : $user->password;
        $user->nombre = $request->nombre;
        $user->apellidos = $request->apellidos;
        $user->telefono = $request->telefono;
        if ($user->email != $request->email) $user->email = $request->email;
        $user->save();
    }


    /**
     * Show the form for editing the specified resource.
     *
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user) {
            $user = User::with('alumno')->find($user->id);

            if ( !isset($user->alumno->tutor_status) || $user->alumno->tutor_status == 'rechaza') {
                if ( isset($user->alumno)) $user->alumno->delete();

                $user->delete();

                return response()->json([
                    'exito' => true,
                    'mensaje' => 'Usuario eliminado con exito'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'No se puede eliminar un usuario con candidaturas'
                ], Response::HTTP_OK);
            }
        }

    }

    // Funcion que utilizaremos para hacer login
    public function authenticate(Request $request)
    {
        // Indicamos que solo queremos recibir email y password de la request
        $credentials = $request->only('email', 'password');

        // Validaciones
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        // Devolvemos un error de validación en caso de fallo en las verificaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        // Intentamos hacer login
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                // Credenciales incorrectas.
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Login falló: credenciales incorrectas',
                ], 401);
            }
        } catch (JWTException $e) {
            // Error al intentar crear el token
            return response()->json([
                'exito' => false,
                'mensaje' => 'No se ha podido crear el token',
            ], 500);
        }
        // Devolvemos el token
        return response()->json([
            'exito' => true,
            'token' => $token
        ]);
    }

    // Función que utilizaremos para eliminar el token y desconectar al usuario
    public function logout(Request $request)
    {
        try {
            // Si el token es válido eliminamos el token desconectando al usuario.
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'exito' => true,
                'mensaje' => 'Usuario desconectado'
            ]);
        } catch (JWTException $exception) {
            // Error al intentar invalidar el token
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error al intentar desconectar al usuario'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Función que utilizaremos para obtener los datos del usuario.
    public function getUser(Request $request)
    {
        // Miramos si el usuario se puede autenticar con el token
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Token invalido / token expirado',
            ], 401);
        }
        
        $user = User::with('alumno')->find($user->id);

        return response()->json([
            'exito' => true,
            'usuario' => $user
        ]);
    }

}