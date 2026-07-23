<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendancePhotoController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $photoBase64 = $request->input('photo_base64');

        if (filled($photoBase64) && str_contains($photoBase64, 'base64,')) {
            $imageData = explode('base64,', $photoBase64)[1] ?? '';
            $decoded = base64_decode($imageData);
            $path = 'attendance-photos/'.uniqid('selfie_').'.jpg';
            Storage::disk('public')->put($path, $decoded);

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ]);
        }

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $path = $request->file('photo')->store('attendance-photos', 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Foto tidak valid atau gagal diterima.',
        ], 422);
    }
}
