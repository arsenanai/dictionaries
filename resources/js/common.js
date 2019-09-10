export const common = {
	data(){
		return {
			tokenVariable: 'enstru_token',
			appLanguage: 'enstru_language',
		}
	},
	methods:{
		display(what,target){
			if(target!==null)
	      if(what==='name'){
	        if(this.$i18n.locale==='ru')
	          return target.name_ru
	        else
	          return target.name_kk
	      }else if(what==='description'){
	        if(this.$i18n.locale==='ru')
	          return target.description_ru
	        else
	          return target.description_kk
	      }
	      	else
	      		return "";
	    },
	    redirectToLogin(){
	    	//this.$router.push({name: 'auth.logout'})
	    	//localStorage.removeItem(this.tokenVariable);
	    	//this.$router.push({name: 'auth.login'})
	    }
	}
}