<template>
    <div class=container>
        <div class="row">
            <div class="col-lg-8 offset-lg-2"> 
              <h2>
            {{$t('Subgroups create')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
      <div v-if="message" class="alert alert-warning">{{ message }}</div>
      <form @submit.prevent="onSubmit($event)" v-else>
          <div class="form-group">
              <label for="subgroup_name_kk">{{$t('Name KK')}}</label>
              <input class="form-control" id="subgroup_name_kk" v-model="subgroup.name_kk" />
              <span v-if="validation.name_kk!==''">{{validation.name_kk}}</span>
          </div>
          <div class="form-group">
              <label for="subgroup_name_ru">{{$t('Name RU')}}</label>
              <input class="form-control" id="subgroup_name_ru" v-model="subgroup.name_ru" />
              <span v-if="validation.name_ru!==''">{{validation.name_ru}}</span>
          </div>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="exampleCheck1" 
            v-model="subgroup.isZKS">
          <label class="form-check-label" for="exampleCheck1">{{$t('isZKS')}}</label>
          </div>
          <br>
          <div class="form-group">
              <label for="group_name">{{$t('Group')}}</label>
              <input class="form-control" id="group_name" list="groups" @keyup="typeahead($event.target.value, 'groups')">
            <datalist id="groups">
              <option v-for="group in groups" :value="display('name',group)"></option>
            </datalist>
              <span v-if="validation.group!==''">{{validation.group}}</span>
          </div>
          <div class="form-group">
              <button type="submit" :disabled="saving"
                class="btn btn-outline-primary">
                  {{ saving ? $t('Creating')+'...' : $t('Create') }}
              </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
  import api from '../api/subgroups';
  import {common} from '../common.js'
  export default {
    mixins: [common],
    data() {
      return {
        message: null,
        loaded: false,
        saving: false,
        validation:{
          name_kk:null,
          name_ru:null,
          group: null
        },
        subgroup: {
          id: null,
          group: null,
          name_kk: null,
          name_ru: null,
          isZKS: false,
        },
        groups:null,
      };
    },
    methods: {
      onSubmit(event) {
        if(this.validated()===true){
          this.saving = true;
            api.create(this.subgroup).then((response) => {
                this.message = this.$i18n.t('Subgroup created');
                this.subgroup = response.data.data;
                setTimeout(() => {
                  this.message = null
                  this.$router.push({name:"subgroups.index"});
                }, 1000);
            }).catch(e => {
                if(e.response.status==401)
                  this.redirectToLogin()
            }).then(_ => this.saving = false);
        }
      },
      validated(){
        this.validation.name_kk=""
        this.validation.name_ru=""
        this.validation.group = ""
        var result = true;
        /*if(this.subgroup.name_kk==="" 
          || this.subgroup.name_kk===null){
          this.validation.name_kk = "Name in kazakh can not be empty"
          result = false;
        }*/
      if(this.subgroup.name_ru==="" 
          || this.subgroup.name_ru===null){
          this.validation.name_ru = this.$i18n.t("Name in kazakh can not be empty")
          result = false;
        }
        if(this.subgroup.group == null){
          this.validation.group = this.$i18n.t("Group have to be choosen")
        }
      return result
    },
    typeahead(input, type) {
      var matched = false;
      if(this.groups && type==='groups'){
        var group;
        if(this.$i18n.locale==='ru')
          group = this.groups.find(group => group.name_ru === input);
        else
          group = this.groups.find(group => group.name_kk === input);
        if(group){
          this.subgroup.group = group
          matched = true
        }
      }
      if (input.length > 1 && matched===false) {
        if(type==='groups'){
          api.searchGroup(input, this.$i18n.locale).then((response) => {
            this.groups = response.data.data
          }).catch(e => {
            if(e.response.status==401)
              this.redirectToLogin()
          });
        }
      }else if(input.length == 0){
        if(type==='groups'){
          this.subgroup.group=null;
          this.groups=null;
        }
      }
    },
    created() {
        api.find(this.$route.params.id)
        .then((response) => {
            //setTimeout(() => {
              this.loaded = true;
              this.subgroup = response.data.data;
            //}, 1000);
        })
        .catch((e) => {
          if(e.response.status==401)
            this.redirectToLogin()
          this.$router.push({ name: '404' });
        });
    }
  }
};
</script>
<style lang="scss" scoped>
$red: lighten(red, 30%);
$darkRed: darken($red, 50%);
.form-group label {
  display: block;
}
.alert {
    background: $red;
    color: $darkRed;
    padding: 1rem;
    margin-bottom: 1rem;
    width: 50%;
    border: 1px solid $darkRed;
    border-radius: 5px;
}
</style>