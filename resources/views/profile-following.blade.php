<x-profile-layout :profileData='$profileData' docTitle="{{ $profileData['username'] }}'s Following">
  @include('profile-following-only')    
</x-profile-layout>