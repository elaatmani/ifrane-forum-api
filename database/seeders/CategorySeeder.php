<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $categories_products = [
        //     "Pulses, Grains and Cereals",
        //     "Milk & Dairy",
        //     "Seafood",
        //     "Canned fruits and vegetables",
        //     "Bakery, sweets, deserts",
        //     "Beverages",
        //     "Alcoholic beverages",
        //     "Frozen foods",
        //     "Organic foods",
        //     "Ready-to-Eat Meals",
        //     "Snacks and Confectionery",
        //     "Condiments and Sauces",
        //     "Ingredients",
        //     "Dry food",
        //     "Organic and Natural Products",
        //     "Specialty and Ethnic Foods",
        //     "Health and Wellness Products",
        //     "Baby and Infant Foods",
        //     "Machinery",
        //     "Packaging",
        //     "Fat & oil",
        //     "Fine grocery",
        //     "Halal food",
        //     "Kosher food",
        //     "Supplement",
        //     // Additional Product Categories
        //     "Meat & Poultry",
        //     "Fresh Fruits & Vegetables",
        //     "Spices & Seasonings",
        //     "Processed Foods",
        //     "Nuts & Dried Fruits",
        //     "Herbs & Botanicals",
        //     "Functional Foods",
        //     "Plant-Based Products",
        //     "Gluten-Free Products",
        //     "Dairy Alternatives",
        //     "Meat Alternatives",
        //     "Superfoods",
        //     "Traditional Foods",
        //     "Gourmet Foods",
        //     "Artisanal Products",
        //     "Seasonal Products",
        //     "Imported Foods",
        //     "Local & Regional Products",
        //     "Premium Products",
        //     "Budget-Friendly Products",
        //     "Bulk Products",
        //     "Single-Serve Products",
        //     "Family-Size Products",
        //     "Travel-Size Products",
        //     "Gift Products",
        //     "Holiday Specialties"
        // ];

        // foreach ($categories_products as $category) {
        //     Category::create([
        //         'name' => $category,
        //         'type' => 'product',
        //     ]);
        // }

        // $categories_services = [
        //     "Consulting and Advisory Services",
        //     "Analysis and Quality Control",
        //     "Engineering and Equipment",
        //     "Training and Skills Development",
        //     "Logistics and Transportation Services",
        //     "Marketing and Distribution Services",
        //     "Research and Development Services",
        //     "Waste Management and Sustainability Services",
        //     "Environmental Sustainability",
        //     "Support organization",
        //     // Additional Service Categories
        //     "Food Safety & Compliance",
        //     "Supply Chain Management",
        //     "Technology & Digital Solutions",
        //     "Financial Services",
        //     "Legal & Regulatory Services",
        //     "Insurance Services",
        //     "Human Resources Services",
        //     "Facility Management",
        //     "Equipment Maintenance",
        //     "Cleaning & Sanitation Services",
        //     "Security Services",
        //     "IT & Software Services",
        //     "E-commerce Solutions",
        //     "Mobile App Development",
        //     "Website Development",
        //     "Digital Marketing",
        //     "Social Media Management",
        //     "Content Creation",
        //     "Photography & Videography",
        //     "Translation Services",
        //     "International Trade Services",
        //     "Customs & Import Services",
        //     "Export Services",
        //     "Certification Services",
        //     "Audit Services",
        //     "Testing Services",
        //     "Laboratory Services",
        //     "Research Services",
        //     "Market Research",
        //     "Consumer Insights",
        //     "Product Development",
        //     "Recipe Development",
        //     "Menu Planning",
        //     "Nutritional Analysis",
        //     "Allergen Testing",
        //     "Shelf Life Testing",
        //     "Packaging Design",
        //     "Label Design",
        //     "Brand Development",
        //     "Event Planning",
        //     "Catering Services",
        //     "Food Styling",
        //     "Culinary Training",
        //     "Food Safety Training",
        //     "HACCP Implementation",
        //     "ISO Certification",
        //     "Organic Certification",
        //     "Halal Certification",
        //     "Kosher Certification",
        //     "Fair Trade Certification",
        //     "Sustainability Certification"
        // ];

        // foreach ($categories_services as $category) {
        //     Category::create([
        //         'name' => $category,
        //         'type' => 'service',
        //     ]);
        // }

        // $categories_companies = [
        //     "Food Manufacturers",
        //     "Distributors & Wholesalers",
        //     "Retailers & Supermarkets",
        //     "Food Service Providers",
        //     "Restaurants & Cafes",
        //     "Catering Companies",
        //     "Food Processors",
        //     "Agricultural Producers",
        //     "Fisheries & Aquaculture",
        //     "Livestock Producers",
        //     "Dairy Farms",
        //     "Poultry Farms",
        //     "Organic Farms",
        //     "Vertical Farms",
        //     "Hydroponic Farms",
        //     "Greenhouse Operations",
        //     "Food Importers",
        //     "Food Exporters",
        //     "Trading Companies",
        //     "Cold Storage Facilities",
        //     "Warehousing Companies",
        //     "Transportation Companies",
        //     "Packaging Companies",
        //     "Equipment Manufacturers",
        //     "Ingredient Suppliers",
        //     "Spice Companies",
        //     "Beverage Companies",
        //     "Bakery Companies",
        //     "Confectionery Companies",
        //     "Snack Companies",
        //     "Dairy Companies",
        //     "Meat Processing Companies",
        //     "Seafood Companies",
        //     "Frozen Food Companies",
        //     "Canned Food Companies",
        //     "Organic Food Companies",
        //     "Health Food Companies",
        //     "Supplement Companies",
        //     "Baby Food Companies",
        //     "Pet Food Companies",
        //     "Food Technology Companies",
        //     "Food Safety Companies",
        //     "Quality Control Companies",
        //     "Laboratory Services",
        //     "Certification Bodies",
        //     "Consulting Firms",
        //     "Research Institutions",
        //     "Universities & Colleges",
        //     "Training Centers",
        //     "Industry Associations",
        //     "Trade Organizations",
        //     "Government Agencies",
        //     "Regulatory Bodies",
        //     "Startup Companies",
        //     "Family Businesses",
        //     "Multinational Corporations",
        //     "Cooperative Organizations",
        //     "Franchise Companies",
        //     "Online Food Retailers",
        //     "Meal Kit Companies",
        //     "Food Delivery Services",
        //     "Food Subscription Services",
        //     "Food Waste Management",
        //     "Recycling Companies",
        //     "Sustainable Packaging",
        //     "Biotechnology Companies",
        //     "Alternative Protein Companies",
        //     "Plant-Based Companies",
        //     "Cultured Meat Companies",
        //     "Insect Protein Companies",
        //     "Algae-Based Companies",
        //     "Fermentation Companies",
        //     "Precision Fermentation",
        //     "Cellular Agriculture",
        //     "Food Innovation Hubs",
        //     "Incubators & Accelerators",
        //     "Investment Companies",
        //     "Venture Capital Firms",
        //     "Private Equity Firms"
        // ];

        // foreach ($categories_companies as $category) {
        //     Category::create([
        //         'name' => $category,
        //         'type' => 'company',
        //     ]);
        // }

    $languages = [
        'English' => 'english',
        'French' => 'french',
        'Spanish' => 'spanish',
        'Portuguese' => 'portuguese'
    ];

    foreach ($languages as $name => $code) {
        Category::create([
            'name' => $name,
            'description' => "Language: $name",
            'type' => 'language',
        ]);
    }

    // Conference Types
    $types = [
        'Announcement' => 'announcement',
        'Break' => 'break',
        'Demo' => 'demo',
        'Interview' => 'interview',
        'Keynote' => 'keynote',
        'Panel Discussion' => 'panel',
        'Presentation' => 'presentation',
        'Q&A Session' => 'qa-session',
        'Workshop' => 'workshop',
        'Networking' => 'networking',
        'Roundtable' => 'roundtable',
        'Case Study' => 'case-study'
    ];

    foreach ($types as $name => $code) {
        Category::create([
            'name' => $name,
            'description' => "Conference type: $name",
            'type' => 'type',
        ]);
    }

    // Topics
    $topics = [
        'Automation' => 'automation',
        'Blockchain' => 'blockchain',
        'Branding' => 'branding',
        'Collaboration' => 'collaboration',
        'Consumer' => 'consumer',
        'Digital' => 'digital',
        'Distribution' => 'distribution',
        'E-commerce' => 'e-commerce',
        'Economics' => 'economics',
        'Finance' => 'finance',
        'Industry 4.0' => 'industry-4-0',
        'Logistics' => 'logistics',
        'Marketing' => 'marketing',
        'Markets' => 'markets',
        'Packaging' => 'packaging',
        'Regulations' => 'regulations',
        'Services' => 'services',
        'Technology' => 'technology',
        'Trends' => 'trends',
        'Human Resources' => 'human-resources',
        'Innovation' => 'innovation',
        'Strategy' => 'strategy',
        'Supply Chain' => 'supply-chain',
        'Sustainability' => 'sustainability',
        'Artificial Intelligence' => 'artificial-intelligence',
        'Machine Learning' => 'machine-learning',
        'Data Analytics' => 'data-analytics',
        'Cybersecurity' => 'cybersecurity',
        'Cloud Computing' => 'cloud-computing',
        'Internet of Things' => 'internet-of-things'
    ];

    foreach ($topics as $name => $code) {
        Category::create([
            'name' => $name,
            'description' => "Conference topic: $name",
            'type' => 'topic',
        ]);
    }
    }
}
