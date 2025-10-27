<?php
namespace App\Services;


use App\Models\User;
use App\Models\Role;
use App\Models\KernTeam;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;


class UserService
{
    public function getAllUsers()
    {
        return User::with('role')->orderBy('name')->get(['id', 'idRole', 'name', 'email']);
    } 
    // medewerkers / rooster makers aanmaken (via microsoft callback)

    public function findOrCreateUser($microsoftAccount)
    {
        // Normalize to object
        $acct = is_array($microsoftAccount) ? (object) $microsoftAccount : $microsoftAccount;

        // Robust email extraction
        $email = $acct->email
            ?? ($acct->mail ?? null)
            ?? ($acct->user['mail'] ?? null)
            ?? ($acct->userPrincipalName ?? null)
            ?? ($acct->user['userPrincipalName'] ?? null);

        if (empty($email)) {
            throw ValidationException::withMessages([
                'email' => ['Geen e-mail beschikbaar in Microsoft account. Neem contact op met de ms365 beheerder.']
            ]);
        }

        // Return existing user if present
        $user = User::where('email', $email)->first();
        if ($user) {
            return $user;
        }

        // Determine jobTitle (may be in different locations)
        $jobTitle = $acct->user['jobTitle'] ?? $acct->jobTitle ?? null;
        $jobTitle = is_string($jobTitle) ? trim($jobTitle) : null;

        // Role lookup: case-insensitive exact first, then LIKE fallback
        $role = null;
        if (!empty($jobTitle)) {
            $role = Role::whereRaw('LOWER(name) = ?', [mb_strtolower($jobTitle)])->first();
            if (!$role) {
                $role = Role::where('name', 'LIKE', '%' . $jobTitle . '%')->first();
            }
        }

        if (!$role) {
            // Throw validation exception so controller can handle it
            throw ValidationException::withMessages([
                'jobTitle' => ['Geen overeenkomende rol gevonden voor uw Microsoft account functie: ' . ($jobTitle ?? 'Onbekend') . ' neem contact op met de ms365 beheerder.']
            ]);
        }

        // Resolve names (givenName + surname)
        $resolved = $this->resolveNamesFromMicrosoftAccount($acct);

        $given = $resolved['givenName'] ?? null;
        $surname = $resolved['surname'] ?? null;

        if (empty($given) && empty($surname)) {
            throw ValidationException::withMessages([
                'name' => ['Microsoft account mist zowel voornaam als achternaam, en volledige naam is niet beschikbaar. Neem contact op met de ms365 beheerder.']
            ]);
        }

        // Create user
        $user = User::create([
            'voornaam' => $given ?? '',
            'achternaam' => $surname ?? '',
            'email' => $email,
            'name' => $acct->displayName ?? ($acct->name ?? trim(($given ?? '') . ' ' . ($surname ?? ''))),
            'password' => Hash::make(Str::random(40)), // Azure verzorgt auth; DB vereist iets
            'idRol' => $role->id ?? 1,
        ]);

        $user->load('role');

        return $user;
    }

    /**
     * Helper: probeer givenName + surname te bepalen.
     * Strategy: use explicit fields if present; otherwise parse displayName/name.
     */
    protected function resolveNamesFromMicrosoftAccount($acct): array
    {
        $given = $acct->givenName ?? ($acct->user['givenName'] ?? null);
        $surname = $acct->surname ?? ($acct->user['surname'] ?? null);

        if (!empty($given) || !empty($surname)) {
            return [
                'givenName' => $given ?: null,
                'surname' => $surname ?: null,
                'note' => 'used explicit fields'
            ];
        }

        $display = $acct->displayName ?? $acct->name ?? ($acct->user['displayName'] ?? null);
        if (empty($display)) {
            return ['givenName' => null, 'surname' => null, 'note' => 'no name available'];
        }

        $parts = preg_split('/\s+/', trim($display));
        if (count($parts) === 1) {
            return ['givenName' => $parts[0], 'surname' => null, 'note' => 'single-token name'];
        }

        $givenName = array_shift($parts);
        $surname = implode(' ', $parts); // preserves prefixes like "van der"

        return ['givenName' => $givenName, 'surname' => $surname, 'note' => 'parsed displayName'];
    }
    // cliënten aanmaken
    public function createUser(array $data): User
    {
        // get the current logged in user
        $user = User::create($data);
        return $user;
    }


    public function getUserByEmail(string $email): ?User
    {
        return User::with('role')->where('email', $email)->first();
    }

    public function getUser(int $id): ?User
    {
        return User::find($id);
    }


    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->getUser($id);

        if (!$user) {
            return response()->json(['messages' => ['User not found']], 404);
        }

        // Handle old koppel removal
        $oldKoppel = User::where('idkoppel', '=', $data['id'])->first();
        if ($oldKoppel) {

            $oldKoppelTeam = KernTeam::where('idClient', $oldKoppel->id)->get();
            KernTeam::where('idClient', $oldKoppel->id)->delete();

            $userTeam = KernTeam::where('idClient', $id)->get();
            KernTeam::where('idClient', $id)->delete();

            $oldKoppel->idKoppel = null;
            $oldKoppel->save();
        }

        // Handle new koppel
        if (!empty($data['idKoppel'])) {
            $otherUser = User::find($data['idKoppel']);
            if ($otherUser) {
                $otherUser->idKoppel = $data['id'];
                $otherUser->save();

                KernTeam::where('idClient', $otherUser->id)->delete();

                KernTeam::where('idClient', $id)->delete();
            }
        }

        // Update the main user
        $user->update($data);

        return $user;
    }

    public function getAvailableUsers($date)
    {
        $date = Carbon::parse($date)->startOfDay();

        $startOfWeek = $date->copy()->startOfWeek();   // Monday by default
        $endOfWeek = $date->copy()->endOfWeek();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $dayName = strtolower(Carbon::parse($date)->locale('nl')->dayName);
        // e.g. "maandag", "dinsdag", ...
        $users = User::where('idRol', '!=', 3)
            ->whereDoesntHave('verlof', function ($q) use ($date) {
                $q->whereDate('startTijd', $date)
                    ->where('verwerkt', 1);
            })
            ->whereHas('werkdagenGebruiker', function ($q) use ($dayName) {
                $q->where($dayName, 1);
            })
            ->with([
                // eager load + nested relations
                'roosterGebruiker' => function ($q) use ($date) {
                    $q->whereDate('datum', $date)
                        ->with(['client:id,voornaam,achternaam,name', 'gebruiker:id,voornaam,achternaam,name']);
                },
                'afspraakGebruiker' => function ($q) use ($date) {
                    $q->whereDate('datum', $date)
                        ->with(['client:id,voornaam,achternaam,name', 'gebruiker:id,voornaam,achternaam,name']);
                },
                'telefoonroosterGebruiker' => function ($q) use ($date) {
                    $q->whereDate('datum', $date)
                        ->with(['gebruiker:id,voornaam,achternaam,name']);
                },
                'role',
            ])
            ->get();



        return $users
            ->filter(function ($user) {
                // original filtering
                $roosters = $user->roosterGebruiker ?? collect();
                $afspraken = $user->afspraakGebruiker ?? collect();
                $telefoon = $user->telefoonroosterGebruiker ?? collect();

                $roosterIds = $roosters->pluck('idAfspraak')->filter();
                $afspraken = $afspraken->reject(fn($a) => $roosterIds->contains($a->id));

                $roosterCount = $roosters->count();
                $telefoonCount = $telefoon->count();
                $totalAppointments = $roosterCount + $afspraken->count() + $telefoonCount;

                if ($roosterCount >= 3)
                    return false;
                if ($telefoonCount > 0 && $totalAppointments >= 2)
                    return false;

                return true;
            })
            ->map(function ($user) use ($date, $startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth) {
                // --- relevant items with from tag ---
                $roosters = ($user->roosterGebruiker ?? collect())
                    ->map(fn($r) => array_merge($r->toArray(), ['from' => 'rooster']))
                    ->values();

                $roosterIds = ($user->roosterGebruiker ?? collect())->pluck('idAfspraak')->filter();

                $afspraken = ($user->afspraakGebruiker ?? collect())
                    ->reject(fn($a) => $roosterIds->contains($a->id))
                    ->map(fn($a) => array_merge($a->toArray(), ['from' => 'afspraak']))
                    ->values();

                $telefoon = ($user->telefoonroosterGebruiker ?? collect())
                    ->map(fn($t) => array_merge($t->toArray(), ['from' => 'telefoon']))
                    ->values();

                $relevant = [
                    'rooster' => $roosters,
                    'afspraak' => $afspraken,
                    'telefoon' => $telefoon,
                ];

                $items = $roosters->concat($afspraken)->concat($telefoon)->values();

                // --- counts ---
                $countDay = $items->count();

                $countWeek = collect()
                    ->concat($user->roosterGebruiker()->whereBetween('datum', [$startOfWeek, $endOfWeek])->get())
                    ->concat($user->afspraakGebruiker()->whereBetween('datum', [$startOfWeek, $endOfWeek])->get())
                    ->concat($user->telefoonroosterGebruiker()->whereBetween('datum', [$startOfWeek, $endOfWeek])->get())
                    ->count();

                $countMonth = collect()
                    ->concat($user->roosterGebruiker()->whereBetween('datum', [$startOfMonth, $endOfMonth])->get())
                    ->concat($user->afspraakGebruiker()->whereBetween('datum', [$startOfMonth, $endOfMonth])->get())
                    ->concat($user->telefoonroosterGebruiker()->whereBetween('datum', [$startOfMonth, $endOfMonth])->get())
                    ->count();

                $user->setAttribute('relevant', $relevant);
                $user->setAttribute('items', $items);
                $user->setAttribute('countDay', $countDay);
                $user->setAttribute('countWeek', $countWeek);
                $user->setAttribute('countMonth', $countMonth);

                return $user;
            })
            ->values();
    }


    public function deleteUser(int $id): bool
    {
        $user = $this->getUser($id);
        if ($user) {
            return $user->delete();
        }
        return false;
    }
}