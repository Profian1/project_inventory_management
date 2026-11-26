<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class SettingsController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // The entire settings area is admin-only.
        $this->middleware('admin');
    }

    /**
     * Show the settings form, user management and backup controls.
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        $users = User::orderBy('name')->get();

        return view('settings.index', compact('settings', 'users'));
    }

    /**
     * Save the application settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_reorder_level' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'receipt_footer' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
        ]);

        // Upload and store the business logo path.
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logo', 'public');
            $this->save('business_logo', $path);
        }

        // Text/number settings.
        foreach ([
            'business_name',
            'business_address',
            'business_phone',
            'business_email',
            'vat_rate',
            'default_reorder_level',
            'low_stock_threshold',
            'receipt_footer',
        ] as $key) {
            $this->save($key, $validated[$key] ?? null);
        }

        // Toggle settings (checkboxes are only present when checked).
        $this->save('vat_enabled', $request->has('vat_enabled') ? '1' : '0');
        $this->save('receipt_show_logo', $request->has('receipt_show_logo') ? '1' : '0');

        return back()->with('status', 'Settings saved.');
    }

    /**
     * Change a user's role (admin only).
     */
    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,staff'],
        ]);

        // Prevent an admin from demoting themselves and locking everyone out.
        if ($user->id === $request->user()->id && $validated['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('status', 'User role updated.');
    }

    /**
     * Activate / deactivate a user (admin only).
     */
    public function toggleUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    /**
     * Stream a mysqldump backup of the database.
     */
    public function backupDatabase()
    {
        $config = config('database.connections.mysql');

        $process = Process::run([
            env('MYSQLDUMP_PATH', 'mysqldump'),
            "--user={$config['username']}",
            "--password={$config['password']}",
            "--host={$config['host']}",
            "--port={$config['port']}",
            '--skip-comments',
            $config['database'],
        ]);

        if (! $process->successful()) {
            return back()->with('error', 'Backup failed. Ensure mysqldump is installed.');
        }

        $filename = 'balsheet-backup-' . now()->format('Y-m-d-His') . '.sql';

        return response()->streamDownload(fn () => print($process->output()), $filename, ['Content-Type' => 'application/sql']);
    }

    /**
     * Persist a single key/value setting.
     */
    private function save(string $key, ?string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
