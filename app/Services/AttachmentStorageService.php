<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AttachmentStorageService
{
    protected array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/heic',
        'application/pdf',
        'text/plain',
    ];

    protected int $maxSizeBytes = 10 * 1024 * 1024; // 10 MB

    public function __construct(
        protected AuditService $auditService
    ) {}

    public function storeAttachment(
        Model $attachable,
        UploadedFile $file,
        string $category = 'DEVICE_PHOTO',
        bool $isPublicToClient = false,
        ?User $user = null
    ): Attachment {
        $tenantId = $attachable->tenant_id ?? app(TenantManager::class)->getTenantId();

        // 1. Strict real MIME check
        $mime = $file->getMimeType();
        if (! in_array($mime, $this->allowedMimes, true)) {
            throw new InvalidArgumentException("Type de fichier non autorisé ({$mime}). Formats acceptés : JPEG, PNG, WEBP, PDF.");
        }

        // 2. Strict size check
        if ($file->getSize() > $this->maxSizeBytes) {
            throw new InvalidArgumentException("Le fichier dépasse la taille maximale autorisée de 10 Mo.");
        }

        // 3. Server-generated safe filename
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $safeName = Str::uuid()->toString() . '.' . strtolower($extension);
        $directory = "private/tenants/{$tenantId}/{$category}";

        // 4. Store in private disk
        $path = $file->storeAs($directory, $safeName, 'local');

        // 5. Create Attachment record
        $attachment = Attachment::create([
            'tenant_id' => $tenantId,
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->getKey(),
            'user_id' => $user?->id,
            'original_filename' => substr(basename($file->getClientOriginalName()), 0, 200),
            'storage_path' => $path,
            'mime_type' => $mime,
            'file_size' => $file->getSize(),
            'file_category' => $category,
            'is_public_to_client' => $isPublicToClient,
        ]);

        $this->auditService->log(
            event: 'attachment.uploaded',
            auditable: $attachment,
            newValues: ['filename' => $attachment->original_filename, 'size' => $attachment->file_size],
            userId: $user?->id,
            tenantId: $tenantId
        );

        return $attachment;
    }

    public function deleteAttachment(Attachment $attachment, ?User $user = null): bool
    {
        if (Storage::disk('local')->exists($attachment->storage_path)) {
            Storage::disk('local')->delete($attachment->storage_path);
        }

        $this->auditService->log(
            event: 'attachment.deleted',
            auditable: $attachment,
            newValues: ['filename' => $attachment->original_filename],
            userId: $user?->id,
            tenantId: $attachment->tenant_id
        );

        return $attachment->delete();
    }
}
