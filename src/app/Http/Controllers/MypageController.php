<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Purchase;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Storage;

class MypageController extends Controller
{
    // プロフィール画面
    public function index(Request $request){
        $user = auth()->user();
        $tab = $request->query('tab', 'sell'); 

        $sellItems = Product::where('user_id', $user->id)->get();
        $buyItems = Purchase::where('user_id', $user->id)->with('product')->get();

        return view('mypage.index', compact('user', 'sellItems', 'buyItems', 'tab'));
    }

    // プロフィール編集画面
    public function edit(){
        $user = auth()->user()->load('userProfile');
        return view('mypage.edit', compact('user'));
    }

    // プロフィール更新処理
    public function updateProfile(AddressRequest $request){
        $user = auth()->user();

        $profile = $user->userProfile ?? new \App\Models\UserProfile(['user_id' => $user->id]);

        if ($request->hasFile('user_image')) {
            if ($profile->profile_image && Storage::disk('public')->exists($profile->profile_image)) {
                Storage::disk('public')->delete($profile->profile_image);
            }

            $originalName = $user->id . '_' . $request->file('user_image')->getClientOriginalName();
            $path = $request->file('user_image')->storeAs('user_images', $originalName, 'public');

            $profile->profile_image = $path;
        }

        $profile->postal_code = $request->postal_code;
        $profile->address = $request->address;
        $profile->building = $request->building;
        $profile->save();

        $user->user_name = $request->user_name;
        $user->save();

        return redirect('/mypage');
    }
    
}
