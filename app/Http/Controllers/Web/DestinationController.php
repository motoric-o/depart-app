<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Destination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class DestinationController extends Controller
{
    private function checkPermission()
    {
        $user = Auth::user();
        $allowedRoles = ['Super Admin', 'Owner', 'Scheduling Admin'];
        
        if (!in_array($user->accountType->name, $allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index(Request $request)
    {
        $this->checkPermission();

        $query = Destination::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('city_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $destinations = $query->orderBy('city_name', 'asc')->paginate(10)->withQueryString();

        return view('management.destinations.index', compact('destinations'));
    }

    public function create()
    {
        $this->checkPermission();
        return view('management.destinations.create');
    }

    public function store(Request $request)
    {
        $this->checkPermission();

        // Sanitization: Auto-uppercase the code
        $request->merge(['code' => strtoupper($request->code)]);

        $request->validate([
            'code' => 'required|string|max:5|unique:destinations,code|alpha_num|uppercase',
            'city_name' => 'required|string|max:255|unique:destinations,city_name',
        ]);

        DB::transaction(function () use ($request) {
            DB::statement("CALL sp_manage_destination('CREATE', NULL, ?, ?)", [
                $request->code,
                $request->city_name
            ]);
        });

        return redirect()->route('admin.destinations')->with('success', 'Destination created successfully.');
    }

    public function edit($code)
    {
        $this->checkPermission();
        $destination = Destination::findOrFail($code);
        return view('management.destinations.edit', compact('destination'));
    }

    public function update(Request $request, $code)
    {
        $this->checkPermission();
        $destination = Destination::findOrFail($code);

        // Sanitization: Auto-uppercase the code
        $request->merge(['code' => strtoupper($request->code)]);

        $request->validate([
            'code' => 'required|string|max:5|alpha_num|uppercase|unique:destinations,code,' . $destination->code . ',code',
            'city_name' => 'required|string|max:255|unique:destinations,city_name,' . $destination->code . ',code',
        ]);

        DB::transaction(function () use ($request, $destination) {
            DB::statement("CALL sp_manage_destination('UPDATE', ?, ?, ?)", [
                $destination->code,
                $request->code,
                $request->city_name
            ]);
        });

        return redirect()->route('admin.destinations')->with('success', 'Destination updated successfully.');
    }

    public function destroy($code)
    {
        $this->checkPermission();
        
        try {
            DB::transaction(function () use ($code) {
                DB::statement("CALL sp_delete_destination(?)", [$code]);
            });
            return redirect()->route('admin.destinations')->with('success', 'Destination deleted successfully.');
        } catch (\Exception $e) {
            // Likely foreign key constraint or custom exception from SP
            return back()->with('error', 'Failed to delete destination. It might be in use by Routes or other entities.');
        }
    }
}
