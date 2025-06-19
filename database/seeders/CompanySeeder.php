<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use Illuminate\Support\Carbon;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'id' => 1,
                'name' => 'Yassine El Aatmani',
                'created_by' => 1,
                'streaming_platform' => 'instagram',
                'logo' => 'companies/logos/1750240401_VBV72PPyw3.png',
                'background_image' => 'companies/backgrounds/1750240401_jBPY61YPDD.png',
                'address' => 'DR OULED BEN SBAA LOUDAYA MARRAKECH',
                'primary_phone' => '0608944024',
                'secondary_phone' => '0608944024',
                'description' => 'https://facebook.com',
                'primary_email' => 'devyassine48@gmail.com',
                'secondary_email' => 'devyassine48@gmail.com',
                'website' => 'https://facebook.com',
                'facebook' => 'https://facebook.com',
                'twitter' => 'https://facebook.com',
                'instagram' => 'https://facebook.com',
                'linkedin' => 'https://facebook.com',
                'youtube' => 'https://facebook.com',
                'country_id' => 2,
                'created_at' => '2025-06-18 10:53:21',
                'updated_at' => '2025-06-18 10:53:21',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'The FoodeShow',
                'created_by' => 1,
                'streaming_platform' => 'twitch',
                'logo' => 'companies/logos/1750324929_hB3AOpErQ9.png',
                'background_image' => 'companies/backgrounds/1750322791_waSmYuUOSi.jpg',
                'address' => 'Marrakech',
                'primary_phone' => '0608944024',
                'secondary_phone' => '0608944024',
                'description' => 'this is description',
                'primary_email' => 'contact@thefoodeshow.com',
                'secondary_email' => null,
                'website' => 'https://thefoodeshow.com',
                'facebook' => 'https://facebook.com',
                'twitter' => 'https://x.com',
                'instagram' => 'https://instagram.com',
                'linkedin' => 'https://linkedin.com',
                'youtube' => 'https://youtube.com',
                'country_id' => 120,
                'created_at' => '2025-06-18 11:13:48',
                'updated_at' => '2025-06-19 10:22:09',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Greativa Consulting Group',
                'created_by' => 1,
                'streaming_platform' => null,
                'logo' => 'companies/logos/1750323422_5CDBat0dqO.jpg',
                'background_image' => 'companies/backgrounds/1750323422_W8f8psSbxB.jpg',
                'address' => 'Marrakech',
                'primary_phone' => '0664929747',
                'secondary_phone' => null,
                'description' => 'Description demo text',
                'primary_email' => 'info@greativaconsulting.com',
                'secondary_email' => null,
                'website' => 'https://greativa.ma',
                'facebook' => null,
                'twitter' => null,
                'instagram' => null,
                'linkedin' => null,
                'youtube' => null,
                'country_id' => 120,
                'created_at' => '2025-06-19 09:57:02',
                'updated_at' => '2025-06-19 09:57:02',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'MONARK IT',
                'created_by' => 1,
                'streaming_platform' => null,
                'logo' => 'companies/logos/1750325156_0ErqZBRq2H.png',
                'background_image' => 'companies/backgrounds/1750325156_u24Z2y0ZXV.png',
                'address' => 'Bergis Business Center, 5th floor Office 18, Avenue Safi - Marrakesh',
                'primary_phone' => '+212808503103',
                'secondary_phone' => null,
                'description' => "MONARK IT is your trusted digital agency for all your web, mobile, and artificial intelligence development projects.\r\nWe're based in Morocco, France, and Qatar — and we work internationally to serve clients all over the world. 🌍\r\n\r\nWhether you're looking for an outsourcing partnership, want to bring an innovative web or mobile app idea to life, or need to optimize your business processes — we're here to help! 😉\r\n\r\nNo matter your constraints, industry, or budget, our mission is to simplify your digital needs with a dedicated team of experts focused on finding the most suitable solution.\r\n▶️ We're a small, agile team with big added value. 🌟\r\n▶️ Our tools: expertise, creative ideas, and great tea. 🛠️\r\n▶️ Our motivation: the success of your project. 🎯\r\n\r\nMany partners have already trusted us — and together, we've achieved great results. 🤝\r\n\r\nBe the next to join us!",
                'primary_email' => 'contact@monarkit.net',
                'secondary_email' => null,
                'website' => 'https://monarkit.net/',
                'facebook' => 'https://www.linkedin.com/company/monarkit/',
                'twitter' => 'https://www.linkedin.com/company/monarkit/',
                'instagram' => 'https://www.instagram.com/monarkit_net/',
                'linkedin' => 'https://www.linkedin.com/company/monarkit/',
                'youtube' => 'https://www.youtube.com/Monarkit',
                'country_id' => 120,
                'created_at' => '2025-06-19 10:25:56',
                'updated_at' => '2025-06-19 10:26:51',
                'deleted_at' => null,
            ],
        ];

        foreach ($companies as $company) {
            Company::query()->updateOrCreate(['id' => $company['id']], $company);
        }
    }
}
