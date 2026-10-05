<?php

namespace Database\Seeders;

use App\Models\ResearchInstitution;
use Illuminate\Database\Seeder;

class ResearchInstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            // ==========================================
            // UNIVERSITIES
            // ==========================================
            [
                'name' => 'Sokoine University of Agriculture',
                'acronym' => 'SUA',
                'institution_type' => ResearchInstitution::UNIVERSITY,
                'description' => 'A premier public university in Tanzania offering agricultural, veterinary, and allied sciences training and research.',
                'website' => 'https://www.sua.ac.tz',
                'email' => 'sua@sua.ac.tz',
                'phone' => '+255 23 260 3511',
                'region' => 'Morogoro',
                'district' => 'Morogoro Urban',
                'address' => 'Main Campus, Edward Moringe Campus, Morogoro',
                'verified' => true,
            ],
            [
                'name' => 'University of Dar es Salaam',
                'acronym' => 'UDSM',
                'institution_type' => ResearchInstitution::UNIVERSITY,
                'description' => 'The oldest and largest public operational university in Tanzania, leading in multidisciplinary academic research.',
                'website' => 'https://www.udsm.ac.tz',
                'email' => 'vc@udsm.ac.tz',
                'phone' => '+255 22 241 0500',
                'region' => 'Dar es Salaam',
                'district' => 'Ubungo',
                'address' => 'Mlimani Campus, Sam Nujoma Road',
                'verified' => true,
            ],
            [
                'name' => 'The Nelson Mandela African Institution of Science and Technology',
                'acronym' => 'NM-AIST',
                'institution_type' => ResearchInstitution::UNIVERSITY,
                'description' => 'A public institution dedicated to postgraduate training and research in science, engineering, technology, and agriculture.',
                'website' => 'https://www.nm-aist.ac.tz',
                'email' => 'vc@nm-aist.ac.tz',
                'phone' => '+255 27 297 0001',
                'region' => 'Arusha',
                'district' => 'Arumeru',
                'address' => 'Tengeru, Arusha',
                'verified' => true,
            ],
            [
                'name' => 'Ardhi University',
                'acronym' => 'ARU',
                'institution_type' => ResearchInstitution::UNIVERSITY,
                'description' => 'Specialized public university providing education and research in land-use planning, environmental engineering, and spatial sciences.',
                'website' => 'https://www.aru.ac.tz',
                'email' => 'aru@aru.ac.tz',
                'phone' => '+255 22 277 5004',
                'region' => 'Dar es Salaam',
                'district' => 'Kinondoni',
                'address' => 'Observation Hill, University Road',
                'verified' => true,
            ],
            [
                'name' => 'Mbeya University of Science and Technology',
                'acronym' => 'MUST',
                'institution_type' => ResearchInstitution::UNIVERSITY,
                'description' => 'A public university focusing on science, engineering, and technology training for agricultural mechanization and industrialization.',
                'website' => 'https://www.must.ac.tz',
                'email' => 'must@must.ac.tz',
                'phone' => '+255 25 250 2857',
                'region' => 'Mbeya',
                'district' => 'Mbeya City',
                'address' => 'Iyunga, Mbeya',
                'verified' => true,
            ],

            // ==========================================
            // RESEARCH INSTITUTIONS & COMMISSIONS
            // ==========================================
            [
                'name' => 'Tanzania Agricultural Research Institute',
                'acronym' => 'TARI',
                'institution_type' => ResearchInstitution::RESEARCH,
                'description' => 'National body mandated to conduct, regulate, coordinate, and promote agricultural research across Tanzanian agro-ecological zones.',
                'website' => 'https://www.tari.go.tz',
                'email' => 'dg@tari.go.tz',
                'phone' => '+255 26 232 0848',
                'region' => 'Dodoma',
                'district' => 'Dodoma Urban',
                'address' => 'Ministry of Agriculture Building, Dodoma',
                'verified' => true,
            ],
            [
                'name' => 'Tanzania Livestock Research Institute',
                'acronym' => 'TALIRI',
                'institution_type' => ResearchInstitution::RESEARCH,
                'description' => 'Semi-autonomous government body established to plan, coordinate, and conduct livestock research and production technologies.',
                'website' => 'https://www.taliri.go.tz',
                'email' => 'info@taliri.go.tz',
                'phone' => '+255 26 232 0212',
                'region' => 'Dodoma',
                'district' => 'Dodoma Urban',
                'address' => 'Government City, Mtumba, Dodoma',
                'verified' => true,
            ],
            [
                'name' => 'Tropical Pesticides Research Institute',
                'acronym' => 'TPRI',
                'institution_type' => ResearchInstitution::RESEARCH,
                'description' => 'National institution mandated to research tropical agricultural pests, weed management, disease vectors, and pesticide safety.',
                'website' => 'https://www.tpri.go.tz',
                'email' => 'dg@tpri.go.tz',
                'phone' => '+255 27 297 0464',
                'region' => 'Arusha',
                'district' => 'Arumeru',
                'address' => 'Ngaramtoni, P.O. Box 3024, Arusha',
                'verified' => true,
            ],
            [
                'name' => 'Tanzania Forestry Research Institute',
                'acronym' => 'TAFORI',
                'institution_type' => ResearchInstitution::RESEARCH,
                'description' => 'Public institute conducting research in forestry, agroforestry, watershed management, and forest bio-resources.',
                'website' => 'https://www.tafori.or.tz',
                'email' => 'tafori@tafori.or.tz',
                'phone' => '+255 23 293 5174',
                'region' => 'Morogoro',
                'district' => 'Morogoro Urban',
                'address' => 'Kingolwira Area, Morogoro',
                'verified' => true,
            ],
            [
                'name' => 'National Irrigation Commission',
                'acronym' => 'NIRC',
                'institution_type' => ResearchInstitution::GOVERNMENT,
                'description' => 'Autonomous public body responsible for the development, promotion, and management of sustainable irrigation infrastructure.',
                'website' => 'https://www.nirc.go.tz',
                'email' => 'dg@nirc.go.tz',
                'phone' => '+255 26 232 2843',
                'region' => 'Dodoma',
                'district' => 'Dodoma Urban',
                'address' => 'Kilimo I Complex, Dodoma',
                'verified' => true,
            ],
        ];

        foreach ($institutions as $institution) {
            ResearchInstitution::updateOrCreate(
                ['acronym' => $institution['acronym']],
                $institution
            );
        }
    }
}
