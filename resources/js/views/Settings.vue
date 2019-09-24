<template>
  <div class="container ">   
    <div class="row">
      <div class="col-lg-8 offset-lg-2"> 
        <h2>
          {{$t('Settings')}}
        </h2>
        <hr>
        <div class="alert alert-success" v-if="message">
          {{message}}
        </div>
        {{setting}}
        <form @submit.prevent="save()">
          <div class="form-group" v-for="setting in settings">
            <label :for="setting.key">
              <i class="fa fa-globe"
                v-if="setting.key==='enstru_language'"></i>
              {{setting.title}}
            </label>
            <input class="form-control" 
              :id="setting.key"
              :type="setting.type"
              v-model="setting.value" 
              v-if="setting.type==='number'"
              :min="setting.constraints.min"
              :max="setting.constraints.max"
              />
            <select class="form-control" :id="setting.key" v-model="setting.value"
              v-else-if="setting.type==='select'">
              <option
                v-for="(value, index) in setting.options" 
                :value="index">{{value}}</option>
            </select>
          </div>
          <div class="form-group">
            <button class="btn btn-outline-primary" type="submit">{{$t('Save')}}</button>
            <router-link class="btn btn-outline-secondary" :to="{ name: 'codes.index' }">{{$t('Cancel')}}</router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
import api from '../api/routes';
import {common} from '../common.js'

export default {
  mixins:[common],
  data() {
    return {
      message: null,
      settings:[
        {
          title: this.$i18n.t('Items per page'),
          key: 'enstru_per_page',
          value: 15,
          type: 'number',
          constraints:{
            min: 10,
            max: 100,
          }
        },
        {
          title: this.$i18n.t('Language'),
          key: 'enstru_language',
          value: 'kk',
          type: 'select',
          options: {
            kk: 'Қазақша',
            ru: 'Русский',
          },
        },
      ],
      setting: null,
    };
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.prevRoute = from
    })
  },
  methods: {
    /*validate(){

    },*/
    save(){
      this.message = null
      this.setting.settings = this.settings
      api.update('setting',this.setting.id, this.setting)
        .then((response) => {
          this.message = this.$i18n.t('Saved')
          //this.$router.go()
        }).catch(e => {
          this.basicErrorHandling(e)
        });
      for(var key in this.settings){
        localStorage.setItem(this.settings[key].key,this.settings[key].value)
        if(this.settings[key].key==='enstru_language'){
          this.$i18n.locale = this.settings[key].value
          document.querySelector('html').setAttribute('lang', this.settings[key].value)
        }
      }
    },
    fetch(){
      api.all('setting',null)
        .then((response) => {
          this.setting = response.data;
          if(this.setting){
            var tabyldy = false;
            for(var i=0;i<this.settings.length;i++){
              for(var j=0;j<this.setting.settings.length;j++)
                if(this.settings[i].key === this.setting.settings[j].key){
                  this.settings[i] = this.setting.settings[j]
                  break
                }
            }
          }
        }).catch(e => {
          this.basicErrorHandling(e)
        });
      //for(var key in this.settings)
        //this.settings[key]['value']=localStorage.getItem(this.settings[key]['key']) || this.settings[key]['value']
    },
  },
  created() {
    this.fetch();
  },
};
</script>