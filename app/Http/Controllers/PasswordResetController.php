<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Mail\SendResetCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function sendCode(Request $request)
    {
        // 1. Cambiamos 'email' por 'correo' para que coincida con tu JSON
        $request->validate([
            'correo' => 'required|email|exists:usuarios,correo'
        ]);

        // 2. Limpiamos códigos anteriores
        PasswordResetCode::where('correo', $request->correo)->delete();

        $code = rand(100000, 999999);

        // 3. Guardamos (usamos $request->correo)
        PasswordResetCode::create([
            'correo' => $request->correo,
            'code' => $code,
            'created_at' => now(),
        ]);

        try {
            // Enviamos el mail al valor de 'correo'
            Mail::to($request->correo)->send(new SendResetCode($code, 'http://localhost:3000'));

            return response()->json([
                'status' => 'success',
                'message' => 'Código generado y guardado en la BD para ' . $request->correo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Guardado en BD, pero falló el envío: ' . $e->getMessage()
            ], 500);
        }
    }

    // ENDPOINT 2: Validar si el código es correcto y vigente
    public function validateCode(Request $request)
    {
        // 1. Validamos usando 'correo' para que coincida con tu JSON
        $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
            'code'   => 'required|string'
        ]);

        // 2. Buscamos el registro usando $request->correo (NO $request->email)
        $record = PasswordResetCode::where('correo', $request->correo)
            ->where('code', $request->code)
            ->first();

        // 3. Verificamos si existe y si han pasado menos de 5 minutos
        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'El código es incorrecto.'
            ], 422);
        }

        if ($record->created_at->addMinutes(5)->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'El código ha expirado (máximo 5 minutos).'
            ], 422);
        }

        // Si todo está bien
        return response()->json([
            'status' => 'success',
            'message' => 'Código válido, puedes proceder a cambiar la contraseña.'
        ], 200);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
            'code' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        $record = PasswordResetCode::where('correo', $request->correo)
            ->where('code', $request->code)
            ->first();

        if (!$record || $record->created_at->addMinutes(5)->isPast()) {
            return response()->json(['message' => 'Sesión expirada'], 422);
        }

        User::where('correo', $request->correo)->update([
            'password' => Hash::make($request->password)
        ]);

        $record->delete();

        return response()->json(['status' => 'success','message' => 'Contraseña actualizada con éxito'], 200);
    }
}
