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
            // Company::query()->updateOrCreate(['id' => $company['id']], $company);
        }

        $this->existingCompanies();
    }


    public function existingCompanies(): void
    {
        // Company data from company.json
        $companyData = [
            ["id" => "2", "name" => "Greativa Consulting Group", "streaming_platform" => null, "logo" => "Greativa Consulting Group/67b5f71933884.jpg", "background_image" => "Greativa Consulting Group/67b5f6c856dda.jpg", "address" => "A9, Burj Malak, Route de Safi, Marrakech, 40 000", "phone" => "0664929747", "description" => '<p>Greativa Consulting Group is a strategy and management consulting firm founded in 2008 by Heuda Farah Guessous. We provide you with a team of professional and dynamic consultants who combine industry expertise, creativity, and the ability to support multidisciplinary and complex projects.</p>

<p>Our expertise allows us to merge a technical and pragmatic approach with a managerial and holistic vision of the company. Our mission is to support you in enhancing the overall performance of your organization. In this regard, we offer a one-stop shop for all your strategic needs.</p>

<p>Greativa is a combination of two words: Great + Creativity. Our team is fully aware of the challenges of success and is committed to providing you with innovative and accessible solutions to support the ongoing changes in your company or organization.</p>', "email" => "info@greativaconsulting.com", "website" => "www.greativa.ma", "facebook" => "https://facebook.com/GreativaCG", "twitter" => "https://x.com/GreativaHG", "instagram" => "https://instagram.com/greqtivacg", "linkedin" => "https://www.linkedin.com/company/greativa", "youtube" => "https://www.youtube.com/@greativa", "country_id" => "120"],
            ["id" => "4", "name" => "JASMI IMPEX", "streaming_platform" => null, "logo" => "JASMI IMPEX/67b86d80c23ff.png", "background_image" => "JASMI IMPEX/67b86d80c2795.jpg", "address" => null, "phone" => null, "description" => '<p><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">To negotiate international logistics, Navigate international supply chain, work with our multilingual team of professionals . Our long-standing relationships with manufacturers, packers, and freight forwarders ensure that your goods are ready for the next step. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">We are based in Marrakech Morocco, serving clients in Anglo saxon markets since 2018. Our team as well as our network make sourcing easy for our clients when it comes to food items, beverages, packaging and machinery. Our network extends to the following countries </span></p>

<ul>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Egypt </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Ivory Coast </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">UAE </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Russia </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Spain </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Poland </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">USA </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Brazil </span></li>
</ul>

<p><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">From Argan oil to snacks we help you source high quality products . </span></p>

<p><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Our main services are :</span></p>

<ul>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Product / Machinery sourcing </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Food branding and labeling </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.6); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Freight negotiation</span></li>
</ul>', "email" => "sales@jasmi-impex.com", "website" => "http://www.jasmi-impex.com/", "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => "https://www.linkedin.com/company/jasmi-impex/", "youtube" => null, "country_id" => "120"],
            ["id" => "5", "name" => "A&B Bags", "streaming_platform" => null, "logo" => null, "background_image" => null, "address" => null, "phone" => null, "description" => null, "email" => "sales.abbags@gmail.com", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => null],
            ["id" => "6", "name" => "IQCC", "streaming_platform" => null, "logo" => "IQCC/67ff7e91f33e5.jpg", "background_image" => "", "address" => null, "phone" => null, "description" => null, "email" => "radi@iqcc.net", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => "188"],
            ["id" => "7", "name" => "MONARK IT", "streaming_platform" => null, "logo" => "MONARK IT/6824b343042eb.png", "background_image" => "MONARK IT/6824b4a67f583.png", "address" => "Bergis Business Center, 5th floor Office 18, Avenue Safi - Marrakesh", "phone" => "+212808503103", "description" => '<p><strong>MONARK IT is your trusted digital agency for all your web, mobile, and artificial intelligence development projects.<br />
We&rsquo;re based in Morocco, France, and Qatar &mdash; and we work internationally to serve clients all over the world. 🌍</strong></p>

<p>Whether you&#39;re looking for an outsourcing partnership, want to bring an innovative web or mobile app idea to life, or need to optimize your business processes &mdash; we&#39;re here to help! 😉</p>

<p>No matter your constraints, industry, or budget, our mission is to simplify your digital needs with a dedicated team of experts focused on finding the most suitable solution.<br />
▶️ We&rsquo;re a small, agile team with big added value. 🌟<br />
▶️ Our tools: expertise, creative ideas, and great tea. 🛠️<br />
▶️ Our motivation: the success of your project. 🎯</p>

<p>Many partners have already trusted us &mdash; and together, we&#39;ve achieved great results. 🤝</p>

<p><strong>Be the next to join us!</strong></p>', "email" => "contact@monarkit.net", "website" => "https://monarkit.net/", "facebook" => "https://www.facebook.com/Monarkit.net", "twitter" => "https://x.com/MONARK_IT", "instagram" => "https://www.instagram.com/monarkit_net/", "linkedin" => "https://www.linkedin.com/company/monarkit/", "youtube" => "https://www.youtube.com/Monarkit", "country_id" => "120"],
            ["id" => "8", "name" => "NUELINK", "streaming_platform" => null, "logo" => "NUELINK/67f0001375548.png", "background_image" => "", "address" => null, "phone" => null, "description" => '<p><strong>Social media scheduling with automation super powers (and AI)</strong><br />
Nuelink helps you organize, automate, analyze and manage your social media from one place and saves you time to focus on your business while your social media runs itself.</p>', "email" => "contact@nuelink.com", "website" => "https://nuelink.com/", "facebook" => "https://facebook.com/nuelinkapp", "twitter" => "https://x.com/nuelinkapp", "instagram" => "https://www.instagram.com/nuelinkapp/", "linkedin" => "https://www.linkedin.com/company/nuelink", "youtube" => "https://www.youtube.com/@nuelink/", "country_id" => "120"],
            ["id" => "9", "name" => "SUCRUNION", "streaming_platform" => null, "logo" => null, "background_image" => null, "address" => null, "phone" => null, "description" => null, "email" => "sucrunion@gmail.com", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => null],
            ["id" => "10", "name" => "SEOCOM", "streaming_platform" => null, "logo" => null, "background_image" => null, "address" => null, "phone" => null, "description" => null, "email" => "contact@seocom.ma", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => null],
            ["id" => "11", "name" => "TN'KOFFEE", "streaming_platform" => null, "logo" => null, "background_image" => null, "address" => null, "phone" => null, "description" => null, "email" => "ijk@tnk.ma", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => null],
            ["id" => "12", "name" => "TRADE SOCIAL", "streaming_platform" => null, "logo" => null, "background_image" => null, "address" => null, "phone" => null, "description" => null, "email" => "kacem.n@tradesocial.tech", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => null],
            ["id" => "13", "name" => "The FoodEshow", "streaming_platform" => null, "logo" => "The FoodEshow/67b8770697925.png", "background_image" => "The FoodEshow/67b8770697c88.png", "address" => null, "phone" => null, "description" => '<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">The first global virtual food trade platform is an effort to spark a movement that defies borders, local markets and build an international community. Our vision is to create a global platform to connect businesses in the food industry ecosystem. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Grow your network and and your export markets You can get participate as an attendee, sponsor, speaker, advertiser or exhibitor. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Why Virtual The future is digital or at least hybrid. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">we also care about our planet, that we want to help reduce the carbon footprint </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">It offers a better return on investment and low barrier of entry.&nbsp;</span><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">There are no waiting lists, everyone is welcome</span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Our app will go beyond the 4 days and for 4 months you can connect with new leads and partners and expand your business The sectors represented </span></p>

<ul>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Drinks and Beverages </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Sauces and Condiments </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Oils and fats </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Pulses grains and cereals </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Health wellness and free from </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Halal/ Kosher </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Gourmet and delicatessen </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Power brands </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Packaging </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Services and IT </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Trade organizations </span></li>
	<li><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Other that exhibiting </span></li>
</ul>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Live sessions and webinars with inspiring speakers leading the way to change. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">An opportunity to engage with your brand and promote your products at your own pace and involving more associate that you can afford to take to international shows. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">A hosted buyer program with food service and retail power names interested in sourcing products from all over the world. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">A space to meet your next supplier and service provider with live demos and promotional offers to try the services before committing. </span></p>

<p><span style="color:rgba(0, 0, 0, 0.9); font-family:-apple-system,system-ui,blinkmacsystemfont,segoe ui,roboto,helvetica neue,fira sans,ubuntu,oxygen,oxygen sans,cantarell,droid sans,apple color emoji,segoe ui emoji,segoe ui emoji,segoe ui symbol,lucida grande,helvetica,arial,sans-serif; font-size:16px">Exhibitor subscription starting at £99 and yearly personnal subscription for £69 to have access to the community for over 100 countries Gain momentum and expand your network and business</span></p>

<div>&nbsp;</div>', "email" => "info@thefoodeshow.com", "website" => "http://www.thefoodeshow.com/", "facebook" => "https://www.facebook.com/foodeshow/", "twitter" => null, "instagram" => "https://www.instagram.com/foodeshow/", "linkedin" => "https://www.linkedin.com/company/foodeshow/", "youtube" => null, "country_id" => "120"],
            ["id" => "14", "name" => "FOOD MAGAZINE", "streaming_platform" => null, "logo" => null, "background_image" => null, "address" => null, "phone" => null, "description" => null, "email" => "m.bencharfa@foodmagazine.ma", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => null],
            ["id" => "15", "name" => "SERIMAR", "streaming_platform" => null, "logo" => null, "background_image" => null, "address" => null, "phone" => null, "description" => null, "email" => "sales@serimar.co", "website" => null, "facebook" => null, "twitter" => null, "instagram" => null, "linkedin" => null, "youtube" => null, "country_id" => null]
        ];

        foreach ($companyData as $company) {
            // Map JSON fields to Laravel company structure
            $companyFields = [
                'name' => $company['name'],
                'created_by' => 1, // Default created_by value
                'streaming_platform' => $company['streaming_platform'],
                'logo' => $company['logo'] ? 'assets/images/companies/' . $company['logo'] : $company['logo'],
                'background_image' => $company['background_image'],
                'address' => $company['address'],
                'primary_phone' => $company['phone'],
                'secondary_phone' => null, // Default null
                'description' => $company['description'],
                'primary_email' => $company['email'],
                'secondary_email' => null, // Default null
                'website' => $company['website'],
                'facebook' => $company['facebook'],
                'twitter' => $company['twitter'],
                'instagram' => $company['instagram'],
                'linkedin' => $company['linkedin'],
                'youtube' => $company['youtube'],
                'country_id' => $company['country_id'] ? (int)$company['country_id'] : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ];

            // Use updateOrCreate to handle existing records
            Company::query()->create($companyFields);
        }
    }
}
