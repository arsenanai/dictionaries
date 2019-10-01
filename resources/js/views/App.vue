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
            <li class="nav-item" v-if="is('admin')">
              <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'users.index' }">{{$t('Users')}}</router-link>
            </li>
            <li class="nav-item">
                <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'groups.index' }">{{$t('Groups')}}</router-link>
            </li>
            <li class="nav-item">
                <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'subgroups.index' }">{{$t('Subgroups')}}</router-link>
            </li>
            <li class="nav-item">
                <router-link v-if="authenticated()==true" class="nav-link" active-class="active" :to="{ name: 'codes.index' }">{{$t('Codes')}}</router-link>
            </li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-globe"></i>
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <button :class="{'active': $i18n.locale==='kk'}" @click.prevent="changeLocale('kk')" class="dropdown-item">Қазақша</button>
                <button :class="{'active': $i18n.locale==='ru'}" @click.prevent="changeLocale('ru')" class="dropdown-item">Русский</button>
              </div>
            </li>
            <li class="nav-item dropdown" v-if="authenticated()==true">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-user-o"></i>
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <router-link class="dropdown-item" active-class=active 
                  :to="{ name: 'profile' }">
                    <i class="fa fa-user"></i> {{$t('Profile')}}
                </router-link>
                <router-link v-if="authenticated()==true" class="dropdown-item" active-class="active" :to="{ name: 'settings' }">
                  <i class="fa fa-cog" aria-hidden="true"></i>
                  {{$t('Settings')}}
                </router-link>
                <router-link v-if="authenticated()==true" class="dropdown-item" active-class="active" :to="{ name: 'auth.logout' }">
                  <i class="fa fa-power-off" aria-hidden="true"></i>
                  {{$t('Logout')}}
                </router-link>
              </div>
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
            /*'grant_type' => 'refresh_token',
        'refresh_token' => 'the-refresh-token',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'scope' => '',*/
            changeLocale(lang){
              this.$i18n.locale = lang
              axios.defaults.headers.common['Accept-Language'] = lang
              document.querySelector('html').setAttribute('lang', lang)
              localStorage.setItem(this.appLanguage, lang)
              this.$router.go()
            }
        },
        created(){
          if(localStorage.getItem(this.appLanguage)){
            this.$i18n.locale = localStorage.getItem(this.appLanguage)
            axios.defaults.headers.common['Accept-Language'] = this.$i18n.locale
              document.querySelector('html').setAttribute('lang', this.$i18n.locale)
          }
        },
    }
</script>

<style type="text/css">
    .page-number{
        width: 63px;
        text-align: center;
        border: 1px solid #dee2e6;
        margin-left: -1px;
        color: #6c757d;
    }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }
    .selected {
        color: #007bff;
    }

    .table-sm th, .table-sm td {
        font-size:0.8rem;
    }
    .name-cell{
        max-width:200px;
    }
    .pointer {cursor: pointer;}
</style>