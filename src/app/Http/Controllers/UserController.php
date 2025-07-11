<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
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
                $items = $user->favorites()
                    ->with(['user', 'purchase'])
                    ->KeywordSearch($keyword)
                    ->where('user_id', '!=', $user->id)
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

        if ($request->hadFile('image')) {
            if ($profile->image) {
                $oldImagePath = str_replace('storage/', 'public/', $profile->image);
                if (\Storage::exists($oldImagePath)) {
                    \Storage::delete($oldImagePath);
                }
            }
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images/profiles', $fileName);
            $profile->image = 'storage/images/profiles' . $fileName;
        }
        $profile->user_name = $request->input('user_name');
        $profile->post_code = $request->input('post_code');
        $profile->address = $request->input('address');
        $profile->building = $request->input('building');

        // 必要に応じてここで profile_completed を更新
        $profile->profile_completed = true;

        $profile->save();

        return redirect('/')->with('message', 'プロフィールを更新しました');
    }
}
