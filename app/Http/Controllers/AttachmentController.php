<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Device;
use App\Models\RepairTicket;
use App\Services\AttachmentStorageService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(
        protected AttachmentStorageService $attachmentStorageService,
        protected TenantManager $tenantManager
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Attachment::class);

        $validated = $request->validate([
            'attachable_type' => ['required', 'string', 'in:ticket,device'],
            'attachable_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:10240', 'mimes:jpeg,jpg,png,webp,pdf,txt'],
            'file_category' => ['nullable', 'string', 'in:DEVICE_PHOTO,DIAGNOSTIC,TEST_RESULT,DEVIS_SIGNE,AUTRE'],
            'is_public_to_client' => ['nullable', 'boolean'],
        ]);

        $modelClass = match ($validated['attachable_type']) {
            'ticket' => RepairTicket::class,
            'device' => Device::class,
        };

        $model = $modelClass::findOrFail($validated['attachable_id']);

        // Extra verification: model must belong to active tenant
        if ($model->tenant_id !== $this->tenantManager->getTenantId()) {
            abort(403, 'Action non autorisée sur cette ressource.');
        }

        try {
            $this->attachmentStorageService->storeAttachment(
                attachable: $model,
                file: $request->file('file'),
                category: $validated['file_category'] ?? 'DEVICE_PHOTO',
                isPublicToClient: (bool) ($request->input('is_public_to_client', false)),
                user: auth()->user()
            );

            return back()->with('success', 'Fichier téléversé avec succès.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Attachment $attachment): BinaryFileResponse|StreamedResponse
    {
        $this->authorize('view', $attachment);

        if (! Storage::disk('local')->exists($attachment->storage_path)) {
            abort(404, 'Fichier introuvable sur le disque.');
        }

        $fullPath = Storage::disk('local')->path($attachment->storage_path);

        return response()->file($fullPath, [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->original_filename) . '"',
        ]);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment);

        if (! Storage::disk('local')->exists($attachment->storage_path)) {
            abort(404, 'Fichier introuvable sur le disque.');
        }

        return Storage::disk('local')->download($attachment->storage_path, $attachment->original_filename);
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        $this->attachmentStorageService->deleteAttachment($attachment, auth()->user());

        return back()->with('success', 'Pièce jointe supprimée avec succès.');
    }
}
