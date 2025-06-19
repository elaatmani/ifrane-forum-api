<?php

namespace App\Http\Controllers\Certificate\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CertificateRepositoryInterface;

class CertificateDeleteController extends Controller
{
    public function __construct(
        protected CertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $certificate = $this->certificateRepository->find($request->id);
        $certificate->delete();

        return response()->json([
            'message' => 'Certificate deleted successfully'
        ]);
    }
}
