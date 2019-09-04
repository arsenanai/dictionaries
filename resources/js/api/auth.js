import axios from 'axios';
//const axios = () => import('axios');

const client = axios.create({
  baseURL: '/api',
});

export default {
  login(params) {
    return client.post('login', params);
  },
  register(params) {
    return client.post(`register`, params);
  },
  logout(data, headers) {
    return client.get(`logout`, data, headers);
  },
  user(data, headers) {
    return client.get(`user`, data, headers);
  },
};