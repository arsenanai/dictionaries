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
        <form @submit.prevent="save()" v-if="setting">
          <div class="form-group" v-for="setting in setting.settings">
            <label :for="setting.key">
              <i class="fa fa-globe"
                v-if="setting.key==='enstru_language'"></i>
              {{$i18n.t(setting.title)}}
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
          title: 'Items per page',
          key: 'enstru_per_page',
          value: 15,
          type: 'number',
          constraints:{
            min: 10,
            max: 100,
          }
        },
        /*{
          title: 'Language',
          key: 'enstru_language',
          value: 'kk',
          type: 'select',
          options: {
            kk: 'Қазақша',
            ru: 'Русский',
          },
        },*/
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
      var setting = {}
      for(var key in this.setting)
        setting[key] = this.setting[key]
      setting.settings = [];
      for(var i in this.setting.settings){
        var old = this.setting.settings[i]
        var set = {}
        set['key'] = old['key']
        set['value'] = old['value']
        setting.settings.push(set)
      }
      api.update('setting',setting.id, setting)
        .then((response) => {
          this.message = this.$i18n.t('Saved')
          //this.$router.go()
          for(var key in this.setting.settings){
            localStorage.setItem(this.setting.settings[key].key,this.setting.settings[key].value)
            if(this.setting.settings[key].key==='enstru_language'){
              this.$i18n.locale = this.setting.settings[key].value
              document.querySelector('html').setAttribute('lang', this.setting.settings[key].value)
            }
          }
        }).catch(e => {
          this.basicErrorHandling(e)
        });
      
    },
    fetch(){
      api.all('setting',null)
        .then((response) => {
          this.setting = response.data.data;
          console.log(this.setting.settings)
          this.setting.settings = this.merge(this.settings, JSON.parse(this.setting.settings));
        }).catch(e => {
          this.basicErrorHandling(e)
        });
      //for(var key in this.settings)
        //this.settings[key]['value']=localStorage.getItem(this.settings[key]['key']) || this.settings[key]['value']
    },
    merge(s1,s2){
      if (!this.arrayIsSet(s2)) {
        s2 = s1
      }else{
        for(var i=0;i<s1.length;i++){
          var tabyldy = false
          for(var j=0;j<s2.length;j++)
            if(s1[i].key === s2[j].key){
              for(var k in s1[i])
                if(!['key','value'].includes(k))
                  s2[j][k] = s1[i][k]
              tabyldy = true
              break
            }
          if(tabyldy == false){
            s2.push(s1)
          }
        }
      }
      return s2
    }
  },
  created() {
    this.fetch();
  },
};
</script>