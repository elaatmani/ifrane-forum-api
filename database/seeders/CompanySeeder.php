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
                'logo' => 'companies/logos/1750346693_rTpxuepUR8.png',
                'background_image' => 'companies/backgrounds/1750346693_NpCTtl3oK3.jpg',
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
                'country_id' => 120,
                'created_at' => '2025-06-18 10:53:21',
                'updated_at' => '2025-06-19 16:24:53',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'The FoodeShow',
                'created_by' => 1,
                'streaming_platform' => 'twitch',
                'logo' => 'companies/logos/1750346645_Dmk6GfubJz.png',
                'background_image' => 'companies/backgrounds/1750346645_iZScXAt9ih.png',
                'address' => 'Marrakech',
                'primary_phone' => '0612345678',
                'secondary_phone' => null,
                'description' => 'The first global virtual food trade platform is an effort to spark a movement that defies borders, local markets and build an international community. Our vision is to create a global platform to connect businesses in the food industry ecosystem.

Grow your network and and your export markets You can get participate as an attendee, sponsor, speaker, advertiser or exhibitor.

Why Virtual The future is digital or at least hybrid.

we also care about our planet, that we want to help reduce the carbon footprint

It offers a better return on investment and low barrier of entry. There are no waiting lists, everyone is welcome

Our app will go beyond the 4 days and for 4 months you can connect with new leads and partners and expand your business The sectors represented

Drinks and Beverages
Sauces and Condiments
Oils and fats
Pulses grains and cereals
Health wellness and free from
Halal/ Kosher
Gourmet and delicatessen
Power brands
Packaging
Services and IT
Trade organizations
Other that exhibiting
Live sessions and webinars with inspiring speakers leading the way to change.

An opportunity to engage with your brand and promote your products at your own pace and involving more associate that you can afford to take to international shows.

A hosted buyer program with food service and retail power names interested in sourcing products from all over the world.

A space to meet your next supplier and service provider with live demos and promotional offers to try the services before committing.

Exhibitor subscription starting at £99 and yearly personnal subscription for £69 to have access to the community for over 100 countries Gain momentum and expand your network and business',
                'primary_email' => 'info@thefoodeshow.com',
                'secondary_email' => null,
                'website' => 'https://thefoodeshow.com',
                'facebook' => 'https://facebook.com',
                'twitter' => 'https://x.com',
                'instagram' => 'https://instagram.com',
                'linkedin' => 'https://linkedin.com',
                'youtube' => 'https://youtube.com',
                'country_id' => 120,
                'created_at' => '2025-06-18 11:13:48',
                'updated_at' => '2025-06-19 16:24:05',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Greativa Consulting Group',
                'created_by' => 1,
                'streaming_platform' => null,
                'logo' => 'companies/logos/1750346511_vJ4nWruleY.jpg',
                'background_image' => 'companies/backgrounds/1750346511_KFfrv0dWcG.jpg',
                'address' => 'Marrakech',
                'primary_phone' => '0664929747',
                'secondary_phone' => null,
                'description' => 'Description demo text',
                'primary_email' => 'info@greativaconsulting.com',
                'secondary_email' => null,
                'website' => 'https://greativa.ma',
                'facebook' => 'https://facebook.com/GreativaCG',
                'twitter' => 'https://x.com/GreativaHG',
                'instagram' => 'https://instagram.com/greqtivacg',
                'linkedin' => 'https://www.linkedin.com/company/greativa',
                'youtube' => 'https://www.youtube.com/@greativa',
                'country_id' => 120,
                'created_at' => '2025-06-19 09:57:02',
                'updated_at' => '2025-06-19 16:21:51',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'MONARK IT',
                'created_by' => 1,
                'streaming_platform' => null,
                'logo' => 'companies/logos/1750346374_RQ13dOwXBj.png',
                'background_image' => 'companies/backgrounds/1750346374_kZglpL6hHw.png',
                'address' => 'Bergis Business Center, 5th floor Office 18, Avenue Safi - Marrakesh',
                'primary_phone' => '0808503103',
                'secondary_phone' => null,
                'description' => "MONARK IT is your trusted digital agency for all your web, mobile, and artificial intelligence development projects.
We're based in Morocco, France, and Qatar — and we work internationally to serve clients all over the world. 🌍

Whether you're looking for an outsourcing partnership, want to bring an innovative web or mobile app idea to life, or need to optimize your business processes — we're here to help! 😉

No matter your constraints, industry, or budget, our mission is to simplify your digital needs with a dedicated team of experts focused on finding the most suitable solution.
▶️ We're a small, agile team with big added value. 🌟
▶️ Our tools: expertise, creative ideas, and great tea. 🛠️
▶️ Our motivation: the success of your project. 🎯

Many partners have already trusted us — and together, we've achieved great results. 🤝

Be the next to join us!",
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
                'updated_at' => '2025-06-19 16:25:40',
                'deleted_at' => null,
            ],
        ];

        foreach ($companies as $company) {
            Company::query()->updateOrCreate(['id' => $company['id']], $company);
        }
    }
}
