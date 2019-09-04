import axios from 'axios';

const client = axios.create({
  baseURL: '/api',
  headers: {
    Authorization: 'Bearer '+localStorage.getItem('enstru_token')
  }
});

export default {
  all(params) {
    return client.get('subgroups', params);
  },
  bygroup(id, params){
    return client.get('subgroups/by-group/${$id}', params);
  },
  find(id) {
    return client.get(`subgroups/show/${id}`);
  },
  update(id, data) {
    return client.put(`subgroups/${id}`, data);
  },
  delete(id) {
    return client.delete(`subgroups/${id}`);
  },
  create(data) {
    return client.post('subgroups', data);
  },
  search(type, params){
    return client.get(type+'s/by-name', {params});
  },
  migrate(params){
    return client.post('subgroups/migrate', params);
  },
};