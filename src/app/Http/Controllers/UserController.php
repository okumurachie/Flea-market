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
    public function index()
    {
        $users = User::all();
        $Items = Item::with('user')->get();
        $Items = Item::with('purchase')->get();
        return view('index', compact('users'));
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
            $filePath = $file->storeAs('public/images/profiles/', $fileName);
            $profileData['image'] = 'storage/images/profiles/' . $fileName;
        }
        $profile = $user->profile()->create($profileData);
        $user->profile_completed = true;
        $user->save();
        return redirect('/')->with('message', 'プロフィールを設定しました');
    }

    public function update(ProfileRequest $request, $id)
    {
        $profile = Profile::findOrFail($id);
        $profile->update(
            $request->except('image'),
        );
        if ($profile->image) {
            $oldImagePath = str_replace('storage/', 'public/', $profile->image);
            if (\Storage::exists($oldImagePath)) {
                \Storage::delete($oldImagePath);
            }
        }
        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('public/images', $fileName);
        $profile->image = 'storage/images/profiles' . $fileName;
        $profile->save();

        return redirect('/')->with('message', 'プロフィールを更新しました');
    }
}
