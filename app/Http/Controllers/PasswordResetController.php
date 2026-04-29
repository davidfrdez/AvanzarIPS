<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\SendResetCode;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetController extends Controller
{
    public function sendCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
        ]);

        PasswordResetCode::where('correo', $data['correo'])->delete();

        // Código de 8 caracteres alfanuméricos (~ 36^8 = 2.8B combinaciones).
        $code = Str::upper(Str::random(8));

        PasswordResetCode::create([
            'correo' => $data['correo'],
            'code' => $code,
            'created_at' => now(),
        ]);

        try {
            Mail::to($data['correo'])->send(new SendResetCode($code, config('app.frontend_url', 'http://localhost:3000')));
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Guardado en BD, pero falló el envío: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Código generado y enviado a ' . $data['correo'],
        ]);
    }

    public function validateCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
            'code' => 'required|string',
        ]);

        $record = PasswordResetCode::where('correo', $data['correo'])
            ->where('code', $data['code'])
            ->first();

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'El código es incorrecto.',
            ], 422);
        }

        if ($record->created_at->addMinutes(5)->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'El código ha expirado (máximo 5 minutos).',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Código válido, puedes proceder a cambiar la contraseña.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = PasswordResetCode::where('correo', $data['correo'])
            ->where('code', $data['code'])
            ->first();

        if (!$record || $record->created_at->addMinutes(5)->isPast()) {
            return response()->json(['message' => 'Sesión expirada'], 422);
        }

        // C11: usar save() (no update masivo) para disparar el trait Auditable.
        $user = User::where('correo', $data['correo'])->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->save();

        $record->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada con éxito',
        ]);
    }
}
