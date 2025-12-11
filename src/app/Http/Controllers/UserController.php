<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Profile;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'recommend');
        $keyword = $request->query('keyword');
        $user = Auth::user();

        if ($tab === 'mylist') {
            if ($user) {
                $items = Item::whereHas('favorites', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                    ->keywordSearch($keyword)
                    ->where('user_id', '!=', $user->id)
                    ->with(['user', 'purchase'])
                    ->latest()
                    ->paginate(8)
                    ->appends($request->except('page'));
            } else {
                $items = collect();
            }
        } else {
            $items = Item::with(['user', 'purchase'])
                ->KeywordSearch($keyword)
                ->when(Auth::check(), function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id);
                })
                ->latest()
                ->paginate(8)
                ->appends(request()->except('page'));
        }
        return view('index', compact('items', 'tab'));
    }
    public function mypage(Request $request)
    {
        $page = $request->query('page', 'sell');
        $user = Auth::user();
        $profile = $user->profile;

        $averageRating = $user->average_rating;
        $reviewCount = $user->review_count;

        $totalUnreadCount = Purchase::where('transaction_status', 'in_progress')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('item', function ($subquery) use ($user) {
                        $subquery->where('user_id', $user->id);
                    });
            })
            ->withCount([
                'messages as unread_count' => function ($query) use ($user) {
                    $query->where('sender_id', '!=', $user->id)
                        ->where('is_read', false);
                }
            ])
            ->get()
            ->sum('unread_count');

        $items = collect();
        $purchases = collect();
        $transactions = collect();

        if ($page === 'buy') {
            $purchases = $user->purchases()
                ->with('item')
                ->latest()
                ->paginate(8)
                ->appends(request()->except('page'));
        } elseif ($page === 'sell') {
            $items = $user->items()
                ->latest()
                ->paginate(8)
                ->appends(request()->except('page'));
        } elseif ($page === 'transaction') {
            $transactions = Purchase::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('item', function ($subquery) use ($user) {
                        $subquery->where('user_id', $user->id);
                    });
            })
                ->with([
                    'item.user',
                    'user',
                    'lastMessage'
                ])
                ->withCount([
                    'messages as unread_count' => function ($query) use ($user) {
                        $query->where('sender_id', '!=', $user->id)
                            ->where('is_read', false);
                    }
                ])
                ->orderByDesc('last_message_at')
                ->orderByDesc('created_at')
                ->paginate(8)
                ->appends(request()->except('page'));
        }
        return view('mypage', compact('profile', 'items', 'purchases', 'transactions', 'page',  'averageRating', 'reviewCount', 'totalUnreadCount'));
    }

    public function profile()
    {
        $user = Auth::user();
        $profile = $user->profile ?? null;
        return view('profile', compact('user', 'profile'));
    }

    public function store(ProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $profileData = $request->validated();
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images/profiles/', $fileName);
            $profileData['image'] = 'storage/images/profiles/' . $fileName;
        }
        $profile = $user->profile()->create($profileData);
        $profile->update(['profile_completed' => true]);

        return redirect('/')->with('message', 'プロフィールを設定しました');
    }

    public function update(ProfileRequest $request, $id)
    {
        $profile = Profile::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($profile->image) {
                $oldImagePath = str_replace('storage/', 'public/', $profile->image);
                if (\Storage::exists($oldImagePath)) {
                    \Storage::delete($oldImagePath);
                }
            }
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images/profiles/', $fileName);
            $profile->image = 'storage/images/profiles/' . $fileName;
        }
        $profile->user_name = $request->input('user_name');
        $profile->post_code = $request->input('post_code');
        $profile->address = $request->input('address');
        $profile->building = $request->input('building');

        $profile->profile_completed = true;

        $profile->save();

        return redirect('/')->with('message', 'プロフィールを更新しました');
    }
    public function showEditAddressForm($item_id)
    {
        $user = Auth::user();
        $profile = $user->profile;
        $item = Item::findOrFail($item_id);
        return view('address', compact('user', 'profile', 'item'));
    }
    public function editAddress(AddressRequest $request, $item_id)
    {
        $user = Auth::user();
        $profile = $user->profile;

        $profile->update([
            'post_code' => $request->input('post_code'),
            'address'     => $request->input('address'),
            'building'    => $request->input('building'),
        ]);
        return redirect()->route('purchase.show', ['id' => $item_id])->with('message', '住所を変更しました');
    }
}
