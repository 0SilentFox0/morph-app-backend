<?php

namespace App\Services;

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Initiate a file upload and return a pre-signed URL for the client to upload to.
     *
     * @param  User   $user
     * @param  string $purpose
     * @param  string $mime
     * @param  int    $sizeBytes
     * @param  string $originalName
     * @return array{media_file: MediaFile, upload_url: string}
     */
    public function initiateUpload(
        User $user,
        string $purpose,
        string $mime,
        int $sizeBytes,
        string $originalName,
    ): array {
        $bucket = config('filesystems.disks.s3.bucket');
        $s3Key = sprintf('%s/%s/%s', $purpose, $user->id, Str::uuid() . '.' . pathinfo($originalName, PATHINFO_EXTENSION));

        $mediaFile = MediaFile::create([
            'owner_user_id' => $user->id,
            'purpose'       => $purpose,
            'mime'          => $mime,
            'size_bytes'    => $sizeBytes,
            's3_bucket'     => $bucket,
            's3_key'        => $s3Key,
            'original_name' => $originalName,
            'status'        => 'pending',
        ]);

        $uploadUrl = Storage::disk('s3')->temporaryUploadUrl($s3Key, now()->addMinutes(30));

        return [
            'media_file' => $mediaFile,
            'upload_url' => $uploadUrl,
        ];
    }

    /**
     * Mark a file upload as completed.
     */
    public function completeUpload(MediaFile $mediaFile): MediaFile
    {
        $mediaFile->update([
            'status'      => 'completed',
            'uploaded_at' => now(),
        ]);

        return $mediaFile->refresh();
    }

    /**
     * Generate a signed URL for downloading / viewing a file.
     */
    public function getSignedUrl(MediaFile $mediaFile, int $expirationMinutes = 60): string
    {
        return Storage::disk('s3')->temporaryUrl(
            $mediaFile->s3_key,
            now()->addMinutes($expirationMinutes),
        );
    }

    /**
     * Delete a file from storage and soft-delete the record.
     */
    public function deleteFile(MediaFile $mediaFile): void
    {
        Storage::disk('s3')->delete($mediaFile->s3_key);

        $mediaFile->delete();
    }
}
