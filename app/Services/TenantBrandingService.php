<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TenantBrandingService
{
    public function refreshMergedStampSignature(Tenant $tenant): void
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];

        $stampPath = $settings['stamp_logo_path'] ?? null;
        $signaturePath = $settings['signature_path'] ?? null;

        if (! $stampPath || ! $signaturePath) {
            unset($settings['stamp_signature_merged_path']);
            $tenant->forceFill(['settings' => $settings])->save();

            return;
        }

        if (! function_exists('imagecreatefromstring')) {
            // Fallback: if GD is unavailable, use signature file directly.
            $settings['stamp_signature_merged_path'] = $signaturePath;
            $tenant->forceFill(['settings' => $settings])->save();

            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($stampPath) || ! $disk->exists($signaturePath)) {
            unset($settings['stamp_signature_merged_path']);
            $tenant->forceFill(['settings' => $settings])->save();

            return;
        }

        $stampBytes = $disk->get($stampPath);
        $signatureBytes = $disk->get($signaturePath);

        $stampImage = @imagecreatefromstring($stampBytes);
        $signatureImage = @imagecreatefromstring($signatureBytes);

        if (! $stampImage || ! $signatureImage) {
            throw new RuntimeException('Unable to read stamp/signature image.');
        }

        $stampWidth = imagesx($stampImage);
        $stampHeight = imagesy($stampImage);
        $signatureWidth = imagesx($signatureImage);
        $signatureHeight = imagesy($signatureImage);

        $canvasWidth = max($stampWidth, $signatureWidth);
        $canvasHeight = max($stampHeight, $signatureHeight);

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagealphablending($canvas, true);

        $stampX = (int) (($canvasWidth - $stampWidth) / 2);
        $stampY = (int) (($canvasHeight - $stampHeight) / 2);
        imagecopy($canvas, $stampImage, $stampX, $stampY, 0, 0, $stampWidth, $stampHeight);

        $signatureX = (int) (($canvasWidth - $signatureWidth) / 2);
        $signatureY = (int) (($canvasHeight - $signatureHeight) / 2);
        imagecopy($canvas, $signatureImage, $signatureX, $signatureY, 0, 0, $signatureWidth, $signatureHeight);

        $previousMergedPath = $settings['stamp_signature_merged_path'] ?? null;
        $outputPath = sprintf('tenant-assets/%s/stamp-signature-merged-%s.png', $tenant->id, now()->format('YmdHisv'));

        ob_start();
        imagepng($canvas);
        $pngBytes = ob_get_clean();

        if (! is_string($pngBytes)) {
            throw new RuntimeException('Failed to render merged stamp/signature image.');
        }

        $disk->put($outputPath, $pngBytes, 'public');

        if ($previousMergedPath && $previousMergedPath !== $outputPath && $disk->exists($previousMergedPath)) {
            $disk->delete($previousMergedPath);
        }

        imagedestroy($stampImage);
        imagedestroy($signatureImage);
        imagedestroy($canvas);

        $settings['stamp_signature_merged_path'] = $outputPath;
        $tenant->forceFill(['settings' => $settings])->save();
    }
}
