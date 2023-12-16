import './bootstrap';
import Search from './live-search';
import Chat from './chat';
import Profile from './profile';

// for Profile Nav
if (document.querySelector(".profile-nav")) {
    new Profile();
}

// for Search Button
if (document.querySelector(".header-search-icon")) {
    new Search();
}

// for Chat Button
if (document.querySelector(".header-chat-icon")) {
    new Chat();
}

