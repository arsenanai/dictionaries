<template>
  <div class="container ">   
    <div class="row">
      <div class="col-lg-8 offset-lg-2"> 
        <h2>
          {{$t('Settings')}}
        </h2>
        <hr>
        <div :class="message.type" v-if="message">
          {{message.text}}
        </div>
        <form @submit.prevent="save()" v-if="setting">
          <div class="form-group" v-for="setting in setting.settings">
            <label :for="setting.key">
              <i class="fa fa-globe"
                v-if="setting.key==='dictionary_language'"></i>
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
            <button class="btn btn-outline-primary mr-2" type="submit" :disabled="loading">{{$t('Save')}}</button>
            <router-link class="btn btn-outline-secondary" :to="{ name: 'codes.index' }" :class="{disabled: loading}">
              {{$t('Cancel')}}
            </router-link>
            <span class="alert alert-info" v-if="loading">{{$t('Loading')}}. {{$t('Please wait until end')}}...</span>
          </div>
        </form>
        <span v-if="setting">
          <span v-if="setting.user_id==1">
            <button class="btn btn-outline-success mb-3 mr-2" @click.prevent="sync()"
              :disabled="loading">
              {{$t('Synchronize Codes with Production')}}
            </button>
            <button class="btn btn-outline-danger mb-3 mr-2" @click.prevent="reset('groups')"
              :disabled="loading">
              {{$t('Reset Groups and Subgroups as in Excel')}}
            </button>
            <button class="btn btn-outline-danger mb-3 mr-2" @click.prevent="reset('codes')"
              :disabled="loading">
              {{$t('Reset all Codes and migrate all to Others Group and Subgroup')}}
            </button>
          </span>
        </span>
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
      loading: false,
      settings:[
        {
          title: 'Items per page',
          key: 'dictionary_per_page',
          value: 15,
          type: 'number',
          constraints:{
            min: 10,
            max: 100,
          }
        },
        /*{
          title: 'Language',
          key: 'dictionary_language',
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
      this.loading = true
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
          this.message = {}
          this.message.type="alert alert-success"
          this.message.text = this.$i18n.t('Saved')
          //this.$router.go()
          for(var key in this.setting.settings){
            localStorage.setItem(this.setting.settings[key].key,this.setting.settings[key].value)
            if(this.setting.settings[key].key==='dictionary_language'){
              this.$i18n.locale = this.setting.settings[key].value
              document.querySelector('html').setAttribute('lang', this.setting.settings[key].value)
            }
          }
        }).catch(e => {
          this.basicErrorHandling(e)
        }).finally(()=>{
          this.loading = false
        })
      
    },
    fetch(){
      api.all('setting',null)
        .then((response) => {
          this.setting = response.data.data;
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
    },
    reset(type){
      this.message = null
      this.loading = true
      if(type=='groups'){
        if(confirm(this.$i18n.t('WARNING')+"! "+this.$i18n.t('This will delete all existing groups and subgroups and reimport them from excel file')+". "+this.$i18n.t("It can't be undone")+". "+this.$i18n.t('Are you sure?'))){
          var params = {}
          params.type = 'group'
          api.reset(params)
          .then((response) =>{
            this.message = this.toast('success',this.$i18n.t('Groups imported')+": "+response.data.split('_')[0]+", "+this.$i18n.t('Subgroups imported')+": "+response.data.split('_')[1])
          })
          .catch((e)=>{
            this.message = this.basicErrorHandling(e)
          }).finally(()=>{
            this.loading = false
          })
        }
      }else if(type=='codes'){
        if(confirm(this.$i18n.t('WARNING')+"! "+this.$i18n.t('This will delete all existing groups and subgroups and reimport them from excel file')+". "+this.$i18n.t("It can't be undone")+". "+this.$i18n.t('Are you sure?'))){
          var params = {}
          params.type = 'code'
          api.reset(params)
          .then((response) =>{
            this.message = this.toast('success',this.$i18n.t('Codes imported')+": "+response.data)
          })
          .catch((e)=>{
            this.message = this.basicErrorHandling(e)
          }).finally(()=>{
            this.loading = false
          })
        }
      }
    },
    sync(){
      this.message = null
      this.loading = true
      if(confirm(this.$i18n.t('This will synchronize codes from production database to local. All relations with groups and subgroups will remain. Any new code will be added to Others group and subgroup')+". "+this.$i18n.t('It can\'t be undone')+". "+this.$i18n.t('Are you sure?'))){
        api.sync({})
        .then((response) =>{
          this.message = this.toast('success',this.$i18n.t('Codes imported')+": "+response.data)
        })
        .catch((e)=>{
          this.message = this.basicErrorHandling(e)
        }).finally(()=>{
          this.loading = false
        })
      }
    },
  },
  created() {
    this.fetch();
  },
};
</script>