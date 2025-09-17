<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@fortunehome.com',
                'phone' => '+1 (555) 123-4567',
                'license_number' => 'RE-2023-001',
                'bio' => 'With over 8 years of experience in luxury real estate, Sarah specializes in high-end properties in Manhattan and Brooklyn. She has consistently been a top performer, helping clients find their dream homes.',
                'office_address' => '123 Real Estate Plaza, New York, NY 10001',
                'social_media' => [
                    'linkedin' => 'https://linkedin.com/in/sarah-johnson-realtor',
                    'instagram' => 'https://instagram.com/sarahjohnsonrealty',
                    'facebook' => 'https://facebook.com/sarahjohnsonrealtor'
                ],
                'commission_rate' => 2.5,
                'properties_sold' => 127,
                'experience_years' => 8,
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@fortunehome.com',
                'phone' => '+1 (555) 234-5678',
                'license_number' => 'RE-2023-002',
                'bio' => 'Michael is a dedicated real estate professional specializing in first-time homebuyers and investment properties. His analytical approach and market knowledge help clients make informed decisions.',
                'office_address' => '456 Property Avenue, Los Angeles, CA 90210',
                'social_media' => [
                    'linkedin' => 'https://linkedin.com/in/michael-chen-realty',
                    'twitter' => 'https://twitter.com/mchenrealestate'
                ],
                'commission_rate' => 2.0,
                'properties_sold' => 89,
                'experience_years' => 5,
            ],
            [
                'name' => 'Emily Rodriguez',
                'email' => 'emily.rodriguez@fortunehome.com',
                'phone' => '+1 (555) 345-6789',
                'license_number' => 'RE-2023-003',
                'bio' => 'Emily brings 12 years of experience in commercial and residential real estate. She is known for her negotiation skills and has helped clients save millions in property transactions.',
                'office_address' => '789 Commerce Street, Chicago, IL 60601',
                'social_media' => [
                    'linkedin' => 'https://linkedin.com/in/emily-rodriguez-realtor',
                    'instagram' => 'https://instagram.com/emilyrodriguezrealty'
                ],
                'commission_rate' => 3.0,
                'properties_sold' => 203,
                'experience_years' => 12,
            ],
            [
                'name' => 'David Thompson',
                'email' => 'david.thompson@fortunehome.com',
                'phone' => '+1 (555) 456-7890',
                'license_number' => 'RE-2023-004',
                'bio' => 'David specializes in luxury waterfront properties and vacation homes. His extensive network and personalized service ensure clients find exceptional properties that exceed their expectations.',
                'office_address' => '321 Ocean Drive, Miami, FL 33139',
                'social_media' => [
                    'linkedin' => 'https://linkedin.com/in/david-thompson-luxury-realty',
                    'facebook' => 'https://facebook.com/davidthompsonrealty',
                    'youtube' => 'https://youtube.com/davidthompsonproperties'
                ],
                'commission_rate' => 3.5,
                'properties_sold' => 156,
                'experience_years' => 10,
            ],
            [
                'name' => 'Jennifer Williams',
                'email' => 'jennifer.williams@fortunehome.com',
                'phone' => '+1 (555) 567-8901',
                'license_number' => 'RE-2023-005',
                'bio' => 'Jennifer is passionate about helping families find their perfect home. With expertise in suburban properties and school districts, she makes the home buying process smooth and stress-free.',
                'office_address' => '654 Suburban Lane, Houston, TX 77001',
                'social_media' => [
                    'facebook' => 'https://facebook.com/jenniferwilliamsrealty',
                    'instagram' => 'https://instagram.com/jenwilliamshomes'
                ],
                'commission_rate' => 2.25,
                'properties_sold' => 98,
                'experience_years' => 6,
            ],
            [
                'name' => 'Robert Kim',
                'email' => 'robert.kim@fortunehome.com',
                'phone' => '+1 (555) 678-9012',
                'license_number' => 'RE-2023-006',
                'bio' => 'Robert focuses on tech professionals and startup employees, understanding their unique needs for flexible living arrangements and modern amenities in urban environments.',
                'office_address' => '987 Tech Hub Boulevard, San Francisco, CA 94105',
                'social_media' => [
                    'linkedin' => 'https://linkedin.com/in/robert-kim-tech-realty',
                    'twitter' => 'https://twitter.com/robertkimrealty'
                ],
                'commission_rate' => 2.75,
                'properties_sold' => 74,
                'experience_years' => 4,
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@fortunehome.com',
                'phone' => '+1 (555) 789-0123',
                'license_number' => 'RE-2023-007',
                'bio' => 'Lisa is a veteran agent with 15 years of experience across all property types. She is known for her market insights and ability to spot emerging neighborhood trends before they become mainstream.',
                'office_address' => '147 Market Street, Seattle, WA 98101',
                'social_media' => [
                    'linkedin' => 'https://linkedin.com/in/lisa-anderson-realty-expert',
                    'facebook' => 'https://facebook.com/lisaandersonrealty',
                    'instagram' => 'https://instagram.com/lisaandersonhomes'
                ],
                'commission_rate' => 2.8,
                'properties_sold' => 287,
                'experience_years' => 15,
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@fortunehome.com',
                'phone' => '+1 (555) 890-1234',
                'license_number' => 'RE-2023-008',
                'bio' => 'James specializes in historic properties and restoration projects. His background in architecture helps clients envision the potential in unique and character-rich properties.',
                'office_address' => '258 Historic District, Boston, MA 02101',
                'social_media' => [
                    'linkedin' => 'https://linkedin.com/in/james-wilson-historic-realty',
                    'instagram' => 'https://instagram.com/jameswilsonhistorichomes'
                ],
                'commission_rate' => 3.25,
                'properties_sold' => 112,
                'experience_years' => 9,
            ],
        ];

        foreach ($agents as $agentData) {
            Agent::firstOrCreate(
                ['email' => $agentData['email']],
                [
                    'name' => $agentData['name'],
                    'email' => $agentData['email'],
                    'phone' => $agentData['phone'],
                    'license_number' => $agentData['license_number'],
                    'bio' => $agentData['bio'],
                    'office_address' => $agentData['office_address'],
                    'social_media' => $agentData['social_media'],
                    'is_active' => true,
                    'commission_rate' => $agentData['commission_rate'],
                    'properties_sold' => $agentData['properties_sold'],
                    'experience_years' => $agentData['experience_years'],
                ]
            );
        }
    }
}