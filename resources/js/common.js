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
	    logout(){
	    	this.$router.push({name:'auth.logout'})
	    },
	    basicErrorHandling(e){
	    	console.log(e)
	    	if(e.response)
	    		if(e.response.status)
            		if(e.response.status==401)
                		this.logout()
	    },
	    getType(type){
            if(type.startsWith('migrate_'))
                return type.split('_')[1]
            else if(type.startsWith('code_')
            		||type.startsWith('subgroup_')
            		||type.startsWith('group_')
            	)
                return type.split('_')[0]
            else
                return type
        },
        stringIsSet(string){
        	return (string!=null && string != '')
        },
        arrayIsSet(array){
        	return (array!=null && array.length>0)
        },
	}
}