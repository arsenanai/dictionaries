import axios from 'axios';

const client = axios.create({
  baseURL: '/api',
  headers: {
    Authorization: 'Bearer '+localStorage.getItem('enstru_token')
  }
});

export default {
  all(params) {
    return client.get('groups', params);
  },
  find(id) {
    return client.get(`groups/show/${id}`);
  },
  update(id, data) {
    return client.put(`groups/${id}`, data);
  },
  delete(id) {
    return client.delete(`groups/${id}`);
  },
  create(data) {
    return client.post('groups', data);
  },
  search(type, params){
    return client.get(type+'s/by-name', {params});
  },
};