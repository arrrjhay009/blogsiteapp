<x-layout :docTitle="$docTitle">

    <div class="container py-md-5 container--narrow">
        <h2>
          <img class="avatar-small" src="{{  $profileData['avatar'] }}" /> {{ $profileData['username'] }}
          @auth
          @if (!$profileData['followCheck'] AND auth()->user()->username !== $profileData['username'])
            <form class="ml-2 d-inline" action="/follow-user/{{ $profileData['username'] }}" method="POST">
              @csrf
              <button class="btn btn-primary btn-sm">Follow <i class="fas fa-user-plus"></i></button>
            </form>
          @endif

          @if ($profileData['followCheck'])
            <form class="ml-2 d-inline" action="/unfollow-user/{{ $profileData['username'] }}" method="POST">
              @csrf
              <button class="btn btn-danger btn-sm">Unfollow <i class="fas fa-user-times"></i></button>
            </form>
            @endif
            @if (auth()->user()->username === $profileData['username'])
              <a href="/manage-avatar" class="ml-2 btn btn-secondary btn-sm">Manage Avatar</a>
            @endif
          @endauth
        </h2>
  
        <div class="profile-nav nav nav-tabs pt-2 mb-4">
          <a href="/profile/{{ $profileData['username'] }}" class="profile-nav-link nav-item nav-link {{ Request::segment(3) === null ? "active" : ""}}">Posts: {{ $profileData['postsCount'] }}</a>
          <a href="/profile/{{ $profileData['username'] }}/follower" class="profile-nav-link nav-item nav-link {{ Request::segment(3) === "follower" ? "active" : ""}}">Followers: {{ $profileData['followerCount'] }}</a>
          <a href="/profile/{{ $profileData['username'] }}/following" class="profile-nav-link nav-item nav-link {{ Request::segment(3) === "following" ? "active" : ""}}">Following: {{ $profileData['followingCount'] }}</a>
        </div>

        <div class="profile-slot-content">
            {{ $slot }}
        </div>
  
      </div>

</x-layout>