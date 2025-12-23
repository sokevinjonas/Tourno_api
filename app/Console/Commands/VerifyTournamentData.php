<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Profile;
use App\Models\GameAccount;
use App\Models\Wallet;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use Illuminate\Console\Command;

class VerifyTournamentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify tournament system data and display statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verifying Tournament System Data...');
        $this->newLine();

        // Users Statistics
        $this->displayUsersStats();
        $this->newLine();

        // Profiles Statistics
        $this->displayProfilesStats();
        $this->newLine();

        // Wallets Statistics
        $this->displayWalletsStats();
        $this->newLine();

        // Game Accounts Statistics
        $this->displayGameAccountsStats();
        $this->newLine();

        // Tournaments Statistics
        $this->displayTournamentsStats();
        $this->newLine();

        // Registrations Statistics
        $this->displayRegistrationsStats();

        $this->newLine();
        $this->info('✅ Verification Complete!');
    }

    private function displayUsersStats(): void
    {
        $this->info('👥 USERS STATISTICS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $admins = User::where('role', 'admin')->count();
        $moderators = User::where('role', 'moderator')->count();
        $organizers = User::where('role', 'organizer')->count();
        $players = User::where('role', 'player')->count();
        $total = User::count();

        $this->table(
            ['Role', 'Count'],
            [
                ['Admin', $admins],
                ['Moderators', $moderators],
                ['Organizers', $organizers],
                ['Players', $players],
                ['TOTAL', $total],
            ]
        );
    }

    private function displayProfilesStats(): void
    {
        $this->info('📋 PROFILES STATISTICS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $validated = Profile::where('status', 'validated')->count();
        $pending = Profile::where('status', 'pending')->count();
        $rejected = Profile::where('status', 'rejected')->count();
        $total = Profile::count();

        $this->table(
            ['Status', 'Count'],
            [
                ['Validated', $validated],
                ['Pending', $pending],
                ['Rejected', $rejected],
                ['TOTAL', $total],
            ]
        );
    }

    private function displayWalletsStats(): void
    {
        $this->info('💰 WALLETS STATISTICS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $totalWallets = Wallet::count();
        $totalBalance = Wallet::sum('balance');
        $avgBalance = Wallet::avg('balance');
        $maxBalance = Wallet::max('balance');
        $minBalance = Wallet::min('balance');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Wallets', $totalWallets],
                ['Total Balance', number_format($totalBalance, 2) . ' MLM'],
                ['Average Balance', number_format($avgBalance, 2) . ' MLM'],
                ['Max Balance', number_format($maxBalance, 2) . ' MLM'],
                ['Min Balance', number_format($minBalance, 2) . ' MLM'],
            ]
        );
    }

    private function displayGameAccountsStats(): void
    {
        $this->info('🎮 GAME ACCOUNTS STATISTICS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $efootball = GameAccount::where('game', 'efootball')->count();
        $fcMobile = GameAccount::where('game', 'fc_mobile')->count();
        $dreamLeague = GameAccount::where('game', 'dream_league_soccer')->count();
        $total = GameAccount::count();

        $this->table(
            ['Game', 'Accounts'],
            [
                ['eFootball', $efootball],
                ['FC Mobile', $fcMobile],
                ['Dream League Soccer', $dreamLeague],
                ['TOTAL', $total],
            ]
        );
    }

    private function displayTournamentsStats(): void
    {
        $this->info('🏆 TOURNAMENTS STATISTICS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $tournaments = Tournament::with('registrations')->get();

        $data = [];
        foreach ($tournaments as $tournament) {
            $registrationsCount = $tournament->registrations()->count();
            $spotsLeft = $tournament->max_participants - $registrationsCount;

            $data[] = [
                $tournament->name,
                $tournament->game,
                "{$registrationsCount}/{$tournament->max_participants}",
                $spotsLeft > 0 ? $spotsLeft : 'FULL',
                $tournament->entry_fee . ' MLM',
                $tournament->start_date,
            ];
        }

        $this->table(
            ['Tournament', 'Game', 'Players', 'Spots Left', 'Entry Fee', 'Start Date'],
            $data
        );
    }

    private function displayRegistrationsStats(): void
    {
        $this->info('📝 REGISTRATIONS STATISTICS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $registered = TournamentRegistration::where('status', 'registered')->count();
        $withdrawn = TournamentRegistration::where('status', 'withdrawn')->count();
        $disqualified = TournamentRegistration::where('status', 'disqualified')->count();
        $total = TournamentRegistration::count();

        $this->table(
            ['Status', 'Count'],
            [
                ['Registered', $registered],
                ['Withdrawn', $withdrawn],
                ['Disqualified', $disqualified],
                ['TOTAL', $total],
            ]
        );
    }
}
