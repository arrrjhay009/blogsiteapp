<x-profile-layout :profileData='$profileData' docTitle="{{ $profileData['username'] }}'s Profile">
  @include('profile-only')
</x-profile-layout>