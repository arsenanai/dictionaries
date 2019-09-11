 <template>
  <div class=container>
    <div class="row">
      <div class="col-lg-8 offset-lg-2"> 
        <h2>
            {{$t('Groups edit')}}
            <!--<router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>-->
        </h2>
        <hr>
        <div v-if="message" :class="message.type">{{ message.text }}</div>
        <div v-if="! loaded">Loading...</div>
          <form @submit.prevent="onSubmit($event)" v-else>
            <div class="form-group">
                <label for="group_name">{{$t('Name KK')}}</label>
                <input class="form-control" id="group_name" v-model="group.name_kk" />
                <span v-if="validation.name_kk!==''">{{validation.name_kk}}</span>
            </div>
            <div class="form-group">
                <label for="group_name">{{$t('Name RU')}}</label>
                <input class="form-control" id="group_name" v-model="group.name_ru" />
                <span v-if="validation.name_ru!==''">{{validation.name_ru}}</span>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="group.isZKS" /> {{$t('isZKS')}}
            </div>
            <br>
            <div class="form-group">
                <button class="btn btn-outline-primary" v-if="group.name_kk!='Қалғандары'" type="submit" :disabled="saving">{{$t('Update')}}</button>
                <button class="btn btn-outline-danger" v-if="group.name_kk!='Қалғандары'" :disabled="saving" @click.prevent="onDelete($event)">{{$t('Delete')}}</button>
                <router-link class="btn btn-outline-secondary" :to="{ name: 'groups.index' }">{{$t('Cancel')}}</router-link>
            </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
import api from '../api/routes';
import {common} from '../common'
export default {
  mixins: [common],
  data() {
    return {
      message: null,
      loaded: false,
      saving: false,
      validation:{
      	name_kk:"",
      	name_ru:""
      },
      group: {
        id: null,
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
        this.message = null;
	        api.update('group',this.group.id, {
	            name_kk: this.group.name_kk,
	            name_ru: this.group.name_ru,
              isZKS: this.group.isZKS,
	        }).then((response) => {
              this.message={}
              this.message.type='alert alert-success'
	            this.message.text = this.$i18n.t('Group updated');
	            this.group = response.data.data;
	            setTimeout(() => {
	            	this.message = null
	            	this.$router.push({name:"groups.index"});
	            }, 1000);
	        }).catch(e => {
            basicErrorHandling(e)
	        }).then(_ => this.saving = false);
    	}
    },
    validated(){
	  	this.validation.name_kk=""
	  	this.validation.name_ru=""
	  	var result = true;
	  	if(this.group.name_kk==="" 
	  		|| this.group.name_kk===null){
	  		this.validation.name_kk = this.$i18n.t('Name in kazakh can not be empty')
	  		result = false;
	  	}
		if(this.group.name_ru==="" 
	  		|| this.group.name_ru===null){
	  		this.validation.name_ru = this.$i18n.t('Name in russian can not be empty')
	  		result = false;
	  	}
	  return result
	},
  onDelete() {
    this.saving = true;
    this.message = null
    api.delete('group',this.group.id)
       .then((response) => {
          this.message = {}
          this.message.type = 'alert alert-success'
          this.message.text = this.$i18n.t('Group Deleted');
        setTimeout(() => this.$router.push({ name: 'groups.index' }), 2000);
       }).catch(e => {
        basicErrorHandling(e)
       });
  	},
  },
  created() {
    this.message = null
      api.find('group',this.$route.params.id)
      .then((response) => {
          //setTimeout(() => {
            this.loaded = true;
            this.group = response.data.data;
          //}, 1000);
      })
      .catch((e) => {
        basicErrorHandling(e)
      });
  }
};
</script>