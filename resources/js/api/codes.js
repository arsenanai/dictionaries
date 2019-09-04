import axios from 'axios';

const client = axios.create({
  baseURL: '/api',
  headers: {
    Authorization: 'Bearer '+localStorage.getItem('enstru_token')
  }
});

export default {
  all(params) {
    return client.get(`codes`, params);
  },
  find(id) {
    return client.get(`codes/show/${id}`);
  },
  update(id, data) {
    return client.put(`codes/${id}`, data);
  },
  delete(id) {
    return client.delete(`codes/${id}`);
  },
  create(data) {
    return client.post('codes', data);
  },
  search(type, params){
    return client.get(type+'s/by-name', {params});
  },
  migrate(params){
    return client.post('codes/migrate', params);
  },
};