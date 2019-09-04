import axios from 'axios';

const client = axios.create({
  baseURL: '/api',
  headers: {
    Authorization: 'Bearer '+localStorage.getItem('enstru_token')
  }
});

export default {
  all(params) {
    return client.get('users', params);
  },
  find(id) {
    return client.get(`users/${id}`);
  },
  update(id, data) {
    return client.put(`users/${id}`, data);
  },
  delete(id) {
    return client.delete(`users/${id}`);
  },
  create(data) {
    return client.post('users', data);
  },
};