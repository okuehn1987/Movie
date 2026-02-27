<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\Actor;
use App\Models\Address;
use App\Models\ChatAssistant;
use App\Models\Customer;
use App\Models\CustomerOperatingSite;
use App\Models\Group;
use App\Models\Movie;
use App\Models\OperatingSite;
use App\Models\OperatingSiteUser;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\OrganizationModule;
use App\Models\OrganizationUser;
use App\Models\TimeAccountSetting;
use App\Models\TimeAccountTransactionChange;
use App\Models\User;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use App\Services\AppModuleService;
use App\Services\OpenAIService;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->create([
                'name' => 'Test User',
                'email' => 'test@test.de',
                'password' => Hash::make('test1234'),
                'is_admin' => false
            ]);

        $admin = User::factory()
            ->create([
                'name' => 'Admin',
                'email' => 'admin@admin.de',
                'password' => Hash::make('admin1234'),
                'is_admin' => true
            ]);

        Actor::factory()
            ->create([
                'first_name' => 'World',
                'last_name' => 'Worlds'
            ]);

        Actor::factory()
            ->create([
                'first_name' => 'Bib Buck',
                'last_name' => 'Bunny'
            ]);



        Movie::factory()
            ->create([
                'user_id' => $admin->id,
                'title' => 'The Bunny',
                'genre' => 'Zeichentrick',
                'publication_date' => '2026-02-09',
                'rating' => '3',
                'hidden' => false,
                'movie_file_path' => Storage::disk('movies')->putFileAs('', new File('storage/app/public/The Bunny.mp4'), 'Bunny.mp4'),
                'thumbnail_file_path' => Storage::disk('movies')->putFileAs('thumbnails', new File('storage/app/public/thumbnails/The Bunny.jpg'), 'The Bunny.jpg'),
                'duration_in_seconds' => 5.803000,
                'description' => 'In diesem bezaubernden Beispielvideo "Bunny" erleben wir die Abenteuer eines süßen Kaninchens, das in einem farbenfrohen Garten voller Blumen und fröhlicher Tiere lebt. Das Video zeigt die neugewonnene Neugier des kleinen Bunny, während es die Umgebung erkundet, Freundschaften mit anderen Tieren schließt und verschiedene Herausforderungen meistert.',
            ]);



        Movie::factory()
            ->create([
                'user_id' => $admin->id,
                'title' => 'The World',
                'genre' => 'Si-Fi',
                'publication_date' => '2026-02-10',
                'rating' => '2',
                'hidden' => false,
                'movie_file_path' => Storage::disk('movies')->putFileAs('', new File('storage/app/public/The World.mp4'), 'World.mp4'),
                'thumbnail_file_path' => Storage::disk('movies')->putFileAs('thumbnails', new File('storage/app/public/thumbnails/The World.jpg'), 'World.jpg'),
                'duration_in_seconds' => 30.526667,
                'description' => 'In dieser faszinierenden Sci-Fi-Visualisierung erleben wir eine dynamische, drehende Weltkugel, die in einem futuristischen Setting präsentiert wird. Die Weltkugel ist nicht nur ein einfaches Modell der Erde, sondern ein interaktives, holografisches Objekt, das die Magie der Technologie und das Mysterium des Universums vereint.',
            ]);
    }
}
