<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123')
        ]);
        $admin->assignRole('admin');


        $user = User::create([
            'name' => 'attendee attendee',
            'email' => 'attendee@gmail.com',
            'password' => bcrypt('attendee123')
        ]);
        $user->assignRole('attendee');

        $user->profile()->create();

        $user = User::create([
            'name' => 'Exhibitor Exhibitor',
            'email' => 'exhibitor@gmail.com',
            'password' => bcrypt('exhibitor123')
        ]);
        $user->assignRole('exhibitor');

        $user->profile()->create();

        $user = User::create([
            'name' => 'Buyer Buyer',
            'email' => 'buyer@gmail.com',
            'password' => bcrypt('buyer123')
        ]);
        $user->assignRole('buyer');

        $user->profile()->create();

        $user = User::create([
            'name' => 'Sponsor Sponsor',
            'email' => 'sponsor@gmail.com',
            'password' => bcrypt('sponsor123')
        ]);
        $user->assignRole('sponsor');

        $user->profile()->create();

        $user = User::create([
            'name' => 'Speaker Speaker',
            'email' => 'speaker@gmail.com',
            'password' => bcrypt('speaker123')
        ]);
        $user->assignRole('speaker');

        $user->profile()->create();

        $this->existingUsers();

    }


    public function existingUsers(): void
    {
        // JSON data from the provided file
        $userData = [
            ["id" => "4", "email" => "Vib@thefoodeshow.com", "username" => "adon cron", "firstname" => "adon", "lastname" => "cron", "completed" => "0", "roles" => '["ROLE_ADMIN"]', "password" => '$2y$13$nQx0KRGjrH1RXWxXFR3V1eEPs6Rk.rYGC4TlFGsRCoCeMY6BNMPYu', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "26", "email" => "hh@greativaconsulting.com", "username" => "Halima EL HADDAD", "firstname" => "Halima", "lastname" => "EL HADDAD", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$s0MGBb0q26n6iveUVecfluWsjbLkzPAGYJL7Xf/Xh524Mk4jSR.1C', "enable" => "1", "deletedAt" => null, "company_id" => "13", "contact_id" => "21"],
            ["id" => "27", "email" => "yl@greativaconsulting.com", "username" => "yousra laasri", "firstname" => "Yousra", "lastname" => "LAASRI", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$vTz.SRhf1V0Isl3jZF0N1uttDPJHnAdhTo78zpPnRN.c8BOOwVpXi', "enable" => "1", "deletedAt" => null, "company_id" => "2", "contact_id" => "23"],
            ["id" => "28", "email" => "hg@greativaconsulting.com", "username" => "heuda guessous", "firstname" => "Heuda", "lastname" => "GUESSOUS", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$Aae.PnY9KnC0Cis76Ad66eLZu4/WBWecxJuPHDvf/bFQ9f/UacmJa', "enable" => "1", "deletedAt" => null, "company_id" => "2", "contact_id" => "24"],
            ["id" => "29", "email" => "at@greativaconsulting.com", "username" => "asmaa tadrari", "firstname" => "Asmaa", "lastname" => "TADRARI", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$r3kDwplw2g1mL5dnUCQQ7eTFTG77dtE48kc.DcImlPx2IiCbPOb1.', "enable" => "1", "deletedAt" => null, "company_id" => "2", "contact_id" => "22"],
            ["id" => "30", "email" => "kacem.n@tradesocial.tech", "username" => "kacem nasri", "firstname" => "Kacem", "lastname" => "NASRI", "completed" => "0", "roles" => '{"1": "ROLE_EXHIBITOR"}', "password" => '$2y$13$DXeyXMIaddS8IXmDaj4C1O1L8rhyHrQzGCnEAkSM/mfVsn1K3NDIy', "enable" => "1", "deletedAt" => null, "company_id" => "12", "contact_id" => null],
            ["id" => "31", "email" => "omedvedevva@gmail.com", "username" => "olga odemchouk medvedeva", "firstname" => "Olga", "lastname" => "Odemchouk Medvedeva", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$ws9.z/AOXRtEP/JyeRavBuURNy/7ezaYD6g7sZY.hxn.aGok3//cG', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "32", "email" => "khalid.machchate@ieee.org", "username" => "khalid machchate", "firstname" => "Khalid", "lastname" => "Machchate", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$fscX.a05v0ShaPrlMToRHeSqxtl3DUMHnbXCFHmTIqpijFC5gIhfW', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "33", "email" => "jevtics@gmail.com", "username" => "sasa jevtic", "firstname" => "Sasa", "lastname" => "Jevtic", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$4HQPKthJtk1EnID9CuxjieLHHwUp6p1obHU0mlSEzKaJLsK3ad2ou', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "34", "email" => "hanane.oumina@800ia.com", "username" => "oumnia hanane ezzahra", "firstname" => "Oumnia", "lastname" => "Hanane Ezzahra", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$VjU4mIUNyj47EBf7f8A7deMkYNSEZGm9c5WDk9i.4/QkChwR3RpTu', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "35", "email" => "bryan@xrglobal.io", "username" => "bryan crosswhite", "firstname" => "Bryan", "lastname" => "Crosswhite", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$K6ErdU4NI4aHBh4fV1aBZebn1K/OdQID54sy47WccWyzYyk.r/l4C', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "36", "email" => "andrea.magelli@futurefoodinstitute.org", "username" => "andrea magelli", "firstname" => "Andrea", "lastname" => "Magelli", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$JRetrzOTDf064NFk3.P9XumpfF9qoQPWNds4vXKwp/1dwHH5ycZhe', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "37", "email" => "d.zayed@fwdegypt.com", "username" => "dahlia zayed", "firstname" => "Dahlia", "lastname" => "Zayed", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$Jg9F.0eblNg9upU.N0Yb6eLRjorJyvPlaFfIilpulUgY1lypfYz.O', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "38", "email" => "aconnolly@agritechcapital.com", "username" => "aidan connolly", "firstname" => "Aidan", "lastname" => "Connolly", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$FxwiKwA5KtTPacCpn/vIZ.h2.KPNH9uecX8KyQauUnKQYhP1kq5lm', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "39", "email" => "eugene@sophiesbionutrients.com", "username" => "eugene wang", "firstname" => "Eugene", "lastname" => "Wang", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$Jr.jzw2vxsfgGTG06jVAE.SQ4nzktF7E4MmfZujjlAphX8BLdshnG', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "40", "email" => "ayoub@pixellabs.tech", "username" => "ayoub a", "firstname" => "Ayoub", "lastname" => "A", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$7rc7yNnpRJ9QIp.u8OHaCumCbYKBYJO2fn/XN3YbuQqK4uaYTwJOG', "enable" => "1", "deletedAt" => null, "company_id" => "2", "contact_id" => "25"],
            ["id" => "41", "email" => "msdxb91@gmail.com", "username" => "mustapha soukrati", "firstname" => "Mustapha", "lastname" => "Soukrati", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$zSGlxxBJqpcpcflbg7ZA3urei2PzS1m90n4fE2Y2/ID65heBKW3uS', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "42", "email" => "tayseer.ggtc@gmail.com", "username" => "alaziz tayseer", "firstname" => "Alaziz", "lastname" => "Tayseer", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$Oii0sVKWZKknGtcCAj36ZeBTCRip0bQF70BXlQVYUDMd7HdrXxV0K', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "43", "email" => "noraitco@gmail.com", "username" => "mohammed taisir", "firstname" => "MOHAMMED", "lastname" => "TAISIR", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$7njAdp1m0PbKGGpVikpmq.Db.dG3slwr9ORPE6E.3J.jvkqtUKVtW', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "44", "email" => "bfedina@bsfinternational.fr", "username" => "bouchra fedina", "firstname" => "Bouchra", "lastname" => "FEDINA", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$rX/siwWioMORxaX5sHKxW.ghsw0XqGBda59Q2nTSOpwSnYMTDyRyC', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "45", "email" => "a.derouiche@labelvie.ma", "username" => "aicha derouiche", "firstname" => "Aicha", "lastname" => "Derouiche", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$nEMlFb3V/YEoMl6N5twQ/.jbm8XtY7U6YwK3N6lTZXuFFzFrwHy8C', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "46", "email" => "bruno.desson@sobreval.com", "username" => "bruno desson", "firstname" => "Bruno", "lastname" => "Desson", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$z2hebfIvTKQ8pb4/qEKOzuc/v5P2MNobtlxMiXZvoweusjRht/sBe', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "48", "email" => "sales@jasmi-impex.com", "username" => "heuda guessous sidqui", "firstname" => "Heuda", "lastname" => "GUESSOUS SIDQUI", "completed" => "0", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$RlgedZo9FryUJ7edRvC8X.JW5vVY5xc/mheR1h6RRM82z5n63GMQ2', "enable" => "1", "deletedAt" => null, "company_id" => "4", "contact_id" => null],
            ["id" => "49", "email" => "m.bencharfa@foodmagazine.ma", "username" => "mostapha bencharfa", "firstname" => "Mostapha", "lastname" => "BENCHARFA", "completed" => "0", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$d5JWwmzD2rZ/Wq2TfO2JdelDZkjeK9PqzkIC23IhOqTGLfxEUCvUm', "enable" => "1", "deletedAt" => null, "company_id" => "14", "contact_id" => null],
            ["id" => "50", "email" => "sanae@nuelink.com", "username" => "sanae a", "firstname" => "Sanae", "lastname" => "A", "completed" => "0", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$R7Hx5bFW1XUYVRWWnWuwdeAFdF1HyJKhGnY/5tktJ51oYcvw5W416', "enable" => "1", "deletedAt" => null, "company_id" => "8", "contact_id" => null],
            ["id" => "51", "email" => "bilal@nuelink.com", "username" => "bilal ararou", "firstname" => "Bilal", "lastname" => "Ararou", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$8HOKlidnX6ffQr0iWs734.jnfJ6qR7R6dIuUPo5iOprj2EM8IadBC', "enable" => "1", "deletedAt" => null, "company_id" => "8", "contact_id" => "26"],
            ["id" => "52", "email" => "t.bouchehboun@labelvie.ma", "username" => "tarek bouchehboune", "firstname" => "Tarek", "lastname" => "BOUCHEHBOUNE", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$3hdsHcY7ToZ2rKDj5Qonuuj3trn9ijUwjk28jWx./7s9PEOxbnG5q', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "53", "email" => "elkolaly@yahoo.com", "username" => "mahmoud el kolaly", "firstname" => "Mahmoud", "lastname" => "EL KOLALY", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$GLQre4Z8mp.AfxmIQCHS3OG/oRrPb6rkg/zf4MnfuJqnFmMHMB3Ri', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "54", "email" => "salim@monarkit.net", "username" => "salim elbouanani", "firstname" => "Salim", "lastname" => "ELBOUANANI", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$UXQCV/a3b4xEILSNELHeOeqjl36nt084wiVG1Q5NJeza3jV.YbjpO', "enable" => "1", "deletedAt" => null, "company_id" => "7", "contact_id" => "31"],
            ["id" => "55", "email" => "yousra.dakar@gmail.com", "username" => "yousra test", "firstname" => "Yousra", "lastname" => "Test", "completed" => "0", "roles" => '["ROLE_SPEAKER"]', "password" => '$2y$13$WqMMgCnzIwb0jwesMh51nO9xU9sD83HlNiNTS7SQ2RGv7Z9S6CYUG', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "56", "email" => "sales@serimar.co", "username" => "rim arhda", "firstname" => "Rim", "lastname" => "ARHDA", "completed" => "0", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$hiFa/UEYULU6aVi/8ZeztecE9q5KtbZCgB.YU/H4CYkXz5fe5/jW.', "enable" => "1", "deletedAt" => null, "company_id" => "15", "contact_id" => null],
            ["id" => "57", "email" => "mehdi@serimar.co", "username" => "mehdi kabli", "firstname" => "Mehdi", "lastname" => "KABLI", "completed" => "0", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$psD7i1VSAlRuPTnhLzwqku1g4LRyknw12iF383thLxJcphYI/K/sK', "enable" => "1", "deletedAt" => null, "company_id" => "15", "contact_id" => null],
            ["id" => "58", "email" => "cadee717@gmail.com", "username" => "sameh madi", "firstname" => "Sameh", "lastname" => "MADI", "completed" => "0", "roles" => '["ROLE_BUYER"]', "password" => '$2y$13$ZdUBwLby7Z8VkIs9Ihg5bulfw2EWTeI/u33hrJkyeHeNe6BG0CMqi', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "59", "email" => "radi@iqcc.net", "username" => "leila radi", "firstname" => "Leila", "lastname" => "RADI", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$LTXNUf0blJOIp8C434ZEROvZq7SKfmfZS.ymizTDPVYagt839CAue', "enable" => "1", "deletedAt" => null, "company_id" => "6", "contact_id" => "27"],
            ["id" => "60", "email" => "mouadhallaffou@gmail.com", "username" => "mouad hallaffou", "firstname" => "Mouad", "lastname" => "HALLAFFOU", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$de8mvHDbZm8WbupD9mRphuGYd0jR8.tmA7n8Ij556LZjQm5eTx9f2', "enable" => "1", "deletedAt" => null, "company_id" => "13", "contact_id" => "28"],
            ["id" => "61", "email" => "reidamohammed@gmail.com", "username" => "Mohammed REIDA", "firstname" => "Mohammed", "lastname" => "REIDA", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$rEM8y8wehVkLwz4xeRW6SOkJ47iK6YKjz1sqienomBMRWRk6NtmLC', "enable" => "1", "deletedAt" => null, "company_id" => "13", "contact_id" => "30"],
            ["id" => "62", "email" => "salmaelallali11@gmail.com", "username" => "salma el allali", "firstname" => "Salma", "lastname" => "EL ALLALI", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$22WiRiPWxtY8nvCLWzLLpOKQki7m2myaDzJkYDzT9e282EUqJNbwq', "enable" => "1", "deletedAt" => null, "company_id" => "13", "contact_id" => "29"],
            ["id" => "63", "email" => "melhafiz@gmail.com", "username" => "mohamed elhafiz omer", "firstname" => "Mohamed Elhafiz", "lastname" => "OMER", "completed" => "0", "roles" => '["ROLE_ATTENDEE"]', "password" => '$2y$13$g.a97GQq15DHaX4vS0UZ/O3QOVDzgjN3967/zRru6YNDu2w0QYc6a', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "64", "email" => "s.alkhiyami@gmail.com", "username" => "salah alkhiyami", "firstname" => "Salah", "lastname" => "ALKHIYAMI", "completed" => "0", "roles" => '["ROLE_ATTENDEE"]', "password" => '$2y$13$N6kJB8HNpxw1Zf646elheOnyWH5KwvI7cJ/5wtDFTO2cxzUe.VbLO', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "65", "email" => "omar.chahine@fambrashalal.com.br", "username" => "omar chahine", "firstname" => "Omar", "lastname" => "CHAHINE", "completed" => "0", "roles" => '["ROLE_ATTENDEE"]', "password" => '$2y$13$niryvUNgSwaR/6reHnjl0eZSuCxwwJdHmpcO15KNxWym9CZ3xvgh2', "enable" => "1", "deletedAt" => null, "company_id" => null, "contact_id" => null],
            ["id" => "66", "email" => "showcase@thefoodeshow.com", "username" => "zineb el idrissi", "firstname" => "Zineb", "lastname" => "EL IDRISSI", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$Uk648hHcwIFP5ckX0RJ6a.PMb89azZeymWHjAcWCQdum8UgRn7DAK', "enable" => "1", "deletedAt" => null, "company_id" => "13", "contact_id" => "32"],
            ["id" => "67", "email" => "yassine@thefoodeshow.com", "username" => "yassine el aatmani", "firstname" => "Yassine", "lastname" => "EL AATMANI", "completed" => "1", "roles" => '["ROLE_EXHIBITOR"]', "password" => '$2y$13$gejVnZKnXVhxjM4N/yszKOdsKQWkSiUlhKIVPknppMTBcps6dAAZC', "enable" => "1", "deletedAt" => null, "company_id" => "13", "contact_id" => "33"]
        ];

        // Contact data from contact.json
        $contactData = [
            ["id" => "21", "phone" => null, "site_web" => "thefoodeshow.com", "adresse" => null, "post" => "Creative Director", "company_name" => "The FoodEshow", "city" => "marrakech", "avatar_path" => "hh@greativaconsulting.com/67b30ca47fef7.png", "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => "https://www.linkedin.com/in/halima-el-haddad/", "description" => null, "country_id" => "317"],
            ["id" => "22", "phone" => "0611559369", "site_web" => "www.greativa.ma", "adresse" => "Ighli 7 N°195 mhamid", "post" => "training manager", "company_name" => "Greativa Consulting group", "city" => "Marrakech", "avatar_path" => "at@greativaconsulting.com/67b30c929eedd.jpg", "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => "https://www.linkedin.com/in/asmaa-tadrari-8b5748179/", "description" => null, "country_id" => "317"],
            ["id" => "23", "phone" => null, "site_web" => null, "adresse" => null, "post" => "Responsable des opérations", "company_name" => "Greativa Consulting Group", "city" => "Dakar", "avatar_path" => "yl@greativaconsulting.com/67b30ee355564.png", "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => null, "description" => null, "country_id" => "352"],
            ["id" => "24", "phone" => null, "site_web" => null, "adresse" => null, "post" => "CEO", "company_name" => "Jasmi impex", "city" => "Marrakech", "avatar_path" => "hg@greativaconsulting.com/67bc4f5d38db8.jpg", "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => null, "description" => null, "country_id" => "317"],
            ["id" => "25", "phone" => null, "site_web" => null, "adresse" => null, "post" => "CEO", "company_name" => "Pixellabs Technolgies", "city" => null, "avatar_path" => null, "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => null, "description" => null, "country_id" => "317"],
            ["id" => "26", "phone" => null, "site_web" => "https://nuelink.com", "adresse" => null, "post" => "CEO", "company_name" => "Nuelink", "city" => "Tetouan", "avatar_path" => "bilal@nuelink.com/67effe8609df0.png", "facebook_link" => null, "instagram_link" => "https://instagram.com/bilalararou/", "twitter_link" => "https://x.com/bilalararou/", "linkedin_link" => "https://www.linkedin.com/in/bilalararou/", "description" => '<p>TL;DR: I&rsquo;m Bilal, a product designer turned entrepreneur from Morocco. I founded Kreatinc, a web design agency, and launched Nuelink, a social media tool used by 20,000+ globally. My mission? To inspire success from Morocco and build the country&rsquo;s first unicorn</p>', "country_id" => "317"],
            ["id" => "27", "phone" => "8603181797", "site_web" => null, "adresse" => "PO Box 3719", "post" => "Director", "company_name" => "International Quality Control Corp.", "city" => "Mount Vernon, NY", "avatar_path" => null, "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => null, "description" => null, "country_id" => "385"],
            ["id" => "28", "phone" => "0678634285", "site_web" => "https://mouadhallaffou.vercel.app/", "adresse" => "Tit Mellil, Casablanca", "post" => "Développeur Web Full Stack", "company_name" => "YouCode UM6P", "city" => "Casablanca", "avatar_path" => "mouadhallaffou@gmail.com/686503c62b522.jpg", "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => "https://www.linkedin.com/in/hallaffou-mouad/", "description" => null, "country_id" => "317"],
            ["id" => "29", "phone" => null, "site_web" => "https://salma-elallali-dev.vercel.app/", "adresse" => null, "post" => "Full Stack Developer", "company_name" => "The FoodEshow", "city" => "Marrakech", "avatar_path" => null, "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => null, "description" => null, "country_id" => "317"],
            ["id" => "30", "phone" => "0688825135", "site_web" => "https://mohammed-reida-portfolio.vercel.app/", "adresse" => null, "post" => "Développeur Web Full Stack", "company_name" => "thefoodeshow", "city" => "Berrechid", "avatar_path" => null, "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => "www.linkedin.com/in/mohammed-reida", "description" => null, "country_id" => "317"],
            ["id" => "31", "phone" => "+2126203234", "site_web" => "https://monarkit.net/", "adresse" => "Bergis Business Center, 5th floor Office 18, Avenue Safi - Marrakesh", "post" => "CEO", "company_name" => "MONARKIT", "city" => "Marrakech", "avatar_path" => "salim@monarkit.net/6824b354c871a.png", "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => "https://www.linkedin.com/in/salim-el-bouanani/", "description" => null, "country_id" => "317"],
            ["id" => "32", "phone" => "+212691247595", "site_web" => null, "adresse" => null, "post" => "intern", "company_name" => "Greativa Consulting Group", "city" => "Marrakech", "avatar_path" => null, "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => null, "description" => null, "country_id" => "317"],
            ["id" => "33", "phone" => null, "site_web" => null, "adresse" => null, "post" => "Full Stack Web Developer", "company_name" => "Greativa Consulting Group", "city" => null, "avatar_path" => null, "facebook_link" => null, "instagram_link" => null, "twitter_link" => null, "linkedin_link" => null, "description" => null, "country_id" => "317"]
        ];

        // Create contact mapping by ID for easy lookup
        $contactMapping = [];
        foreach ($contactData as $contact) {
            $contactMapping[$contact['id']] = $contact;
        }

        // Role mapping from JSON format to Laravel format
        $roleMapping = [
            'ROLE_ADMIN' => 'admin',
            'ROLE_EXHIBITOR' => 'exhibitor',
            'ROLE_SPEAKER' => 'speaker',
            'ROLE_BUYER' => 'buyer',
            'ROLE_ATTENDEE' => 'attendee'
        ];

        foreach ($userData as $userEntry) {
            // Parse roles - handle both array format and object format
            $roles = $userEntry['roles'];
            $parsedRole = null;
            
            if (strpos($roles, '{') === 0) {
                // Handle object format like {"1": "ROLE_EXHIBITOR"}
                $rolesArray = json_decode($roles, true);
                if (is_array($rolesArray)) {
                    $parsedRole = array_values($rolesArray)[0];
                }
            } else {
                // Handle array format like ["ROLE_ADMIN"]
                $rolesArray = json_decode($roles, true);
                if (is_array($rolesArray) && !empty($rolesArray)) {
                    $parsedRole = $rolesArray[0];
                }
            }

            // Map to Laravel role format
            $laravelRole = $roleMapping[$parsedRole] ?? 'attendee';

            // Create user
            $userId = DB::table('users')->insertGetId([
                'name' => $userEntry['firstname'] . ' ' . $userEntry['lastname'],
                'email' => $userEntry['email'],
                'password' => $userEntry['password'], // Keep original password hash
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Get the user instance for role assignment
            $user = User::find($userId);

            // Assign role
            $user->assignRole($laravelRole);

            // Assign contact data if contact_id exists
            $contactId = $userEntry['contact_id'] ?? null;
            if ($contactId && is_string($contactId) && array_key_exists($contactId, $contactMapping)) {
                $contactData = $contactMapping[$contactId];
                
                // Assign contact data directly to user object
                $user->profile_image = $contactData['avatar_path'] ? 'assets/images/users/' . $contactData['avatar_path'] : $contactData['avatar_path'];
                $user->save();
            }

            $user->profile()->create();
        }
    }
}
