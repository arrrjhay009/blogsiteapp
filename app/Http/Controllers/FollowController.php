<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    //
    public function followUser(User $user) {

        if (auth()->user()->id == $user->id) {
            return back()->with('fail', 'You cannot follow yourself.');
        }

        // $followCheck = Follow::where('user_id', auth()->user()->id)->where('followedUser', $user->id)->first();
        $followCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->count();
        if ($followCheck) {
            return back()->with('fail', 'You are already following this user.');
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followedUser = $user->id;
        $newFollow->save();

        return back()->with('success', 'You are now following this user.');
    }

    public function unfollowUser(User $user) {
        Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->delete();
        return back()->with('success', 'You have unfollowed this user.');
    }
}
