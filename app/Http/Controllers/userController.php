<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Cocur\Slugify\Slugify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class userController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::with(['city:id,name', 'governorate:id,name', 'shopCategory:id,name'])->get();
            return response()->json($users, 200);
        } catch (Exception $e) {
            return response()->json('error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $slugify = new Slugify();
            $slug = $slugify->slugify($request->full_name);
            $user = new User;
            $user->full_name = $request->full_name;
            $user->slug = $slug;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->governorate_id = $request->governorate_id;
            $user->city_id = $request->city_id;
            $user->shop_category_id = $request->shop_category_id;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;

            $userName = $request->email;
            $path = 'image/';
            $userFolder = public_path($path . $userName);


            // Save profile Image
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImageName = $userName . time() . '.' . $profileImage->getClientOriginalExtension();
                $profileImage->move($userFolder, $profileImageName);
                $user['profile_image'] = $profileImageName;
            } else {
                $user['profile_image'] = "profile.png";
            }

            // Save Cover Image
            if ($request->hasFile('cover_image')) {
                $coverImage = $request->file('cover_image');
                $coverImageName = $userName . time() . '.' . $coverImage->getClientOriginalExtension();
                $coverImage->move($userFolder, $coverImageName);
                $user['cover_image'] = $coverImageName;
            } else {
                $user['cover_image'] = "cover.png";
            }

            $user->save();

            $phones = $request->input('phone');
            $user->phones()->create(['phone' => $phones]);

            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json('error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::with(['city:id,name', 'governorate:id,name'])->findorfail($id);
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json('error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findorfail($id);
            $user->f_name = $request->f_name;
            $user->l_name = $request->l_name;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->governorate_id = $request->governorate_id;
            $user->city_id = $request->city_id;
            $user->shop_category_id = $request->shop_category_id;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->update();
            return response()->json($user, 200);
        } catch (Exception $e) {
            return response()->json('error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findorfail($id);
            $user->delete();
            return response()->json('user deleted successfully', 200);
        } catch (Exception $e) {
            return response()->json('error: ' . $e->getMessage(), 500);
        }
    }

    public function getShopOwner()
    {
        try {
            $shopOwners = User::where('role', 'صاحب محل')->with('shopCategory:id,name')->get(['full_name', 'address', 'shop_category_id', 'slug']);
            return response()->json($shopOwners, 200);
        } catch (Exception $e) {
            return response()->json('error: ' . $e->getMessage(), 500);
        }
    }

    public function filterProducts(Request $request)
    {
        $query = User::query();
        if ($request->has('category')) {
            $query->whereHas('shopCategory', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        // You can add more filters based on your requirements
        $shops = $query->with('shopCategory:name,id')->get();
        return response()->json($shops);
    }
}
