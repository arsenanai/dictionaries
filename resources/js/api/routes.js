import axios from 'axios';

const client = axios.create({
  baseURL: '/api',
});
if(localStorage.getItem("enstru_token")!==null)
  client.defaults.headers.common['Authorization'] = localStorage.getItem("token_type")+' '+localStorage.getItem("enstru_token")

export default {
  login(params) {
    return axios.post('oauth/token', params);
  },
  register(params) {
    return client.post(`register`, params);
  },
  logout(data) {
    return client.get(`logout`, data);
  },
  user(data, headers) {
    return client.get(`user`, data, headers);
  },
  quit(){
    delete client.defaults.headers.common["Authorization"]
  },
  logged(){
    client.defaults.headers.common['Authorization'] = localStorage.getItem("token_type")+' '+localStorage.getItem("enstru_token")
  },
  all(type, params) {
    return client.get(type+'s', params);
  },
  profile(){
    return client.get('users/profile');
  },
  find(type, id) {
    return client.get(type+`s/show/${id}`);
  },
  update(type, id, data) {
    return client.put(type+`s/${id}`, data);
  },
  delete(type, id) {
    return client.delete(type+`s/${id}`);
  },
  create(type, data) {
    return client.post(type+'s', data);
  },
  search(type, params){
    return client.get(type+'s/by-name', {params});
  },
  migrate(type, params){
    return client.post(type+'s/migrate', params);
  },
  save(type,params){
    return client.post(type+'s/save',params)
  },
  reset(params){
    return client.post('settings/reset',params)
  },
  sync(params){
    return client.post('settings/sync',params)
  },
};