<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin 1
        $admin1 = User::updateOrCreate(
            ['email' => 'kjonasdevpro@gmail.com',
                'name' => 'SO Kevin Jonas',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        Profile::create([
            'user_id' => $admin1->id,
            'whatsapp_number' => '+237600000001',
            'country' => 'Burkina Faso',
            'city' => 'Ouagadougou',
            'status' => 'validated',
        ]);

        Wallet::create([
            'user_id' => $admin1->id,
            'balance' => 0,
        ]);

        // Admin 2
        $admin2 = User::create([
            'name' => 'KONE Kader',
            'email' => 'koneakader1219@gmail.com',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Profile::create([
            'user_id' => $admin2->id,
            'whatsapp_number' => '+22674159768',
            'country' => 'Burkina Faso',
            'city' => 'Ouagadougou',
            'status' => 'validated',
        ]);

        Wallet::create([
            'user_id' => $admin2->id,
            'balance' => 0,
        ]);
        // Moderator
        $moderator = User::create([
            'name' => 'KOUATT',
            'email' => 'autrea218@gmail.com',
            'role' => 'moderator',
            'email_verified_at' => now(),
        ]);

        Profile::create([
            'user_id' => $moderator->id,
            'whatsapp_number' => '+22554654864',
            'country' => 'Cote d\'Ivoire',
            'city' => 'Abidjan',
            'status' => 'validated',
        ]);

        Wallet::create([
            'user_id' => $moderator->id,
            'balance' => 0,
        ]);

        $this->command->info('✅ Production users created successfully!');
        $this->command->info('');
        $this->command->info('📧 SO Kevin Jonas: kjonasdevpro@gmail.com ');
        $this->command->info('📧 KONE Kader: koneakader1219@gmail.com ');
        $this->command->info('📧 Moderator: moderator@tourno.com ');
        $this->command->info('');
    }
}
