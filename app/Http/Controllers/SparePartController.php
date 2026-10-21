<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparePartController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SparePart::class);

        $tenant = $this->tenantManager->getTenant();
        $query = SparePart::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('brand_compatibility', 'LIKE', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock_alert');
        }

        $parts = $query->orderBy('name')->paginate(15)->withQueryString();

        $totalStockValue = (float) SparePart::where('is_active', true)
            ->selectRaw('COALESCE(SUM(stock_quantity * purchase_price), 0) as val')
            ->value('val');

        $lowStockCount = SparePart::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->count();

        return view('parts.index', compact('parts', 'tenant', 'totalStockValue', 'lowStockCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SparePart::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['nullable', 'string', 'max:80'],
            'category' => ['required', 'string', 'in:ECRAN,BATTERIE,STOCKAGE,MEMOIRE,CONNECTEUR,CARTE_MERE,CHARGEUR,AUTRE'],
            'brand_compatibility' => ['nullable', 'string', 'max:150'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
        ]);

        $part = SparePart::create([
            'tenant_id' => $this->tenantManager->getTenantId(),
            ...$validated,
            'is_active' => true,
        ]);

        $this->auditService->log(
            event: 'spare_part.created',
            auditable: $part,
            newValues: ['name' => $part->name, 'stock_quantity' => $part->stock_quantity, 'selling_price' => $part->selling_price]
        );

        return redirect()->route('parts.index')->with('success', "Pièce « {$part->name} » ajoutée au stock.");
    }

    public function update(Request $request, SparePart $part): RedirectResponse
    {
        $this->authorize('update', $part);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['nullable', 'string', 'max:80'],
            'category' => ['required', 'string', 'in:ECRAN,BATTERIE,STOCKAGE,MEMOIRE,CONNECTEUR,CARTE_MERE,CHARGEUR,AUTRE'],
            'brand_compatibility' => ['nullable', 'string', 'max:150'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
        ]);

        $old = $part->only(array_keys($validated));
        $part->update($validated);

        $this->auditService->log(
            event: 'spare_part.updated',
            auditable: $part,
            oldValues: $old,
            newValues: $part->only(array_keys($validated))
        );

        return redirect()->route('parts.index')->with('success', "Pièce « {$part->name} » mise à jour.");
    }

    public function adjustStock(Request $request, SparePart $part): RedirectResponse
    {
        $this->authorize('update', $part);

        $validated = $request->validate([
            'delta' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        $oldQty = $part->stock_quantity;
        $newQty = max(0, $oldQty + (int) $validated['delta']);
        $part->update(['stock_quantity' => $newQty]);

        $this->auditService->log(
            event: 'spare_part.stock_adjusted',
            auditable: $part,
            oldValues: ['stock_quantity' => $oldQty],
            newValues: ['stock_quantity' => $newQty, 'delta' => $validated['delta'], 'reason' => $validated['reason'] ?? null]
        );

        return redirect()->route('parts.index')->with('success', "Stock de « {$part->name} » ajusté ({$newQty} en stock).");
    }

    public function destroy(SparePart $part): RedirectResponse
    {
        $this->authorize('delete', $part);

        $part->delete();

        $this->auditService->log(
            event: 'spare_part.archived',
            auditable: $part,
            newValues: ['name' => $part->name]
        );

        return redirect()->route('parts.index')->with('success', "Pièce « {$part->name} » archivée.");
    }
}
