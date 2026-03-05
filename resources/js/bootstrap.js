import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set auth token from localStorage on every page
const token = localStorage.getItem('token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
}