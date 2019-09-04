 <template>
  <div class=container>
    <div class="row">
      <div class="col-lg-8 offset-lg-2"> 
        <h2>
            {{$t('Subgroups edit')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
          <div v-if="message" class="alert">{{ message }}</div>
          <div v-if="! loaded">{{$t('Loading')}}...</div>
          <form @submit.prevent="onSubmit($event)" v-else>
            <div class="form-group">
                <label for="subgroup_name">{{$t('Name KK')}}</label>
                <input class="form-control" id="subgroup_name" v-model="subgroup.name_kk" />
                <span v-if="validation.name_kk!==''">{{validation.name_kk}}</span>
            </div>
            <div class="form-group">
                <label for="subgroup_name">{{$t('Name RU')}}</label>
                <input class="form-control" id="subgroup_name" v-model="subgroup.name_ru" />
                <span v-if="validation.name_ru!==''">{{validation.name_ru}}</span>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="subgroup.isZKS" /> {{$t('isZKS')}}
              </div>
              <br>
            <div class="form-group">
              <label for="group_name">{{$t('Group')}}</label>
              <input class="form-control" id="group_name" list="groups" @keyup="typeahead($event.target.value, 'groups')" v-model="type_group_name">
            <datalist id="groups">
              <option v-for="group in groups" :value="display('name',group)">{{display('name',group)}}</option>
            </datalist>
              <span v-if="validation.group!==''">{{validation.group}}</span>
          </div>
            <div class="form-group">
                <button class="btn btn-outline-primary" v-if="subgroup.name_kk!='Қалғандары'" type="submit" :disabled="saving">{{$t('Update')}}</button>
                <button class="btn btn-outline-danger" v-if="subgroup.name_kk!='Қалғандары'" :disabled="saving" @click.prevent="onDelete($event)">{{$t('Delete')}}</button>
                <router-link class="btn btn-outline-secondary" :to="{ name: 'subgroups.index' }">{{$t('Cancel')}}</router-link>
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
      groups: null,
      loaded: false,
      saving: false,
      validation:{
      	name_kk:"",
      	name_ru:"",
      	group: ""
      },
      type_group_name:null,
      subgroup: {
        id: null,
        group: null,
        name_kk: "",
        name_ru: "",
        isZKS: false,
      }
    };
  },
  methods: {
    onSubmit(event) {
    	if(this.validated()===true){
    		this.saving = true;
	        api.update(this.subgroup.id, {
	            name_kk: this.subgroup.name_kk,
	            name_ru: this.subgroup.name_ru,
              isZKS: this.subgroup.isZKS,
	            group: this.subgroup.group
	        }).then((response) => {
	            this.message = this.$i18n.t('Subgroup updated');
	            this.subgroup = response.data.data;
	            setTimeout(() => {
	            	this.message = null
	            	this.$router.push({name:"subgroups.index"});
	            }, 2000);
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
	  	if(this.subgroup.name_kk==="" 
	  		|| this.subgroup.name_kk===null){
	  		this.validation.name_kk = this.$i18n.t('Name in kazakh can not be empty')
	  		result = false;
	  	}
		if(this.subgroup.name_ru==="" 
	  		|| this.subgroup.name_ru===null){
	  		this.validation.name_ru = this.$i18n.t('Name in russian can not be empty')
	  		result = false;
	  	}
	  	if(this.subgroup.group == null){
	  		this.validation.group = this.$i18n.t('Group have to be choosen')
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
          var params={}
          params.name = input
          params.lang = this.$i18n.locale
          api.search('group', {params}).then((response) => {
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
          this.subgroup.subgroup=null;
          this.subgroups=null;
          this.type_subgroup=null
        }
      }
    },
  onDelete() {
    this.saving = true;
    api.delete(this.subgroup.id)
       .then((response) => {
          this.message = this.$i18n.t('Subgroup Deleted')+', '+this.$i18n.t('codes migrated: ')+response.data.migrated_childs;
        //setTimeout(() => this.$router.push({ name: 'subgroups.index' }), 2000);
       }).catch(e => {
          if(e.response.status==401)
            this.redirectToLogin()
       });
  	},
  },
  created() {
      api.find(this.$route.params.id)
      .then((response) => {
          //setTimeout(() => {
            this.loaded = true;
            this.subgroup = response.data.data;
            this.type_group_name = this.display('name',this.subgroup.group)
          //}, 1000);
      })
      .catch((e) => {
        if(e.response.status==401)
          this.redirectToLogin()

        //this.$router.push({ name: '404' });
      });
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