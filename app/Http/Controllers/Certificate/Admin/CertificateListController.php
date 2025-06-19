<?php

namespace App\Http\Controllers\Certificate\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CertificateRepositoryInterface;

class CertificateListController extends Controller
{
    public function __construct(
        protected CertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $certificates = $this->certificateRepository->query()
        ->whereNull('deleted_at')
        ->when(!empty($request->search), function($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
        ->when(!empty($request->type), function($query) use ($request) {
            $query->where('type', $request->type);
        })
        ->paginate($request->per_page ?? 10);

        return response()->json($certificates);
    }
}
