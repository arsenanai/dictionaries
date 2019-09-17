 <template>
  <div class=container>
    <div class="row">
      <div class="col-lg-8 offset-lg-2"> 
        <h2>
            {{$t('Subgroups edit')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
          <div v-if="message" :class="message.type">{{ message.text }}</div>
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
            <div class="form-group">
              <label class="sr-only" for="migrateGroup">{{$t('Group')}}</label>
              <select class="form-control mb-2 mr-sm-2" id="migrateGroup" v-model="subgroup.group_id">
                  <option selected disabled value=-1>
                      {{$t('Group')}}
                  </option>
                  <option v-for="group in groups" :value="group.id">
                      {{display('name',group)+((group.isZKS==true) ? " ("+$t('ZKS')+")" : '')}}
                  </option>
              </select>
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
import api from '../api/routes';
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
      	group_id: ""
      },
      type_group_name:null,
      subgroup: {
        id: null,
        group_id: -1,
        name_kk: "",
        name_ru: "",
      }
    };
  },
  methods: {
    onSubmit(event) {
    	if(this.validated()===true){
    		this.saving = true;
        this.message=null;
	        api.update('subgroup',this.subgroup.id, {
	            name_kk: this.subgroup.name_kk,
	            name_ru: this.subgroup.name_ru,
	            group_id: this.subgroup.group_id
	        }).then((response) => {
            this.message={}
            this.message.type="alert alert-success"
	            this.message.text = this.$i18n.t('Subgroup updated');
	            this.subgroup = response.data;
	            setTimeout(() => {
	            	this.message = null
	            	this.$router.push({name:"subgroups.index"});
	            }, 500);
	        }).catch(e => {
            this.basicErrorHandling(e)
            if(e.response.status==422){
                this.message = {}
                this.message.type = 'alert alert-danger'
                var message = this.$i18n.t(e.response.data.message)
                for(var key in e.response.data.errors){
                    e.response.data.errors[key].forEach((value)=>{
                        this.validation[key] = this.$i18n.t(value)
                    })
                }
                this.message.text = message;
              }
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
   fetchDatalist(input,type,parent=null){
            var params = {} 
              params.input = input
              params.lang = this.$i18n.locale
              params.parent = parent
            this.request(type,params)
        },
    request(type,params){
        api.search(this.getType(type), params).then((response) => {
            this[type+'s'] = response.data
        }).catch(e => {
            this.basicErrorHandling(e)
        });
    },
  onDelete() {
    this.saving = true;
    api.delete('subgroup',this.subgroup.id)
       .then((response) => {
          alert(this.$i18n.t('Subgroup Deleted')+', '+this.$i18n.t('codes migrated: ')+" "+response.data.migrated_childs);
          this.$router.push({ name: 'subgroups.index' });
       }).catch(e => {
        this.basicErrorHandling(e)
       });
  	},
  },
  created() {
      api.find('subgroup',this.$route.params.id)
      .then((response) => {
          //setTimeout(() => {
            this.loaded = true;
            this.subgroup = response.data.data;
            this.subgroup.group_id = this.subgroup.group.id
          //}, 1000);
      })
      .catch((e) => {
        this.basicErrorHandling(e)
      });
      this.fetchDatalist('','group')
  }
};
</script>