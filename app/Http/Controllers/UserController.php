<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    // USER RELATED FUNCTIONS
    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:8', 'max:32', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'max:32', 'confirmed']
        ]);

        // User::create($incomingFields);
        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect('/')->with('success', 'You have successfully registered!');
    }

    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'loginusername' => ['required'],
            'loginpassword' => ['required']
        ]);

        if (auth()->attempt([
            'username' => $incomingFields['loginusername'],
            'password' => $incomingFields['loginpassword']])) {
                $request->session()->regenerate();
                event(new OurExampleEvent(['username' => auth()->user()->username, 'action' => 'logged in']));
                return redirect('/')->with('success', 'You have successfully logged in!');
            }
            else {
                return redirect('/')->with('fail', 'Incorrect login details!');
            }
    }

    public function logout(Request $request)
    {
        event(new OurExampleEvent(['username' => auth()->user()->username, 'action' => 'logged out']));
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'You have successfully logged out!');
    }

    public function showHomepage()
    {
        if (auth()->check()) {
            return view('feed', ['posts' => auth()->user()->feed()->latest()->paginate(6)]);
        } else {
            // if (Cache::has('postCount')) {
            //     $postCount = Cache::get('postCount');
            //     } else {
            //         $postCount = Post::count();
            //         Cache::put('postCount', $postCount, 300);
            //     }
            $postCount = Cache::remember('postCount', 300, function(){
                return Post::count();
            });
            return view('homepage' , ['postCount'=> $postCount]);
        }
    }

    // PROFILE RELATED FUNCTIONS

    private function profileData($user) {
        $followCheck = 0;
        if (auth()->check()) {
            $followCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->count();
        }

        View::share('profileData', ['username' => $user->username, 'avatar' => $user->avatar, 'postsCount' => $user->posts()->count(), 'followerCount' => $user->follower()->count(), 'followingCount' => $user->following()->count(), 'followCheck' => $followCheck]);
    }
    public function showProfile(User $user)
    {
        $this->profileData($user);
        return view('profile', ['posts' => $user->posts()->latest()->get()]);
    }

    public function showProfileRAW(User $user)
    {
        return response()->json(['theHTML' => view('profile-only', ['posts' => $user->posts()->latest()->get()])->render(), 'docTitle' => $user->username . "'s Profile"]);
    }

    public function showProfileFollower(User $user)
    {
        $this->profileData($user);  
        return view('profile-follower', ['follower' => $user->follower()->latest()->get()]);
    }

    public function showProfileFollowerRAW(User $user)
    {
        return response()->json(['theHTML' => view('profile-follower-only', ['follower' => $user->follower()->latest()->get()])->render(), 'docTitle' => $user->username . "'s Followers"]);
    }

    public function showProfileFollowing(User $user)
    {
        $this->profileData($user);   
        return view('profile-following', ['following' => $user->following()->latest()->get()]);
    }

    public function showProfileFollowingRAW(User $user)
    {
        return response()->json(['theHTML' => view('profile-following-only', ['following' => $user->following()->latest()->get()])->render(), 'docTitle' => $user->username . "'s Following"]);
    }

    public function showAvatarForm()
    {
        return view('avatar-form');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image']
        ]);

        $user = auth()->user();
        $filename = $user->username . '-' . uniqid() . '.jpg';

        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $filename, $imgData);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != '/fallback-avatar.png') {
            // Storage::delete('public/' . $oldAvatar);
            Storage::delete(str_replace('/storage/', 'public/', $oldAvatar));
        }

        return back()->with('success', 'Avatar updated!');
    }
}
