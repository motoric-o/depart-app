<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bookmark;
use App\Models\Schedule;
use App\Models\Booking;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->bookmarks()->with('bookmarkable');

        if ($request->filled('filter')) {
            if ($request->filter === 'schedule') {
                $query->where('bookmarkable_type', 'App\Models\Schedule');
            } elseif ($request->filter === 'booking') {
                $query->where('bookmarkable_type', 'App\Models\Booking');
            }
        }

        $bookmarks = $query->latest()->get();
        return view('customer.bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'bookmarkable_id' => 'required',
            'bookmarkable_type' => 'required|in:App\Models\Schedule,App\Models\Booking',
        ]);

        $user = auth()->user();
        $attributes = [
            'user_id' => $user->id,
            'bookmarkable_id' => $request->bookmarkable_id,
            'bookmarkable_type' => $request->bookmarkable_type,
        ];

        $bookmark = Bookmark::where($attributes)->first();

        if ($bookmark) {
            $bookmark->delete();
            
            if ($request->wantsJson()) {
                return response()->json(['status' => 'removed']);
            }
            return back();
        } else {
            Bookmark::create($attributes);
            
            if ($request->wantsJson()) {
                return response()->json(['status' => 'created']);
            }
            return back();
        }
    }
}
