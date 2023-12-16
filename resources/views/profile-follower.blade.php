<x-profile-layout :profileData='$profileData' docTitle="{{ $profileData['username'] }}'s Followers">
    @include('profile-follower-only')
</x-profile-layout>