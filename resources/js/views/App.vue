<template>
    <div>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container">
        <a class="navbar-brand" href="#">ENS TRU</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'users.index' }">Users</router-link>
            </li>
            <li class="nav-item">
                <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'groups.index' }">Groups</router-link>
            </li>
            <li class="nav-item">
                <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'subgroups.index' }">Subgroups</router-link>
            </li>
            <li class="nav-item">
                <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'codes.index' }">Codes</router-link>
            </li>
          </ul>
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a @click.prevent="changeLocale('kk')" class="nav-link">Қаз</a>
              </li>
              <li class="nav-item">
                  <a @click.prevent="changeLocale('ru')" class="nav-link">Рус</a>
              </li>
              <li class="nav-item">
                <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'auth.logout' }">
                  {{$t('Logout')}}
                  </router-link>
              </li>
          </ul>
        </div>
      </div>
    </nav>
    <div>
        <router-view></router-view>
    </div>
    </div>
</template>
<script>
    import {common} from '../common.js'
    import axios from 'axios'
    export default {
        mixins: [common],
        methods: {
            authenticated() {
                var result = false
                var token = localStorage.getItem(this.tokenVariable)
                if(token !== null){
                    result = true
                }
                return result
            },
            changeLocale(lang){
              this.$i18n.locale = lang
              localStorage.setItem(this.appLanguage, lang)
                params = {}
                params.lang = lang
                axios.get('/api/change-locale',{params})
              .then(response => {
                  console.log('locale changed in server: '+response.data)
              }).catch(error => {
                  console.log(error)
              });
            }
        },
        created(){
          if(localStorage.getItem(this.appLanguage)){
            this.$i18n.locale = localStorage.getItem(this.appLanguage)
          }else{
            axios.get('/api/get-locale')
              .then(response => {
                  console.log(response.data)
                  //localStorage.setItem(this.appLanguage, response.data)
              }).catch(error => {
                  console.log(error)
              });
          }
        },
    }
</script>