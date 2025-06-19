<?php

namespace App\Http\Controllers\Company\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CompanyRepositoryInterface;

class CompanyDeleteController extends Controller
{
    public function __construct(protected CompanyRepositoryInterface $companyRepository)
    {
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $company = $this->companyRepository->find($id);

        if (!$company) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'Company not found'
            ], 404);
        }

        try {
            // Delete the company (includes file cleanup in repository)
            $this->companyRepository->delete($company);

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Company deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'Failed to delete company: ' . $e->getMessage()
            ], 500);
        }
    }
}
