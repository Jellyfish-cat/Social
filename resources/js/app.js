import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// import modules
import './modules/home';
import './modules/post';
import './modules/like';
import './modules/comment';
import './modules/createPost';
import './modules/editPost';