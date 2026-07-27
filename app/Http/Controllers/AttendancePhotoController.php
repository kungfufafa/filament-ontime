<?php

namespace App\Http\Controllers;

use App\Services\FileNamingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AttendancePhotoController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $employeeCode = $user?->employee?->employee_code ?? (string) ($user?->id ?? 'ANONYMOUS');

            // Option 1: File upload via FormData ('photo')
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                if ($file && $file->isValid()) {
                    $path = FileNamingService::storeUploadedFile(
                        $file,
                        'attendance-photos',
                        'ATT',
                        $employeeCode,
                        's3'
                    );

                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'url' => Storage::disk('s3')->url($path),
                    ]);
                }

                $errorMsg = $file ? $file->getErrorMessage() : 'File photo tidak valid.';

                return response()->json([
                    'success' => false,
                    'message' => 'Upload file foto gagal: '.$errorMsg,
                ], 422);
            }

            // Option 2: Base64 string payload ('photo_base64')
            $photoBase64 = $request->input('photo_base64');

            if (filled($photoBase64)) {
                if (str_contains($photoBase64, 'base64,')) {
                    $photoBase64 = explode('base64,', $photoBase64)[1] ?? '';
                }

                $photoBase64 = str_replace(' ', '+', trim($photoBase64));
                $decoded = base64_decode($photoBase64);

                if ($decoded !== false && strlen($decoded) > 0) {
                    $filename = FileNamingService::generateFileName('ATT', $employeeCode, 'selfie.jpg');
                    $path = 'attendance-photos/'.$filename;
                    Storage::disk('s3')->put($path, $decoded);

                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'url' => Storage::disk('s3')->url($path),
                    ]);
                }
            }

            $keysReceived = implode(', ', array_keys($request->all()));

            return response()->json([
                'success' => false,
                'message' => 'Foto tidak valid atau gagal diterima. Parameter yang diterima: '.($keysReceived ?: 'kosong'),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Upload selfie error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server: '.$e->getMessage(),
            ], 500);
        }
    }
}
