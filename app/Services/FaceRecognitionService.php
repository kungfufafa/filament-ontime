<?php

namespace App\Services;

use App\Models\CompanyPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionService
{
    /**
     * Compare a captured selfie photo with a worker's registered master face photo.
     *
     * @param  string|null  $selfiePath  Storage relative path or absolute path to selfie
     * @param  string|null  $masterPhotoPath  Storage relative path to registered master photo
     * @return array{is_matched: bool, score: int, notes: string}
     */
    public function verifyFace(?string $selfiePath, ?string $masterPhotoPath, ?CompanyPolicy $policy = null): array
    {
        $threshold = $policy?->face_match_threshold ?? 60;

        if (empty($masterPhotoPath)) {
            return [
                'is_matched' => false,
                'score' => 0,
                'notes' => 'Foto Master Wajah belum didaftarkan pada profil karyawan.',
            ];
        }

        if (empty($selfiePath)) {
            return [
                'is_matched' => false,
                'score' => 0,
                'notes' => 'Foto selfie absensi tidak ditemukan.',
            ];
        }

        $disk = config('filesystems.default', 'public');

        try {
            $getPhotoContents = function (?string $path) use ($disk): ?string {
                if (empty($path)) {
                    return null;
                }

                if (Storage::disk('public')->exists($path)) {
                    return Storage::disk('public')->get($path);
                }

                if (Storage::disk($disk)->exists($path)) {
                    return Storage::disk($disk)->get($path);
                }

                if (Storage::disk('local')->exists($path)) {
                    return Storage::disk('local')->get($path);
                }

                if (file_exists($path)) {
                    return file_get_contents($path);
                }

                return null;
            };

            $masterContents = $getPhotoContents($masterPhotoPath);
            $selfieContents = $getPhotoContents($selfiePath);

            if (! $masterContents || ! $selfieContents) {
                return [
                    'is_matched' => false,
                    'score' => 0,
                    'notes' => 'Gagal membaca berkas foto master atau foto selfie dari storage.',
                ];
            }

            $masterImage = @imagecreatefromstring($masterContents);
            $selfieImage = @imagecreatefromstring($selfieContents);

            if (! $masterImage || ! $selfieImage) {
                return [
                    'is_matched' => false,
                    'score' => 0,
                    'notes' => 'Format foto tidak valid untuk pemrosesan citra.',
                ];
            }

            $score = $this->calculateSimilarityScore($masterImage, $selfieImage);

            imagedestroy($masterImage);
            imagedestroy($selfieImage);

            $isMatched = $score >= $threshold;
            $notes = $isMatched
                ? "Verifikasi wajah berhasil (Skor: {$score}%, Threshold: {$threshold}%)."
                : "Tingkat kemiripan wajah rendah (Skor: {$score}%, Threshold minimum: {$threshold}%).";

            return [
                'is_matched' => $isMatched,
                'score' => $score,
                'notes' => $notes,
            ];
        } catch (\Throwable $e) {
            Log::error('Face recognition verification error: '.$e->getMessage());

            return [
                'is_matched' => false,
                'score' => 0,
                'notes' => 'Terjadi kesalahan sistem saat memproses verifikasi wajah.',
            ];
        }
    }

    /**
     * Calculate structural perceptual similarity score (0-100) between two GD image resources.
     */
    protected function calculateSimilarityScore($img1, $img2): int
    {
        $targetWidth = 32;
        $targetHeight = 32;

        $thumb1 = imagecreatetruecolor($targetWidth, $targetHeight);
        $thumb2 = imagecreatetruecolor($targetWidth, $targetHeight);

        imagecopyresampled($thumb1, $img1, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($img1), imagesy($img1));
        imagecopyresampled($thumb2, $img2, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($img2), imagesy($img2));

        $diffSum = 0;
        $maxDiff = $targetWidth * $targetHeight * 255 * 3;

        for ($x = 0; $x < $targetWidth; $x++) {
            for ($y = 0; $y < $targetHeight; $y++) {
                $rgb1 = imagecolorat($thumb1, $x, $y);
                $rgb2 = imagecolorat($thumb2, $x, $y);

                $r1 = ($rgb1 >> 16) & 0xFF;
                $g1 = ($rgb1 >> 8) & 0xFF;
                $b1 = $rgb1 & 0xFF;

                $r2 = ($rgb2 >> 16) & 0xFF;
                $g2 = ($rgb2 >> 8) & 0xFF;
                $b2 = $rgb2 & 0xFF;

                $diffSum += abs($r1 - $r2) + abs($g1 - $g2) + abs($b1 - $b2);
            }
        }

        imagedestroy($thumb1);
        imagedestroy($thumb2);

        $similarityRatio = 1 - ($diffSum / $maxDiff);

        // Normalize ratio to percentage score
        $score = (int) round($similarityRatio * 100);

        return max(0, min(100, $score));
    }
}
