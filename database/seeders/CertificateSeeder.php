<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createProductCertificates();
        $this->createServiceCertificates();
        $this->createCompanyCertificates();
    }

    /**
     * Create product-specific certificates
     */
    private function createProductCertificates(): void
    {
        $productCertificates = [
            [
                'name' => 'ISO 22000',
                'code' => 'ISO22000',
                'description' => 'Food Safety Management System certification'
            ],
            [
                'name' => 'FSSC 22000',
                'code' => 'FSSC22000',
                'description' => 'Food Safety System Certification 22000'
            ],
            [
                'name' => 'BRC Global Standards',
                'code' => 'BRC',
                'description' => 'British Retail Consortium Global Standards for food safety'
            ],
            [
                'name' => 'IFS Food',
                'code' => 'IFS',
                'description' => 'International Featured Standards for food safety and quality'
            ],
            [
                'name' => 'HACCP',
                'code' => 'HACCP',
                'description' => 'Hazard Analysis and Critical Control Points certification'
            ],
            [
                'name' => 'Organic Certification',
                'code' => 'ORGANIC',
                'description' => 'Certification for organic food and agricultural products'
            ],
            [
                'name' => 'Non-GMO Project',
                'code' => 'NONGMO',
                'description' => 'Non-Genetically Modified Organism certification'
            ],
            [
                'name' => 'Vegan Certification',
                'code' => 'VEGAN',
                'description' => 'Certification for vegan-friendly products'
            ],
            [
                'name' => 'Kosher Certification',
                'code' => 'KOSHER',
                'description' => 'Jewish dietary law compliance certification'
            ],
            [
                'name' => 'Halal Certification',
                'code' => 'HALAL',
                'description' => 'Islamic dietary law compliance certification'
            ],
            [
                'name' => 'Fair Trade',
                'code' => 'FAIRTRADE',
                'description' => 'Fair trade certification for ethical sourcing'
            ],
            [
                'name' => 'GMP',
                'code' => 'GMP',
                'description' => 'Good Manufacturing Practice certification'
            ],
            [
                'name' => 'SQF',
                'code' => 'SQF',
                'description' => 'Safe Quality Food certification'
            ],
            [
                'name' => 'GlobalGAP',
                'code' => 'GLOBALGAP',
                'description' => 'Global Good Agricultural Practice certification'
            ],
            [
                'name' => 'Rainforest Alliance',
                'code' => 'RAINFOREST',
                'description' => 'Rainforest Alliance certification for sustainable agriculture'
            ],
            [
                'name' => 'OEKO-TEX',
                'code' => 'OEKOTEX',
                'description' => 'Textile safety and sustainability certification'
            ],
            [
                'name' => 'GOTS',
                'code' => 'GOTS',
                'description' => 'Global Organic Textile Standard certification'
            ],
            [
                'name' => 'Bluesign',
                'code' => 'BLUESIGN',
                'description' => 'Textile sustainability and safety certification'
            ],
            [
                'name' => 'REACH Compliance',
                'code' => 'REACH',
                'description' => 'Registration, Evaluation, Authorization of Chemicals compliance'
            ],
            [
                'name' => 'TSCA Compliance',
                'code' => 'TSCA',
                'description' => 'Toxic Substances Control Act compliance'
            ],
            [
                'name' => 'GHS Compliance',
                'code' => 'GHS',
                'description' => 'Globally Harmonized System of Classification and Labelling compliance'
            ]
        ];

        foreach ($productCertificates as $certificate) {
            Certificate::create([
                'name' => $certificate['name'],
                'code' => $certificate['code'],
                'description' => $certificate['description'],
                'type' => 'product',
            ]);
        }
    }

    /**
     * Create service-specific certificates
     */
    private function createServiceCertificates(): void
    {
        $serviceCertificates = [
            [
                'name' => 'ISO 9001',
                'code' => 'ISO9001',
                'description' => 'Quality Management System certification'
            ],
            [
                'name' => 'ISO 14001',
                'code' => 'ISO14001',
                'description' => 'Environmental Management System certification'
            ],
            [
                'name' => 'ISO 45001',
                'code' => 'ISO45001',
                'description' => 'Occupational Health and Safety Management System'
            ],
            [
                'name' => 'OHSAS 18001',
                'code' => 'OHSAS18001',
                'description' => 'Occupational Health and Safety Assessment Series'
            ],
            [
                'name' => 'SMETA',
                'code' => 'SMETA',
                'description' => 'Sedex Members Ethical Trade Audit'
            ],
            [
                'name' => 'SA8000',
                'code' => 'SA8000',
                'description' => 'Social Accountability International certification'
            ],
            [
                'name' => 'ISO 27001',
                'code' => 'ISO27001',
                'description' => 'Information Security Management System'
            ],
            [
                'name' => 'ISO 20000',
                'code' => 'ISO20000',
                'description' => 'IT Service Management System certification'
            ],
            [
                'name' => 'CMMI',
                'code' => 'CMMI',
                'description' => 'Capability Maturity Model Integration'
            ],
            [
                'name' => 'ITIL',
                'code' => 'ITIL',
                'description' => 'Information Technology Infrastructure Library certification'
            ],
            [
                'name' => 'PMP',
                'code' => 'PMP',
                'description' => 'Project Management Professional certification'
            ],
            [
                'name' => 'PRINCE2',
                'code' => 'PRINCE2',
                'description' => 'Projects IN Controlled Environments certification'
            ],
            [
                'name' => 'Six Sigma',
                'code' => 'SIXSIGMA',
                'description' => 'Six Sigma quality management methodology certification'
            ],
            [
                'name' => 'Lean Management',
                'code' => 'LEAN',
                'description' => 'Lean management methodology certification'
            ]
        ];

        foreach ($serviceCertificates as $certificate) {
            Certificate::create([
                'name' => $certificate['name'],
                'code' => $certificate['code'],
                'description' => $certificate['description'],
                'type' => 'service',
            ]);
        }
    }

    /**
     * Create company-specific certificates
     */
    private function createCompanyCertificates(): void
    {
        $companyCertificates = [
            [
                'name' => 'Export License',
                'code' => 'EXPORT_LICENSE',
                'description' => 'Government-issued license for exporting goods'
            ],
            [
                'name' => 'Import License',
                'code' => 'IMPORT_LICENSE',
                'description' => 'Government-issued license for importing goods'
            ],
            [
                'name' => 'Business Registration',
                'code' => 'BUSINESS_REG',
                'description' => 'Official business registration certificate'
            ],
            [
                'name' => 'Tax Registration',
                'code' => 'TAX_REG',
                'description' => 'Tax identification and registration certificate'
            ],
            [
                'name' => 'VAT Registration',
                'code' => 'VAT_REG',
                'description' => 'Value Added Tax registration certificate'
            ],
            [
                'name' => 'ISO 14000',
                'code' => 'ISO14000',
                'description' => 'Environmental Management System certification'
            ],
            [
                'name' => 'Corporate Social Responsibility',
                'code' => 'CSR',
                'description' => 'Corporate Social Responsibility certification'
            ],
            [
                'name' => 'B Corp Certification',
                'code' => 'BCORP',
                'description' => 'B Corporation certification for social and environmental performance'
            ],
            [
                'name' => 'LEED Certification',
                'code' => 'LEED',
                'description' => 'Leadership in Energy and Environmental Design certification'
            ],
            [
                'name' => 'BREEAM',
                'code' => 'BREEAM',
                'description' => 'Building Research Establishment Environmental Assessment Method'
            ],
            [
                'name' => 'Energy Star',
                'code' => 'ENERGYSTAR',
                'description' => 'Energy efficiency certification program'
            ],
            [
                'name' => 'Carbon Trust',
                'code' => 'CARBONTRUST',
                'description' => 'Carbon footprint and energy efficiency certification'
            ],
            [
                'name' => 'FSC Certification',
                'code' => 'FSC',
                'description' => 'Forest Stewardship Council certification'
            ],
            [
                'name' => 'PEFC Certification',
                'code' => 'PEFC',
                'description' => 'Programme for the Endorsement of Forest Certification'
            ],
            [
                'name' => 'Fair Labor Association',
                'code' => 'FLA',
                'description' => 'Fair Labor Association workplace standards certification'
            ],
            [
                'name' => 'Ethical Trading Initiative',
                'code' => 'ETI',
                'description' => 'Ethical Trading Initiative certification'
            ]
        ];

        foreach ($companyCertificates as $certificate) {
            Certificate::create([
                'name' => $certificate['name'],
                'code' => $certificate['code'],
                'description' => $certificate['description'],
                'type' => 'company',
            ]);
        }
    }
}
